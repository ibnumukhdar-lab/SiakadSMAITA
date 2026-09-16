<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    private const STATUS_SAH = ['Aktif', 'Alumni', 'Pindah'];
    private const KELAS_SAH = ['X', 'XI', 'XII', 'Lulus'];
    private const PER_HALAMAN = [25, 50, 100, 200];

    // =====================================================================
    //  DAFTAR SISWA (paginasi & filter di sisi server)
    // =====================================================================

    public function index(Request $request)
    {
        $filter = $this->filterDari($request);
        $siswas = $this->queryDaftar($filter)->paginate($filter['per_halaman'])->withQueryString();

        $ringkasan = [
            'aktif'      => Siswa::count(),
            'tong_sampah'=> Siswa::onlyTrashed()->count(),
            'nisn_perlu' => Siswa::whereRaw("nisn NOT REGEXP '^[0-9]{10}$'")->count(),
            'tanpa_foto' => Siswa::whereNull('foto')->count(),
            'tanpa_alamat' => Siswa::where(function ($q) {
                $q->whereNull('alamat')->orWhere('alamat', '');
            })->count(),
        ];

        return view('siswa.index', [
            'siswas' => $siswas,
            'filter' => $filter,
            'ringkasan' => $ringkasan,
            'daftarKelas' => $this->daftarKelasSah(),
        ]);
    }

    /** Nama kelas yang sah: semua kelas di Kelola Kelas + nama lama yang masih dipakai data siswa. */
    private function daftarKelasSah(): array
    {
        $daftar = \App\Models\Kelas::terurut()->pluck('nama')->all();

        foreach (array_keys(\App\Models\Kelas::jumlahSiswaPerKelas(true)) as $nama) {
            if (! in_array($nama, $daftar, true)) {
                $daftar[] = $nama;
            }
        }

        return $daftar;
    }

    /** Saring + urut + paginasi: query dibangun sekali di sini supaya daftar, ekspor, dan cetak konsisten. */
    private function queryDaftar(array $filter)
    {
        $query = Siswa::query();

        if ($filter['q'] !== '') {
            $kata = $filter['q'];
            $query->where(function ($q) use ($kata) {
                $q->where('nama_lengkap', 'like', "%{$kata}%")
                    ->orWhere('nisn', 'like', "%{$kata}%")
                    ->orWhere('nis', 'like', "%{$kata}%");
            });
        }

        if ($filter['kelas'] !== '') {
            $query->where('kelas', $filter['kelas']);
        }
        if ($filter['status'] !== '') {
            $query->where('status', $filter['status']);
        }
        if ($filter['jk'] !== '') {
            $query->where('jk', $filter['jk']);
        }
        if ($filter['angkatan'] !== '') {
            $query->where('thn_masuk', 'like', $filter['angkatan'] . '%');
        }

        switch ($filter['perlu']) {
            case 'nisn':
                $query->whereRaw("nisn NOT REGEXP '^[0-9]{10}$'");
                break;
            case 'foto':
                $query->whereNull('foto');
                break;
            case 'kontak':
                $query->where(function ($q) {
                    $q->whereNull('hp_ortu')->orWhere('hp_ortu', '');
                })->where(function ($q) {
                    $q->whereNull('hp_wali')->orWhere('hp_wali', '');
                });
                break;
            case 'alamat':
                $query->where(function ($q) {
                    $q->whereNull('alamat')->orWhere('alamat', '');
                });
                break;
        }

        return match ($filter['urut']) {
            'nama'   => $query->orderBy('nama_lengkap'),
            'nisn'   => $query->orderBy('nisn'),
            'kelas'  => $query->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")->orderBy('nama_lengkap'),
            'lama'   => $query->orderBy('created_at'),
            default  => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    private function filterDari(Request $request): array
    {
        $perHalaman = (int) $request->input('per_halaman', 25);

        return [
            'q'           => trim((string) $request->input('q', '')),
            'kelas'       => mb_substr(trim((string) $request->input('kelas', '')), 0, 60),
            'status'      => in_array($request->input('status'), self::STATUS_SAH, true) ? $request->input('status') : '',
            'jk'          => in_array($request->input('jk'), ['Laki-laki', 'Perempuan'], true) ? $request->input('jk') : '',
            'angkatan'    => preg_replace('/[^0-9]/', '', (string) $request->input('angkatan', '')),
            'perlu'       => in_array($request->input('perlu'), ['nisn', 'foto', 'kontak', 'alamat'], true) ? $request->input('perlu') : '',
            'urut'        => in_array($request->input('urut'), ['nama', 'nisn', 'kelas', 'lama', 'baru'], true) ? $request->input('urut') : 'baru',
            'per_halaman' => in_array($perHalaman, self::PER_HALAMAN, true) ? $perHalaman : 25,
        ];
    }

    // =====================================================================
    //  EKSPOR & CETAK
    // =====================================================================

    public function ekspor(Request $request)
    {
        $filter = $this->filterDari($request);
        $daftar = $this->queryDaftar($filter)->get();

        $namaBerkas = 'Data-Siswa-' . date('Ymd-Hi') . '.csv';

        return response()->streamDownload(function () use ($daftar) {
            $keluar = fopen('php://output', 'w');
            fwrite($keluar, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar

            fputcsv($keluar, [
                'NISN', 'NIS', 'Nama Lengkap', 'Jenis Kelamin', 'Kelas', 'Status',
                'Tempat Tgl Lahir', 'Tahun Masuk', 'Tahun Ajaran', 'No HP Ortu',
                'Nama Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Pekerjaan Ibu',
                'Nama Wali', 'No HP Wali', 'Alamat', 'Tinggal Bersama', 'Jarak (km)',
                'Transportasi', 'No KIP/PKH', 'Asal Sekolah', 'Riwayat Penyakit',
            ]);

            foreach ($daftar as $s) {
                fputcsv($keluar, [
                    $s->nisn, $s->nis, $s->nama_lengkap, $s->jk, $s->kelas, $s->status,
                    $s->ttl, $s->thn_masuk, $s->tahun_ajaran, $s->hp_ortu,
                    $s->nama_ayah, $s->pekerjaan_ayah, $s->nama_ibu, $s->pekerjaan_ibu,
                    $s->nama_wali, $s->hp_wali, $s->alamat, $s->tinggal_bersama, $s->jarak,
                    $s->transportasi, $s->kesejahteraan, $s->asal_sekolah, $s->penyakit,
                ]);
            }

            fclose($keluar);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function cetak(Request $request)
    {
        $filter = $this->filterDari($request);
        $daftar = $this->queryDaftar($filter)->get();

        // Untuk cetak, dikelompokkan per kelas supaya rapi dibaca wali kelas.
        $perKelas = $daftar->groupBy(fn ($s) => $s->kelas ?: 'Tanpa Kelas')
            ->sortKeysUsing(fn ($a, $b) => array_search($a, self::KELAS_SAH) <=> array_search($b, self::KELAS_SAH));

        return view('siswa.cetak', [
            'perKelas'  => $perKelas,
            'total'     => $daftar->count(),
            'filter'    => $filter,
            'pengaturan'=> \App\Models\Pengaturan::first(),
        ]);
    }

    public function kartu(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        // Ambil sekumpulan siswa sekaligus? Tidak: satu kartu per siswa, tapi bisa dicetak berurutan.
        return view('siswa.kartu', [
            'siswa'     => $siswa,
            'pengaturan'=> \App\Models\Pengaturan::first(),
        ]);
    }

    // =====================================================================
    //  FORM
    // =====================================================================

    public function create()
    {
        return view('siswa.create', [
            'daftarKelas' => \App\Models\Kelas::daftarNama(true),
            'tahunAktif' => \App\Models\TahunAjaran::namaAktif(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validasiData($request);

        $data = $this->siapkanData($request);

        if ($balasan = $this->cekNisnBentrok($data['nisn'])) {
            return $balasan;
        }

        // Tahun ajaran kosong → diisi tahun ajaran yang sedang aktif.
        if (empty($data['tahun_ajaran'])) {
            $data['tahun_ajaran'] = \App\Models\TahunAjaran::namaAktif();
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
        }

        Siswa::create($data);

        return redirect()->route('siswa.index')
            ->with('success', 'Data siswa ' . $data['nama_lengkap'] . ' berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $siswa = Siswa::findOrFail($id);

        return view('siswa.edit', [
            'siswa' => $siswa,
            'daftarKelas' => \App\Models\Kelas::daftarNama(true),
        ]);
    }

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

        return redirect()->route('siswa.index')->with('success', 'Data siswa ' . $siswa->nama_lengkap . ' berhasil diperbarui!');
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
    //  TONG SAMPAH
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
    //  IMPOR CSV — unggah → PRATINJAU → eksekusi
    // =====================================================================

    public function importForm()
    {
        return view('siswa.import');
    }

    /** Langkah 1: baca & validasi berkas, simpan hasilnya sebagai pratinjau (belum menyentuh data). */
    public function prosesImport(Request $request)
    {
        if (! $request->hasFile('file_csv')) {
            return redirect()->back()->with('error', 'Silakan pilih file CSV terlebih dahulu.');
        }

        $file = $request->file('file_csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return redirect()->route('siswa.import')->with('error', 'Berkas CSV tidak bisa dibaca.');
        }

        $barisPertama = (string) fgets($handle);
        $delimiter = (substr_count($barisPertama, ';') > substr_count($barisPertama, ',')) ? ';' : ',';
        rewind($handle);

        $judul = fgetcsv($handle, 0, $delimiter) ?: [];
        $peta = $this->petakanKolom($judul);

        $siapSimpan = [];
        $masalah = [];
        $duplikat = [];
        $nomor = 1;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $nomor++;

            $isiSel = array_filter((array) $data, fn ($v) => $this->rapikanTeks($v) !== '' && $this->rapikanTeks($v) !== null);
            if (empty($isiSel)) {
                continue;
            }

            $ambil = function (string $kunci) use ($data, $peta) {
                if (! isset($peta['peta'][$kunci])) {
                    return null;
                }
                $i = $peta['peta'][$kunci];
                return isset($data[$i]) ? $this->rapikanTeks($data[$i]) : null;
            };

            $nama = (string) $ambil('nama_lengkap');
            $nis = $this->rapikanKode($ambil('nis'));
            $nisn = $this->bersihkanNisn($ambil('nisn'));

            if ($nama === '') {
                $masalah[] = ['baris' => $nomor, 'nama' => '(tanpa nama)', 'alasan' => 'Kolom Nama Lengkap kosong'];
                continue;
            }
            if ($nisn === '') {
                $masalah[] = ['baris' => $nomor, 'nama' => $nama, 'alasan' => 'NISN kosong'];
                continue;
            }
            if (strlen($nisn) > 20) {
                $masalah[] = ['baris' => $nomor, 'nama' => $nama, 'alasan' => "NISN '$nisn' lebih dari 20 karakter"];
                continue;
            }
            if (strlen($nisn) !== 10) {
                $masalah[] = ['baris' => $nomor, 'nama' => $nama, 'alasan' => "NISN '$nisn' bukan 10 digit (" . strlen($nisn) . ' digit)'];
                continue;
            }
            if (isset($siapSimpan[$nisn])) {
                $masalah[] = ['baris' => $nomor, 'nama' => $nama, 'alasan' => "NISN $nisn muncul dua kali di berkas ini"];
                continue;
            }

            $sudahAda = Siswa::withTrashed()->where('nisn', $nisn)->first();
            if ($sudahAda) {
                if ($sudahAda->trashed()) {
                    $masalah[] = ['baris' => $nomor, 'nama' => $nama, 'alasan' => "NISN $nisn ada di Tong Sampah atas nama {$sudahAda->nama_lengkap}"];
                } else {
                    $duplikat[] = ['baris' => $nomor, 'nama' => $nama, 'nisn' => $nisn, 'alasan' => "Sudah terdaftar sebagai {$sudahAda->nama_lengkap}"];
                }
                continue;
            }

            $siapSimpan[$nisn] = [
                'nama_lengkap'    => $nama,
                'nis'             => $nis,
                'nisn'            => $nisn,
                'ttl'             => $ambil('ttl'),
                'jk'              => $this->rapikanJk($ambil('jk')),
                'status'          => $this->rapikanStatus($ambil('status')) ?? 'Aktif',
                'kelas'           => $this->rapikanKelas($ambil('kelas')),
                'thn_masuk'       => $ambil('thn_masuk'),
                'hp_ortu'         => $ambil('hp_ortu'),
                'nama_ayah'       => $ambil('nama_ayah'),
                'status_ayah'     => $ambil('status_ayah') ?: 'Masih Hidup',
                'pekerjaan_ayah'  => $ambil('pekerjaan_ayah'),
                'nama_ibu'        => $ambil('nama_ibu'),
                'status_ibu'      => $ambil('status_ibu') ?: 'Masih Hidup',
                'pekerjaan_ibu'   => $ambil('pekerjaan_ibu'),
                'nama_wali'       => $ambil('nama_wali'),
                'pekerjaan_wali'  => $ambil('pekerjaan_wali'),
                'hp_wali'         => $ambil('hp_wali'),
                'kesejahteraan'   => $ambil('kesejahteraan'),
                'tinggal_bersama' => $ambil('tinggal_bersama'),
                'jarak'           => $ambil('jarak'),
                'transportasi'    => $ambil('transportasi'),
                'asal_sekolah'    => $ambil('asal_sekolah'),
                'alamat'          => $ambil('alamat'),
                'penyakit'        => $ambil('penyakit'),
                'tahun_ajaran'    => $ambil('tahun_ajaran') ?: \App\Models\TahunAjaran::namaAktif(),
            ];
        }
        fclose($handle);

        if (empty($siapSimpan) && empty($masalah) && empty($duplikat)) {
            return redirect()->route('siswa.import')->with('error', 'Tidak ada baris data yang bisa dibaca dari berkas itu.');
        }

        $token = (string) Str::uuid();
        $this->bersihkanPratinjauLama();
        $this->simpanPratinjau($token, [
            'berkas'    => $file->getClientOriginalName(),
            'peta'      => $peta,
            'siap'      => array_values($siapSimpan),
            'masalah'   => $masalah,
            'duplikat'  => $duplikat,
            'dibuat'    => now()->toDateTimeString(),
        ]);

        return redirect()->route('siswa.pratinjau', ['token' => $token]);
    }

    /** Langkah 2: halaman pratinjau — belum ada data yang masuk. */
    public function pratinjau(Request $request)
    {
        $data = $this->bacaPratinjau((string) $request->query('token'));

        if (! $data) {
            return redirect()->route('siswa.import')->with('error', 'Pratinjau sudah kedaluwarsa. Silakan unggah ulang berkasnya.');
        }

        return view('siswa.import-pratinjau', [
            'data'  => $data,
            'token' => $request->query('token'),
        ]);
    }

    /** Langkah 3: benar-benar menyimpan baris yang lolos validasi. */
    public function eksekusiImpor(Request $request)
    {
        $token = (string) $request->input('token');
        $data = $this->bacaPratinjau($token);

        if (! $data) {
            return redirect()->route('siswa.import')->with('error', 'Pratinjau sudah kedaluwarsa. Silakan unggah ulang berkasnya.');
        }

        $berhasil = 0;
        $bentrok = [];

        DB::transaction(function () use ($data, &$berhasil, &$bentrok) {
            foreach ($data['siap'] as $baris) {
                // Cek ulang saat eksekusi: bisa saja NISN terisi sejak pratinjau dibuat.
                if (Siswa::withTrashed()->where('nisn', $baris['nisn'])->exists()) {
                    $bentrok[] = $baris['nama_lengkap'] . ' (' . $baris['nisn'] . ')';
                    continue;
                }
                Siswa::create($this->kosongKeNull($baris));
                $berhasil++;
            }
        });

        $this->hapusPratinjau($token);

        $pesan = "Impor selesai: $berhasil siswa masuk.";
        if (count($bentrok) > 0) {
            $pesan .= ' ' . count($bentrok) . ' baris batal karena NISN-nya sudah terpakai: ' . implode(', ', array_slice($bentrok, 0, 5)) . '.';
        }

        return redirect()->route('siswa.index')->with('success', $pesan);
    }

    /** Unduh daftar baris bermasalah dari pratinjau (untuk dibetulkan di Excel). */
    public function laporanImpor(Request $request)
    {
        $data = $this->bacaPratinjau((string) $request->query('token'));

        if (! $data) {
            return redirect()->route('siswa.import')->with('error', 'Pratinjau sudah kedaluwarsa.');
        }

        $namaBerkas = 'Masalah-Impor-Siswa-' . date('Ymd-Hi') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $keluar = fopen('php://output', 'w');
            fwrite($keluar, "\xEF\xBB\xBF");
            fputcsv($keluar, ['Baris di berkas', 'Nama', 'Masalah', 'NISN']);
            foreach ($data['masalah'] as $m) {
                fputcsv($keluar, [$m['baris'], $m['nama'], $m['alasan'], '']);
            }
            foreach ($data['duplikat'] as $d) {
                fputcsv($keluar, [$d['baris'], $d['nama'], $d['alasan'], $d['nisn']]);
            }
            fclose($keluar);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Template_Import_Siswa.csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
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

    /** Petakan judul kolom CSV ke nama field (tahan urutan acak & huruf besar/kecil). */
    private function petakanKolom(array $judul): array
    {
        $alias = [
            'nama_lengkap'    => ['namalengkap', 'nama', 'namasiswa', 'namapesertadidik'],
            'nis'             => ['nis', 'nislokal', 'nomorinduk'],
            'nisn'            => ['nisn', 'nomornisn', 'nisnnasional'],
            'ttl'             => ['tempatTGLLahir', 'tempattgllahir', 'ttl', 'tempatlahir', 'tanggallahir', 'tempat tanggallahir'],
            'jk'              => ['jeniskelamin', 'jk', 'gender', 'lp'],
            'status'          => ['status', 'statussiswa', 'statuspesertadidik'],
            'kelas'           => ['kelas', 'rombel', 'kelasrombel'],
            'thn_masuk'       => ['tahunmasuk', 'thnmasuk', 'angkatan', 'tahunmasuk'],
            'hp_ortu'         => ['nohputamaortu', 'hp_ortu', 'hp', 'nohp', 'nohpwali', 'notelpon', 'telepon', 'nohportu', 'nohp orangtua'],
            'nama_ayah'       => ['namaayah', 'ayah'],
            'status_ayah'     => ['statusayah'],
            'pekerjaan_ayah'  => ['pekerjaanayah', 'kerjaayah'],
            'nama_ibu'        => ['namaibu', 'ibu'],
            'status_ibu'      => ['statusibu'],
            'pekerjaan_ibu'   => ['pekerjaanibu', 'kerjaibu'],
            'nama_wali'       => ['namawali', 'wali'],
            'pekerjaan_wali'  => ['pekerjaanwali'],
            'hp_wali'         => ['nohpwali', 'hpwali'],
            'kesejahteraan'   => ['nokippkh', 'kip', 'pkh', 'kesejahteraan', 'nokip'],
            'tinggal_bersama' => ['tinggalbersama', 'status tinggal', 'statustinggal'],
            'jarak'           => ['jarakkm', 'jarak', 'jaraksekolah'],
            'transportasi'    => ['transportasi', 'modatransportasi'],
            'asal_sekolah'    => ['asalsekolah', 'sekolahasal', 'asalsmp'],
            'alamat'          => ['alamatlengkap', 'alamat', 'alamatdomisili'],
            'penyakit'        => ['riwayatpenyakit', 'penyakit'],
            'tahun_ajaran'    => ['tahunajaran', 'thnajaran', 'tahunpelajaran'],
        ];

        // Bersihkan judul: huruf kecil, buang tanda baca & spasi ganda
        $bersih = array_map(function ($j) {
            $t = strtolower($this->rapikanTeks($j) ?? '');
            return preg_replace('/[^a-z0-9 ]/', '', $t);
        }, $judul);

        $peta = [];
        $dikenali = [];
        foreach ($alias as $field => $kandidat) {
            foreach ($bersih as $i => $judulBersih) {
                if ($judulBersih === '') {
                    continue;
                }
                $tanpaSpasi = str_replace(' ', '', $judulBersih);
                foreach ($kandidat as $k) {
                    $k = str_replace(' ', '', strtolower($k));
                    if ($tanpaSpasi === $k || str_replace(' ', '', $judulBersih) === $k) {
                        if (! isset($peta[$field])) {
                            $peta[$field] = $i;
                            $dikenali[$field] = trim((string) ($judul[$i] ?? ''));
                        }
                        break 2;
                    }
                }
            }
        }

        // Judul tidak dikenali (mis. berkas tanpa baris judul) → pakai urutan bawaan
        if (count($peta) < 3) {
            $urutan = ['nama_lengkap', 'nis', 'nisn', 'ttl', 'jk', 'status', 'kelas', 'thn_masuk', 'hp_ortu',
                'nama_ayah', 'status_ayah', 'pekerjaan_ayah', 'nama_ibu', 'status_ibu', 'pekerjaan_ibu',
                'nama_wali', 'pekerjaan_wali', 'hp_wali', 'kesejahteraan', 'tinggal_bersama', 'jarak',
                'transportasi', 'asal_sekolah', 'alamat', 'penyakit', 'tahun_ajaran'];

            return ['peta' => array_combine($urutan, range(0, count($urutan) - 1)), 'dikenali' => [], 'mode' => 'urutan'];
        }

        return ['peta' => $peta, 'dikenali' => $dikenali, 'mode' => 'judul'];
    }

    // =====================================================================
    //  PRATINJAU: simpan/baca/hapus berkas sementara
    // =====================================================================

    private function folderPratinjau(): string
    {
        $folder = storage_path('app/pratinjau-impor');
        if (! is_dir($folder)) {
            @mkdir($folder, 0775, true);
        }
        return $folder;
    }

    private function simpanPratinjau(string $token, array $data): void
    {
        file_put_contents($this->folderPratinjau() . DIRECTORY_SEPARATOR . $token . '.json', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function bacaPratinjau(string $token): ?array
    {
        if (! preg_match('/^[0-9a-fA-F-]{36}$/', $token)) {
            return null;
        }
        $berkas = $this->folderPratinjau() . DIRECTORY_SEPARATOR . $token . '.json';
        if (! is_file($berkas)) {
            return null;
        }
        $isi = json_decode((string) file_get_contents($berkas), true);

        return is_array($isi) ? $isi : null;
    }

    private function hapusPratinjau(string $token): void
    {
        if (preg_match('/^[0-9a-fA-F-]{36}$/', $token)) {
            @unlink($this->folderPratinjau() . DIRECTORY_SEPARATOR . $token . '.json');
        }
    }

    /** Pratinjau yang tidak dieksekusi dibuang setelah 6 jam. */
    private function bersihkanPratinjauLama(): void
    {
        foreach ((array) glob($this->folderPratinjau() . DIRECTORY_SEPARATOR . '*.json') as $berkas) {
            if (is_file($berkas) && filemtime($berkas) < time() - 6 * 3600) {
                @unlink($berkas);
            }
        }
    }

    // =====================================================================
    //  PROFIL & KARTU
    // =====================================================================

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
    //  AKSI MASSAL
    // =====================================================================

    public function bulkAction(Request $request)
    {
        $aksi = (string) $request->input('bulk_action_type');

        $sah = ['delete', 'set_kelas', 'set_x', 'set_xi', 'set_xii', 'set_alumni', 'set_aktif', 'set_laki', 'set_perempuan'];

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
                $jumlah = Siswa::whereIn('id', $ids)->delete();
                return back()->with('success', "$jumlah data siswa dipindahkan ke Tong Sampah. Masih bisa dipulihkan dari menu Tong Sampah.");

            case 'set_kelas':
                $kelasBaru = $this->rapikanTeks($request->input('bulk_kelas'));
                if ($kelasBaru === null || $kelasBaru === '' || ! in_array($kelasBaru, $this->daftarKelasSah(), true)) {
                    return back()->with('error', 'Pilih dulu kelas tujuan yang terdaftar di menu Kelola Kelas.');
                }
                Siswa::whereIn('id', $ids)->update(['kelas' => $kelasBaru] + $tambahan);

                return back()->with('success', count($ids) . ' siswa dipindahkan ke kelas ' . $kelasBaru . '.');

            case 'set_x':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'X', 'status' => 'Aktif'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil dinaikkan ke Kelas X.');

            case 'set_xi':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XI', 'status' => 'Aktif'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil dinaikkan ke Kelas XI.');

            case 'set_xii':
                Siswa::whereIn('id', $ids)->update(['kelas' => 'XII', 'status' => 'Aktif'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil dinaikkan ke Kelas XII.');

            case 'set_alumni':
                Siswa::whereIn('id', $ids)->update(['status' => 'Alumni'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil diubah statusnya menjadi Alumni.');

            case 'set_aktif':
                Siswa::whereIn('id', $ids)->update(['status' => 'Aktif'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil dikembalikan statusnya menjadi Aktif.');

            case 'set_laki':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Laki-laki'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil diubah gendernya menjadi Laki-laki.');

            case 'set_perempuan':
                Siswa::whereIn('id', $ids)->update(['jk' => 'Perempuan'] + $tambahan);
                return back()->with('success', count($ids) . ' siswa berhasil diubah gendernya menjadi Perempuan.');
        }

        return back()->with('error', 'Aksi massal tidak dikenali.');
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
            'kelas' => ['nullable', 'string', 'max:60', function ($attribute, $value, $fail) use ($siswa) {
                $nilai = $this->rapikanTeks($value);
                if ($nilai === null || $nilai === '') {
                    return;
                }
                // Data lama yang kelasnya belum terdaftar tetap boleh disimpan apa adanya.
                if ($siswa && $nilai === (string) $siswa->kelas) {
                    return;
                }
                if (! in_array($nilai, $this->daftarKelasSah(), true)) {
                    $fail('Kelas "' . $nilai . '" belum terdaftar. Tambahkan dulu lewat menu Kelola Kelas.');
                }
            }],
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

    private function bersihkanNisn($nilai): string
    {
        return preg_replace('/\D+/', '', $this->rapikanTeks($nilai) ?? '');
    }

    private function rapikanKode($nilai): ?string
    {
        $teks = $this->rapikanTeks($nilai);

        return $teks === null ? null : str_replace(' ', '', $teks);
    }

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
