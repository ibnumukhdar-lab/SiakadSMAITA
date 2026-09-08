<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    // Halaman Utama Tabel Daftar Siswa
    public function index()
    {
        $siswas = Siswa::latest()->get();
        return view('siswa.index', compact('siswas'));
    }

    // Form Tambah Siswa
    public function create()
    {
        return view('siswa.create');
    }

    // Simpan Tambah Siswa
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nisn' => 'required|unique:siswas,nisn',
        ]);

        $data = $request->except(['_token']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        Siswa::create($data);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan!');
    }

    // Form Edit Siswa
    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);
        return view('siswa.edit', compact('siswa'));
    }

    // Proses Update Data Siswa
    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required',
            'nisn' => 'required|unique:siswas,nisn,' . $id,
        ]);

        $data = $request->except(['_token', '_method']);

        if ($request->hasFile('foto')) {
            if ($siswa->foto) { Storage::disk('public')->delete($siswa->foto); }
            $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')->with('success', 'Data siswa ' . $siswa->nama_lengkap . ' berhasil diperbarui!');
    }

    // Fitur Hapus Data Siswa (HARD DELETE PERMANEN)
    public function destroy($id)
    {
        // Cari data (meskipun berstatus terhapus sementara/soft delete sebelumnya)
        $siswa = Siswa::withTrashed()->findOrFail($id);
        
        // Hapus file foto dari server agar tidak jadi sampah storage
        if ($siswa->foto) { 
            Storage::disk('public')->delete($siswa->foto); 
        }
        
        // Hancurkan data secara permanen dari MySQL
        $siswa->forceDelete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus secara permanen dari sistem!');
    }

    // Halaman Form Impor CSV
    public function importForm()
    {
        return view('siswa.import');
    }

    // Proses Eksekusi Impor CSV (Pembersih Data Hantu Otomatis)
    public function prosesImport(Request $request)
    {
        if (!$request->hasFile('file_csv')) {
            return redirect()->back()->with('error', 'Silakan pilih file CSV terlebih dahulu.');
        }

        $barisKe = 1; 

        try {
            $file = $request->file('file_csv');
            ini_set('auto_detect_line_endings', true);
            $handle = fopen($file->getRealPath(), "r");
            
            // Deteksi pemisah
            $firstLine = fgets($handle);
            $komaCount = substr_count($firstLine, ',');
            $titikKomaCount = substr_count($firstLine, ';');
            $delimiter = ($titikKomaCount > $komaCount) ? ';' : ',';
            
            rewind($handle);
            fgetcsv($handle, 1000, $delimiter); 

            $sukses = 0;
            $duplikat = 0;

            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $barisKe++;
                
                if (empty($data) || count($data) < 3) continue; 

                $nama = isset($data[0]) ? trim($data[0]) : null;
                $nisn = isset($data[2]) ? trim($data[2]) : null;

                if (empty($nama)) {
                    return redirect()->route('siswa.index')->with('error', "❌ GAGAL PADA BARIS KE-$barisKe. MASALAH: Kolom 'Nama Lengkap' kosong. SOLUSI: Pastikan nama siswa terisi, tidak boleh dikosongkan.");
                }
                if (empty($nisn)) {
                    return redirect()->route('siswa.index')->with('error', "❌ GAGAL PADA BARIS KE-$barisKe. MASALAH: Kolom 'NISN' kosong untuk siswa bernama $nama. SOLUSI: Pastikan nomor NISN terisi.");
                }
                if (strlen($nisn) > 20) {
                    return redirect()->route('siswa.index')->with('error', "❌ GAGAL PADA BARIS KE-$barisKe. MASALAH: NISN '$nisn' terlalu panjang. SOLUSI: Cek excel Anda, mungkin salah ketik. Maksimal 20 karakter.");
                }

                // --- LOGIKA ANTI DATA HANTU ---
                $cekGhost = Siswa::withTrashed()->where('nisn', $nisn)->first();
                if ($cekGhost) {
                    if ($cekGhost->trashed()) {
                        // Jika data nyangkut di tong sampah, hancurkan permanen agar data baru bisa masuk!
                        $cekGhost->forceDelete();
                    } else {
                        // Jika data memang sudah ada dan aktif di tabel, biarkan lewat (murni duplikat)
                        $duplikat++;
                        continue;
                    }
                }

                Siswa::create([
                    'nama_lengkap'     => $nama,
                    'nis'              => isset($data[1]) ? trim($data[1]) : null,
                    'nisn'             => $nisn,
                    'ttl'              => isset($data[3]) ? trim($data[3]) : null,
                    'jk'               => isset($data[4]) ? trim($data[4]) : null,
                    'status'           => (!empty($data[5])) ? trim($data[5]) : 'Aktif',
                    'kelas'            => isset($data[6]) ? trim($data[6]) : null,
                    'thn_masuk'        => isset($data[7]) ? trim($data[7]) : null,
                    'hp_ortu'          => isset($data[8]) ? trim($data[8]) : null,
                    'nama_ayah'        => isset($data[9]) ? trim($data[9]) : null,
                    'status_ayah'      => (!empty($data[10])) ? trim($data[10]) : 'Masih Hidup',
                    'pekerjaan_ayah'   => isset($data[11]) ? trim($data[11]) : null,
                    'nama_ibu'         => isset($data[12]) ? trim($data[12]) : null,
                    'status_ibu'       => (!empty($data[13])) ? trim($data[13]) : 'Masih Hidup',
                    'pekerjaan_ibu'    => isset($data[14]) ? trim($data[14]) : null,
                    'nama_wali'        => isset($data[15]) ? trim($data[15]) : null,
                    'pekerjaan_wali'   => isset($data[16]) ? trim($data[16]) : null,
                    'hp_wali'          => isset($data[17]) ? trim($data[17]) : null,
                    'kesejahteraan'    => isset($data[18]) ? trim($data[18]) : null,
                    'tinggal_bersama'  => isset($data[19]) ? trim($data[19]) : null,
                    'jarak'            => isset($data[20]) ? trim($data[20]) : null,
                    'transportasi'     => isset($data[21]) ? trim($data[21]) : null,
                    'asal_sekolah'     => isset($data[22]) ? trim($data[22]) : null,
                ]);
                $sukses++;
            }
            fclose($handle);

            return redirect()->route('siswa.index')->with('success', "Impor selesai! $sukses siswa berhasil dimasukkan, $duplikat data duplikat dilewati.");

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $pesanRamah = "Gagal memproses data.";
            $solusi = "Periksa kembali format penulisan file CSV Anda.";

            if (strpos($errorMessage, 'Data too long') !== false) {
                $pesanRamah = "Ada teks yang terlalu panjang dan melebihi kapasitas kolom.";
                $solusi = "Cek baris tersebut. Jangan memasukkan teks/paragraf yang panjangnya tidak wajar pada excel Anda.";
            } elseif (strpos($errorMessage, 'Incorrect integer') !== false || strpos($errorMessage, 'Numeric value out of range') !== false) {
                $pesanRamah = "Sistem mengharapkan angka (nomor), tapi diisi dengan huruf atau tanda baca asing.";
                $solusi = "Cek nomor HP, NIS, dll pada baris tersebut. Pastikan murni angka, tanpa spasi, strip, atau huruf.";
            } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
                $pesanRamah = "Data bentrok/ganda.";
                $solusi = "Ada data yang sama persis terselip dan berbenturan dengan data lama.";
            }

            return redirect()->route('siswa.index')->with('error', "❌ GAGAL PADA BARIS KE-$barisKe. MASALAH: $pesanRamah SOLUSI: $solusi (Pesan Asli Sistem: " . substr($errorMessage, 0, 50) . "...)");
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Template_Import_Siswa.csv',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nama Lengkap', 'NIS', 'NISN', 'Tempat Tgl Lahir', 'Jenis Kelamin', 'Status', 'Kelas', 'Tahun Masuk', 'No HP Utama Ortu', 'Nama Ayah', 'Status Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Status Ibu', 'Pekerjaan Ibu', 'Nama Wali', 'Pekerjaan Wali', 'No HP Wali', 'No KIP PKH', 'Tinggal Bersama', 'Jarak km', 'Transportasi', 'Asal Sekolah']);
            fputcsv($file, ['Ahmad Dahlan', '23241001', '0051234567', 'Sampit, 12 Mei 2008', 'Laki-laki', 'Aktif', 'X', '2024', '08123456789', 'Budi', 'Masih Hidup', 'Wiraswasta', 'Siti', 'Masih Hidup', 'Ibu Rumah Tangga', '', '', '', '', 'Orang Tua', '2.5', 'Sepeda Motor', 'SMPN 1 Sampit']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);
        return view('siswa.show', compact('siswa'));
    }

    public function showPublic($nisn)
    {
        $siswa = Siswa::where('nisn', $nisn)->firstOrFail();
        return view('siswa.public', compact('siswa'));
    }

    // --- UPDATE: Fitur Aksi Massal (Bulk Action) TERMASUK UBAH GENDER ---
    public function bulkAction(Request $request)
    {
        $request->validate([
            'bulk_action_type' => 'required',
        ]);

        if (empty($request->siswa_ids)) {
            return back()->with('error', 'Pilih minimal satu siswa terlebih dahulu dengan mencentang kotaknya.');
        }

        $ids = $request->siswa_ids;

        switch ($request->bulk_action_type) {
            case 'delete':
                // Ambil data yang diceklis (bahkan yang di tong sampah jika terselip)
                $siswas = Siswa::withTrashed()->whereIn('id', $ids)->get();
                foreach($siswas as $s) {
                    if ($s->foto) { Storage::disk('public')->delete($s->foto); } // Hapus fisik foto
                    $s->forceDelete(); // Hapus fisik database
                }
                $pesan = count($ids) . " data siswa berhasil dimusnahkan secara permanen.";
                break;
                
            case 'set_x':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'X', 'status' => 'Aktif']);
                $pesan = count($ids) . " siswa berhasil dinaikkan ke Kelas X.";
                break;
                
            case 'set_xi':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XI', 'status' => 'Aktif']);
                $pesan = count($ids) . " siswa berhasil dinaikkan ke Kelas XI.";
                break;
                
            case 'set_xii':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XII', 'status' => 'Aktif']);
                $pesan = count($ids) . " siswa berhasil dinaikkan ke Kelas XII.";
                break;
                
            case 'set_alumni':
                Siswa::whereIn('id', $ids)->update(['status' => 'Alumni']);
                $pesan = count($ids) . " siswa berhasil diubah statusnya menjadi Alumni.";
                break;

            // --- TAMBAHAN BARU: UBAH GENDER MASSAL ---
            case 'set_laki':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Laki-laki']);
                $pesan = count($ids) . " siswa berhasil diubah gendernya menjadi Laki-laki.";
                break;
                
            case 'set_perempuan':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Perempuan']);
                $pesan = count($ids) . " siswa berhasil diubah gendernya menjadi Perempuan.";
                break;

            default:
                return back()->with('error', 'Aksi tidak dikenali.');
        }

        if (!empty($request->bulk_tahun_ajaran)) {
            Siswa::whereIn('id', $ids)->update([
                'tahun_ajaran' => $request->bulk_tahun_ajaran
            ]);
        }

        return back()->with('success', $pesan);
    }
}