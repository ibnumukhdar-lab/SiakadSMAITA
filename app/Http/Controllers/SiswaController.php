<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    /**
     * DAFTAR PUTIH kolom yang boleh diisi dari form.
     * Sebelumnya controller memakai $request->except([...]) sehingga kolom apa pun
     * (mis. deleted_at / thn_lulus) bisa disetel dari browser.
     */
    private const KOLOM_ISI = [
        'nama_lengkap', 'nisn', 'nis', 'ttl', 'jk', 'status', 'kelas',
        'thn_masuk', 'thn_lulus', 'tahun_ajaran',
        'nama_ayah', 'status_ayah', 'pekerjaan_ayah',
        'nama_ibu', 'status_ibu', 'pekerjaan_ibu',
        'nama_wali', 'pekerjaan_wali', 'hp_wali',
        'hp_ortu', 'tinggal_bersama', 'alamat', 'jarak', 'transportasi',
        'kesejahteraan', 'asal_sekolah', 'penyakit',
    ];

    private const KELAS_SAH = ['X', 'XI', 'XII', 'Lulus'];
    private const STATUS_SAH = ['Aktif', 'Alumni', 'Pindah'];

    // =====================================================================
    //  DAFTAR & FORM
    // =====================================================================

    // Halaman Utama Tabel Daftar Siswa
    public function index()
    {
        $siswas = Siswa::latest()->get();

        $jumlahTong = Siswa::onlyTrashed()->count();
        $jumlahNisnBermasalah = $siswas->filter(
            fn ($s) => ! preg_match('/^\d{10}$/', (string) $s->nisn)
        )->count();

        return view('siswa.index', compact('siswas', 'jumlahTong', 'jumlahNisnBermasalah'));
    }

    // Form Tambah Siswa
    public function create()
    {
        return view('siswa.create');
    }

    // Simpan Tambah Siswa
    public function store(Request $request)
    {
        $this->validasiData($request);

        $data = $this->siapkanData($request);

        if ($balasan = $this->cekNisnBentrok($data['nisn'])) {
            return $balasan;
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        Siswa::create($data);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa ' . $data['nama_lengkap'] . ' berhasil ditambahkan!');
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

        $this->validasiData($request, $siswa);

        $data = $this->siapkanData($request);

        if ($balasan = $this->cekNisnBentrok($data['nisn'], $siswa->id)) {
            return $balasan;
        }

        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        $siswa->update($data);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa ' . $siswa->nama_lengkap . ' berhasil diperbarui!');
    }

    // Hapus data siswa = PINDAH KE TONG SAMPAH (masih bisa dipulihkan)
    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);
        $nama = $siswa->nama_lengkap;

        $siswa->delete();

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa ' . $nama . ' dipindahkan ke Tong Sampah. Masih bisa dipulihkan dari menu Tong Sampah.');
    }

    // =====================================================================
    //  TONG SAMPAH (soft delete) — pulihkan / hapus permanen
    // =====================================================================

    public function trash()
    {
        $siswas = Siswa::onlyTrashed()->orderByDesc('deleted_at')->get();
        return view('siswa.trash', compact('siswas'));
    }

    public function restore($id)
    {
        $siswa = Siswa::onlyTrashed()->findOrFail($id);
        $nama = $siswa->nama_lengkap;
        $siswa->restore();

        return redirect()->route('siswa.trash')
            ->with('success', 'Data siswa ' . $nama . ' berhasil dipulihkan ke daftar Data Induk.');
    }

    // Hapus PERMANEN — dijaga middleware permission:hapus-permanen-siswa
    public function forceDestroy($id)
    {
        $siswa = Siswa::onlyTrashed()->findOrFail($id);
        $nama = $siswa->nama_lengkap;

        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->forceDelete();

        return redirect()->route('siswa.trash')
            ->with('success', 'Data siswa ' . $nama . ' dihapus permanen dari sistem.');
    }

    // =====================================================================
    //  IMPOR CSV
    // =====================================================================

    public function importForm()
    {
        return view('siswa.import');
    }

    /**
     * Impor CSV. Aturan barunya:
     *  - TIDAK menghapus data di tong sampah secara otomatis (dulu forceDelete!).
     *  - Setiap baris divalidasi lebih dulu; baris bermasalah dilewati & dilaporkan.
     *  - NISN dibersihkan dari spasi/karakter tak terlihat, wajib 10 digit.
     *  - Baris yang lolos barulah disimpan, dalam satu transaksi.
     */
    public function prosesImport(Request $request)
    {
        if (! $request->hasFile('file_csv')) {
            return redirect()->back()->with('error', 'Silakan pilih file CSV terlebih dahulu.');
        }

        $file = $request->file('file_csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()->route('siswa.index')->with('error', 'Berkas CSV tidak bisa dibaca.');
        }

        // Deteksi pemisah (koma / titik koma)
        $barisPertama = (string) fgets($handle);
        $delimiter = (substr_count($barisPertama, ';') > substr_count($barisPertama, ',')) ? ';' : ',';
        rewind($handle);
        fgetcsv($handle, 0, $delimiter); // buang baris judul

        $siapSimpan = [];
        $masalah = [];
        $duplikat = [];
        $nomor = 1;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $nomor++;

            $isiSel = array_filter($data, fn ($v) => $this->rapikanTeks($v) !== '' && $this->rapikanTeks($v) !== null);
            if (empty($data) || empty($isiSel)) {
                continue; // baris kosong
            }

            $ambil = fn (int $i) => isset($data[$i]) ? $this->rapikanTeks($data[$i]) : null;

            $nama = (string) $ambil(0);
            $nis = $this->rapikanKode($ambil(1));
            $nisn = $this->bersihkanNisn($ambil(2));

            if ($nama === '') {
                $masalah[] = "Baris $nomor: kolom Nama Lengkap kosong — dilewati.";
                continue;
            }
            if ($nisn === '') {
                $masalah[] = "Baris $nomor ($nama): NISN kosong — dilewati.";
                continue;
            }
            if (strlen($nisn) > 20) {
                $masalah[] = "Baris $nomor ($nama): NISN '$nisn' lebih dari 20 karakter — dilewati.";
                continue;
            }
            if (strlen($nisn) !== 10) {
                $masalah[] = "Baris $nomor ($nama): NISN '$nisn' bukan 10 digit — dilewati, betulkan dulu di Excel.";
                continue;
            }
            if (isset($siapSimpan[$nisn])) {
                $masalah[] = "Baris $nomor ($nama): NISN $nisn muncul dua kali di berkas ini — hanya yang pertama diimpor.";
                continue;
            }

            $sudahAda = Siswa::withTrashed()->where('nisn', $nisn)->first();
            if ($sudahAda) {
                if ($sudahAda->trashed()) {
                    $masalah[] = "Baris $nomor ($nama): NISN $nisn ada di Tong Sampah atas nama {$sudahAda->nama_lengkap} — pulihkan atau hapus permanen dulu di menu Tong Sampah.";
                } else {
                    $duplikat[] = $nama;
                }
                continue;
            }

            $siapSimpan[$nisn] = [
                'nama_lengkap'    => $nama,
                'nis'             => $nis,
                'nisn'            => $nisn,
                'ttl'             => $ambil(3),
                'jk'              => $this->rapikanJk($ambil(4)),
                'status'          => $this->rapikanStatus($ambil(5)) ?? 'Aktif',
                'kelas'           => $this->rapikanKelas($ambil(6)),
                'thn_masuk'       => $ambil(7),
                'hp_ortu'         => $ambil(8),
                'nama_ayah'       => $ambil(9),
                'status_ayah'     => $ambil(10) ?: 'Masih Hidup',
                'pekerjaan_ayah'  => $ambil(11),
                'nama_ibu'        => $ambil(12),
                'status_ibu'      => $ambil(13) ?: 'Masih Hidup',
                'pekerjaan_ibu'   => $ambil(14),
                'nama_wali'       => $ambil(15),
                'pekerjaan_wali'  => $ambil(16),
                'hp_wali'         => $ambil(17),
                'kesejahteraan'   => $ambil(18),
                'tinggal_bersama' => $ambil(19),
                'jarak'           => $ambil(20),
                'transportasi'    => $ambil(21),
                'asal_sekolah'    => $ambil(22),
                'alamat'          => $ambil(23),
                'penyakit'        => $ambil(24),
                'tahun_ajaran'    => $ambil(25),
            ];
        }
        fclose($handle);

        $berhasil = 0;
        if (! empty($siapSimpan)) {
            DB::transaction(function () use ($siapSimpan, &$berhasil) {
                foreach ($siapSimpan as $baris) {
                    Siswa::create($this->kosongKeNull($baris));
                    $berhasil++;
                }
            });
        }

        $pesan = "Impor selesai: $berhasil siswa masuk.";
        if (count($duplikat) > 0) {
            $pesan .= ' ' . count($duplikat) . ' baris dilewati karena NISN sudah terdaftar.';
        }
        if (count($masalah) > 0) {
            $pesan .= ' ' . count($masalah) . ' baris bermasalah (lihat rinciannya di bawah).';
        }
        if ($berhasil === 0 && count($masalah) === 0 && count($duplikat) === 0) {
            $pesan = 'Tidak ada baris data yang bisa diimpor dari berkas itu.';
        }

        return redirect()->route('siswa.index')
            ->with('success', $pesan)
            ->with('masalah_impor', array_slice($masalah, 0, 15))
            ->with('masalah_impor_total', count($masalah));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Template_Import_Siswa.csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Nama Lengkap', 'NIS', 'NISN', 'Tempat Tgl Lahir', 'Jenis Kelamin', 'Status', 'Kelas',
                'Tahun Masuk', 'No HP Utama Ortu', 'Nama Ayah', 'Status Ayah', 'Pekerjaan Ayah',
                'Nama Ibu', 'Status Ibu', 'Pekerjaan Ibu', 'Nama Wali', 'Pekerjaan Wali', 'No HP Wali',
                'No KIP PKH', 'Tinggal Bersama', 'Jarak km', 'Transportasi', 'Asal Sekolah',
                'Alamat Lengkap', 'Riwayat Penyakit', 'Tahun Ajaran',
            ]);
            fputcsv($file, [
                'Ahmad Dahlan', '23241001', '0051234567', 'Sampit, 12 Mei 2008', 'Laki-laki', 'Aktif', 'X',
                '2024', '08123456789', 'Budi', 'Masih Hidup', 'Wiraswasta',
                'Siti', 'Masih Hidup', 'Ibu Rumah Tangga', '', '', '',
                '', 'Orang Tua', '2.5', 'Sepeda Motor', 'SMPN 1 Sampit',
                'Jl. Contoh No. 1, Sampit', '', '2026/2027',
            ]);
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

    // =====================================================================
    //  AKSI MASSAL (Bulk Action)
    // =====================================================================

    public function bulkAction(Request $request)
    {
        $aksi = (string) $request->input('bulk_action_type');

        $sah = ['delete', 'set_x', 'set_xi', 'set_xii', 'set_alumni', 'set_aktif', 'set_laki', 'set_perempuan'];

        if (! in_array($aksi, $sah, true)) {
            return back()->with('error', 'Aksi massal tidak dikenali.');
        }

        $ids = array_filter((array) $request->input('siswa_ids', []), fn ($id) => is_numeric($id));

        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal satu siswa terlebih dahulu dengan mencentang kotaknya.');
        }

        $tahunAjaran = $this->rapikanTeks($request->input('bulk_tahun_ajaran'));
        $tambahan = ($tahunAjaran !== null && $tahunAjaran !== '') ? ['tahun_ajaran' => mb_substr($tahunAjaran, 0, 20)] : [];

        switch ($aksi) {
            case 'delete':
                // Sekarang PINDAH KE TONG SAMPAH (bisa dipulihkan), bukan dihapus permanen.
                $jumlah = Siswa::whereIn('id', $ids)->delete();
                return back()->with('success', "$jumlah data siswa dipindahkan ke Tong Sampah. Masih bisa dipulihkan dari menu Tong Sampah.");

            case 'set_x':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'X', 'status' => 'Aktif'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil dinaikkan ke Kelas X.';
                break;

            case 'set_xi':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XI', 'status' => 'Aktif'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil dinaikkan ke Kelas XI.';
                break;

            case 'set_xii':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XII', 'status' => 'Aktif'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil dinaikkan ke Kelas XII.';
                break;

            case 'set_alumni':
                Siswa::whereIn('id', $ids)->update(['status' => 'Alumni'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil diubah statusnya menjadi Alumni.';
                break;

            case 'set_aktif':
                Siswa::whereIn('id', $ids)->update(['status' => 'Aktif'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil dikembalikan statusnya menjadi Aktif.';
                break;

            case 'set_laki':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Laki-laki'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil diubah gendernya menjadi Laki-laki.';
                break;

            case 'set_perempuan':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Perempuan'] + $tambahan);
                $pesan = count($ids) . ' siswa berhasil diubah gendernya menjadi Perempuan.';
                break;

            default:
                return back()->with('error', 'Aksi massal tidak dikenali.');
        }

        return back()->with('success', $pesan);
    }

    // =====================================================================
    //  VALIDASI & PEMBERSIH DATA
    // =====================================================================

    private function validasiData(Request $request, ?Siswa $siswa = null): void
    {
        $nisnBersih = $this->bersihkanNisn($request->input('nisn'));
        $nisnLama = $siswa ? (string) $siswa->nisn : null;

        $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'nisn' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) use ($nisnBersih, $nisnLama) {
                // Data lama yang NISN-nya belum 10 digit tetap boleh disunting selama NISN-nya tidak diubah.
                if ($nisnBersih === $nisnLama) {
                    return;
                }
                if (! preg_match('/^\d{10}$/', $nisnBersih)) {
                    $fail('NISN harus 10 digit angka' . ($nisnBersih === '' ? ' (kolom NISN kosong)' : " — yang terbaca: '$nisnBersih'") . '.');
                }
            }],
            'nis' => 'nullable|string|max:30',
            'ttl' => 'nullable|string|max:150',
            'jk' => 'nullable|in:Laki-laki,Perempuan',
            'status' => 'nullable|in:Aktif,Alumni,Pindah',
            'kelas' => 'nullable|in:X,XI,XII,Lulus',
            'thn_masuk' => 'nullable|string|max:10',
            'thn_lulus' => 'nullable|string|max:10',
            'tahun_ajaran' => 'nullable|string|max:20',
            'hp_ortu' => 'nullable|string|max:30',
            'hp_wali' => 'nullable|string|max:30',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'prestasi' => 'nullable|array',
            'prestasi.*.nama' => 'nullable|string|max:150',
            'prestasi.*.tingkat' => 'nullable|string|max:60',
            'prestasi.*.tgl' => 'nullable|string|max:30',
            'pelanggaran' => 'nullable|array',
            'pelanggaran.*.kasus' => 'nullable|string|max:200',
            'pelanggaran.*.kategori' => 'nullable|string|max:60',
            'pelanggaran.*.tgl' => 'nullable|string|max:30',
            'pelanggaran.*.tindakan' => 'nullable|string|max:200',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nisn.required' => 'NISN wajib diisi dan harus 10 digit angka.',
            'jk.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'status.in' => 'Status siswa harus Aktif, Alumni, atau Pindah.',
            'kelas.in' => 'Kelas harus X, XI, XII, atau Lulus.',
            'foto.image' => 'Berkas foto harus berupa gambar (JPG, PNG, atau WEBP).',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ]);
    }

    /** NISN bentrok dengan data lain (termasuk yang ada di tong sampah). */
    private function cekNisnBentrok(string $nisn, ?int $kecualiId = null)
    {
        $query = Siswa::withTrashed()->where('nisn', $nisn);
        if ($kecualiId) {
            $query->where('id', '!=', $kecualiId);
        }
        $bentrok = $query->first();

        if (! $bentrok) {
            return null;
        }

        $pesan = $bentrok->trashed()
            ? "NISN $nisn ada di Tong Sampah atas nama {$bentrok->nama_lengkap}. Pulihkan atau hapus permanen dulu di menu Tong Sampah."
            : "NISN $nisn sudah dipakai siswa lain: {$bentrok->nama_lengkap}.";

        return redirect()->back()->withInput()->withErrors(['nisn' => $pesan]);
    }

    /** Ambil hanya kolom yang diizinkan + bersihkan isinya. */
    private function siapkanData(Request $request): array
    {
        $data = $request->only(self::KOLOM_ISI);

        $nisn = $this->bersihkanNisn($request->input('nisn'));
        $data['nisn'] = $nisn !== '' ? $nisn : $this->rapikanTeks($request->input('nisn'));
        $data['nis'] = $this->rapikanKode($request->input('nis'));
        $data['jk'] = $this->rapikanJk($request->input('jk'));
        $data['status'] = $this->rapikanStatus($request->input('status')) ?? 'Aktif';
        $data['kelas'] = $this->rapikanKelas($request->input('kelas'));

        $data['prestasi'] = $this->rapikanLogbook($request->input('prestasi'), ['tgl', 'tingkat', 'nama']);
        $data['pelanggaran'] = $this->rapikanLogbook($request->input('pelanggaran'), ['tgl', 'kategori', 'kasus', 'tindakan']);

        return $this->kosongKeNull($data);
    }

    /** Buang semua karakter selain angka (spasi, NBSP dari Excel, titik, strip). */
    private function bersihkanNisn($nilai): string
    {
        return preg_replace('/\D+/', '', $this->rapikanTeks($nilai) ?? '');
    }

    /** Kode lain (NIS, No HP): rapikan spasi tanpa membuang isi. */
    private function rapikanKode($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);

        return $teks === null ? null : str_replace(' ', '', $teks);
    }

    /** Normalisasi teks: NBSP → spasi, spasi ganda → satu, trim. */
    private function rapikanTeks($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $teks = str_replace(["\xC2\xA0", "\xE2\x80\xAF"], ' ', (string) $nilai);
        $teks = preg_replace('/\s+/u', ' ', $teks);

        return trim((string) $teks);
    }

    private function rapikanJk($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);
        if ($teks === null || $teks === '') {
            return null;
        }

        $kunci = strtolower(str_replace([' ', '-', '_', '.'], '', $teks));
        if (str_starts_with($kunci, 'l')) {
            return 'Laki-laki';
        }
        if (str_starts_with($kunci, 'p')) {
            return 'Perempuan';
        }

        return $teks;
    }

    private function rapikanStatus($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);
        if ($teks === null || $teks === '') {
            return null;
        }

        $kunci = strtolower($teks);
        if (str_starts_with($kunci, 'aktif')) {
            return 'Aktif';
        }
        if (str_starts_with($kunci, 'alumni') || str_starts_with($kunci, 'lulus')) {
            return 'Alumni';
        }
        if (str_starts_with($kunci, 'pindah') || str_starts_with($kunci, 'keluar') || str_starts_with($kunci, 'mutasi')) {
            return 'Pindah';
        }

        return in_array($teks, self::STATUS_SAH, true) ? $teks : 'Aktif';
    }

    private function rapikanKelas($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);
        if ($teks === null || $teks === '') {
            return null;
        }

        $kunci = strtoupper(str_replace([' ', '-'], '', $teks));
        $peta = ['X' => 'X', '10' => 'X', 'XI' => 'XI', '11' => 'XI', 'XII' => 'XII', '12' => 'XII', 'LULUS' => 'Lulus'];

        return $peta[$kunci] ?? $teks;
    }

    /** Rapikan baris logbook (prestasi/pelanggaran): buang baris yang benar-benar kosong. */
    private function rapikanLogbook($baris, array $kolom): ?array
    {
        if (! is_array($baris)) {
            return null;
        }

        $hasil = [];
        foreach ($baris as $item) {
            if (! is_array($item)) {
                continue;
            }
            $bersih = [];
            $adaIsi = false;
            foreach ($kolom as $k) {
                $nilai = $this->rapikanTeks($item[$k] ?? null);
                $bersih[$k] = $nilai;
                if ($nilai !== null && $nilai !== '') {
                    $adaIsi = true;
                }
            }
            if ($adaIsi) {
                $hasil[] = $bersih;
            }
        }

        return empty($hasil) ? null : $hasil;
    }

    /** String kosong → null supaya statistik "kolom terisi" tidak menipu. */
    private function kosongKeNull(array $data): array
    {
        foreach ($data as $kunci => $nilai) {
            if (in_array($kunci, ['nisn', 'prestasi', 'pelanggaran'], true)) {
                continue;
            }
            if (is_string($nilai) && trim($nilai) === '') {
                $data[$kunci] = null;
            }
        }

        return $data;
    }
}
