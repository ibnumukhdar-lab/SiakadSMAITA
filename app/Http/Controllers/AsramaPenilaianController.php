<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar;
use App\Models\AsramaMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AsramaPenilaianController extends Controller
{
    // =========================================================
    // ATURAN BATAS KAMAR TERKOTOR (permintaan Fahri, 17 Sep 2026)
    // Kamar hanya boleh ditandai TERKOTOR bila nilainya di bawah
    // BATAS_TERKOTOR_PERSEN dari skor maksimal. Bila semua kamar >= batas itu,
    // statusnya BERSIH (tidak ada poin -1) dan kamar paling bawah hanya masuk
    // daftar "perlu diperhatikan" + catatan inspektor.
    // =========================================================
    public const SKOR_MAKS = 25;                 // 5 kriteria × skor maksimal 5
    public const BATAS_TERKOTOR_PERSEN = 70;     // batas bawah (%) untuk menyebut kamar "terkotor"

    // =========================================================
    // DUA SESI INSPEKSI (permintaan Fahri, 17 Sep 2026)
    // Pagi dinilai 05.00–09.00 WIB, sore 17.00–18.00 WIB.
    // Sesi hanya menentukan JAM PENGISIAN; penilaian & finalisasi tiap sesi
    // berdiri sendiri (poin terbersih/terkotor keluar per sesi).
    // =========================================================
    public const SESI = [
        'pagi' => ['label' => 'Sesi Pagi', 'ikon' => '🌅', 'mulai' => '05:00', 'selesai' => '09:00'],
        'sore' => ['label' => 'Sesi Sore', 'ikon' => '🌇', 'mulai' => '17:00', 'selesai' => '18:00'],
    ];

    public static function daftarSesi(): array
    {
        return self::SESI;
    }

    public static function labelSesi($sesi): string
    {
        return self::SESI[$sesi]['label'] ?? 'Sesi Pagi';
    }

    /** Jam buka–tutup sesi, mis. "05.00–09.00 WIB". */
    public static function jendelaSesi($sesi): string
    {
        $s = self::SESI[$sesi] ?? self::SESI['pagi'];

        return str_replace(':', '.', $s['mulai']) . '–' . str_replace(':', '.', $s['selesai']) . ' WIB';
    }

    /** Sesi yang jendelanya sedang terbuka; null bila di luar kedua jendela. */
    public static function sesiSekarang(): ?string
    {
        $jam = now()->format('H:i');

        foreach (self::SESI as $kunci => $s) {
            if ($jam >= $s['mulai'] && $jam <= $s['selesai']) {
                return $kunci;
            }
        }

        return null;
    }

    public static function dalamJendela($sesi): bool
    {
        if (! isset(self::SESI[$sesi])) {
            return false;
        }

        $jam = now()->format('H:i');

        return $jam >= self::SESI[$sesi]['mulai'] && $jam <= self::SESI[$sesi]['selesai'];
    }

    /**
     * Boleh mengisi sesi ini?
     * Musyrif: hanya di dalam jendela jamnya.
     * Pengelola kamar (Kepala Diniyah / Super Admin): kapan saja, untuk perbaikan data.
     */
    public static function bolehIsi($sesi): bool
    {
        if (self::dalamJendela($sesi)) {
            return true;
        }

        $user = Auth::user();

        return (bool) ($user && $user->can('buka-menu-manajemen-kamar'));
    }

    public static function pesanTutupSesi($sesi): string
    {
        return self::labelSesi($sesi) . ' hanya bisa diisi pada jam ' . self::jendelaSesi($sesi)
            . '. Kalau memang perlu diisi di luar jam tersebut, minta Kepala Diniyah / Super Admin melakukannya.';
    }

    /** Ambang nilai absolut: 70% dari 25 = 17,5 → kamar dengan total < 17,5 (yaitu maks 17) yang boleh disebut terkotor. */
    public static function batasTerkotor(): float
    {
        return self::SKOR_MAKS * self::BATAS_TERKOTOR_PERSEN / 100;
    }

    /** Persentase nilai kamar terhadap skor maksimal. */
    public static function persenSkor($total): float
    {
        return round(((float) $total) / self::SKOR_MAKS * 100, 1);
    }

    // =========================================================
    // 1. FUNGSI INDEX (HALAMAN HISTORI INSPEKSI)
    // =========================================================
    public function index()
    {
        $histori = AsramaPenilaian::with(['kamarTerbersih', 'kamarTerkotor', 'kamarPerhatian', 'musyrif', 'rincianKamars'])
                    ->where('status', 'final')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('sesi', 'desc')
                    ->get();

        // Lembar DRAFT yang menggantung (bukan hari ini) — dulu menumpuk tanpa bisa dibersihkan.
        $drafts = AsramaPenilaian::with('musyrif')
                    ->withCount('rincianKamars')
                    ->where('status', 'draft')
                    ->where('tanggal', '<', now()->toDateString())
                    ->orderBy('tanggal', 'desc')
                    ->get();

        return view('asrama.penilaian.index', compact('histori', 'drafts'));
    }

    // =========================================================
    // 2. FUNGSI DASHBOARD (KLASEMEN)
    // =========================================================
    public function dashboard()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $semuaKamar = AsramaKamar::where('status', 'aktif')->get();
        if ($semuaKamar->count() == 0) {
            $semuaKamar = AsramaKamar::all();
        }

        $totalKamarPutra = $semuaKamar->filter(fn ($k) => strtolower($k->kategori ?? '') == 'putra')->count();
        $totalKamarPutri = $semuaKamar->filter(fn ($k) => strtolower($k->kategori ?? '') == 'putri')->count();

        $inspeksiFinalBulanIni = AsramaPenilaian::where('status', 'final')
                                    ->whereMonth('tanggal', $bulanIni)
                                    ->whereYear('tanggal', $tahunIni)
                                    ->pluck('id');

        $totalInspeksiPutra = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putra')->count();
        $totalInspeksiPutri = AsramaPenilaian::whereIn('id', $inspeksiFinalBulanIni)->where('kategori', 'putri')->count();

        $penilaianDetail = AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksiFinalBulanIni)->get();
        $users = DB::table('users')->pluck('name', 'id');

        $rankingKamarRaw = $semuaKamar->map(function ($kamar) use ($penilaianDetail, $users) {
            $sidakKamarIni = $penilaianDetail->where('kamar_id', $kamar->id);
            $jumlahSidak = $sidakKamarIni->count();

            $rataRata = $jumlahSidak > 0 ? $sidakKamarIni->avg('total_skor') : 0;

            $musyrifId = $kamar->musyrif_id ?? null;
            $namaMusyrif = $musyrifId ? ($users[$musyrifId] ?? 'Belum Diatur') : 'Belum Diatur';

            return (object) [
                'id' => $kamar->id,
                'nama_kamar' => $kamar->nama_kamar ?? 'Kamar ' . $kamar->id,
                'nama_musyrif' => $namaMusyrif,
                'kategori' => strtolower($kamar->kategori ?? ''),
                'jumlah_sidak' => $jumlahSidak,
                'rata_rata_skor' => (float) $rataRata,
            ];
        });

        $rankingKamar = $rankingKamarRaw->sortByDesc('rata_rata_skor')->values();

        $rankingPutra = $rankingKamar->where('kategori', 'putra')->values();
        $putraAktif = $rankingPutra->filter(fn ($k) => $k->jumlah_sidak > 0)->values();
        $putraTerbersih = $putraAktif->take(3);
        $putraTerkotor = $putraAktif->filter(fn ($k) => $k->rata_rata_skor < self::batasTerkotor())
                                    ->reverse()->take(3)->values();

        $rankingPutri = $rankingKamar->where('kategori', 'putri')->values();
        $putriAktif = $rankingPutri->filter(fn ($k) => $k->jumlah_sidak > 0)->values();
        $putriTerbersih = $putriAktif->take(3);
        $putriTerkotor = $putriAktif->filter(fn ($k) => $k->rata_rata_skor < self::batasTerkotor())
                                    ->reverse()->take(3)->values();

        return view('asrama.dashboard', compact(
            'totalKamarPutra', 'totalKamarPutri',
            'totalInspeksiPutra', 'totalInspeksiPutri',
            'rankingPutra', 'rankingPutri',
            'putraTerbersih', 'putraTerkotor',
            'putriTerbersih', 'putriTerkotor'
        ));
    }

    // =========================================================
    // 3. FUNGSI HARI INI — dua sesi (pagi & sore) × dua divisi
    // =========================================================
    /**
     * Divisi bawaan saat halaman inspeksi dibuka tanpa parameter:
     * musyrif yang hanya membina satu divisi langsung dibukakan divisi itu.
     */
    private function divisiBawaan(): string
    {
        $user = Auth::user();

        if ($user && ! $user->can('buka-menu-manajemen-kamar')) {
            $kategori = AsramaKamar::where('musyrif_id', $user->id)->pluck('kategori')->unique();

            if ($kategori->count() === 1) {
                return strtolower((string) $kategori->first()) === 'putri' ? 'putri' : 'putra';
            }
        }

        return 'putra';
    }

    public function hariIni(Request $request)
    {
        $tanggal = now()->toDateString();

        // Halaman inspeksi DIPISAH per divisi (permintaan Fahri, 17 Sep 2026):
        // /asrama/penilaian/hari-ini?divisi=putra  |  ?divisi=putri
        $divisi = in_array($request->input('divisi'), ['putra', 'putri'], true)
            ? $request->input('divisi')
            : $this->divisiBawaan();

        $kamars = AsramaKamar::where('status', 'aktif')->orderBy('nama_kamar', 'asc')->get();
        if ($kamars->count() == 0) {
            $kamars = AsramaKamar::orderBy('nama_kamar', 'asc')->get();
        }

        if ($kamars->count() == 0) {
            return redirect('/asrama/kamar')->with('error', 'Belum ada data kamar sama sekali di database.');
        }

        $kamarDivisi = $kamars->filter(fn ($k) => strtolower($k->kategori ?? '') == $divisi)->values();
        $kamarIds = $kamarDivisi->pluck('id')->toArray();

        $lembar = [];

        foreach (array_keys(self::SESI) as $sesi) {
            $draft = AsramaPenilaian::firstOrCreate(
                ['tanggal' => $tanggal, 'kategori' => $divisi, 'sesi' => $sesi],
                ['status' => 'draft']
            );

            // Auto-cleanup data hantu: skor kamar yang sudah dihapus/dinonaktifkan
            if (! empty($kamarIds)) {
                AsramaPenilaianKamar::where('penilaian_id', $draft->id)
                    ->whereNotIn('kamar_id', $kamarIds)
                    ->delete();
            }

            $rincian = AsramaPenilaianKamar::where('penilaian_id', $draft->id)->get();
            $dinilai = $rincian->pluck('kamar_id')->toArray();
            $selesai = ($kamarDivisi->count() > 0 && count($dinilai) == $kamarDivisi->count());

            $lembar[$sesi] = [
                'draft'    => $draft,
                'kamar'    => $kamarDivisi,
                'dinilai'  => $dinilai,
                'skor'     => $rincian->keyBy('kamar_id'),   // untuk mengisi ulang modal saat "Koreksi"
                'selesai'  => $selesai,
                'kandidat' => $selesai
                    ? $rincian->sortByDesc('total_skor')->values()
                    : null,
            ];
        }

        // Progres tiap divisi untuk label tab (jumlah kamar per divisi + yang sudah dinilai per sesi)
        $progres = [];
        foreach (['putra', 'putri'] as $kat) {
            $progres[$kat]['jumlah'] = $kamars->filter(fn ($k) => strtolower($k->kategori ?? '') == $kat)->count();

            foreach (array_keys(self::SESI) as $sesi) {
                $p = AsramaPenilaian::where('tanggal', $tanggal)
                        ->where('kategori', $kat)
                        ->where('sesi', $sesi)
                        ->first();

                $progres[$kat][$sesi] = $p ? AsramaPenilaianKamar::where('penilaian_id', $p->id)->count() : 0;
            }
        }

        $sesiSekarang = self::sesiSekarang();

        return view('asrama.penilaian.hari-ini', compact('tanggal', 'lembar', 'sesiSekarang', 'divisi', 'kamarDivisi', 'progres'));
    }

    // =========================================================
    // 4. FUNGSI SIMPAN SKOR (per kamar, per sesi)
    // =========================================================
    public function simpanSkor(Request $request, $kamar_id)
    {
        $kamar = AsramaKamar::findOrFail($kamar_id);

        $request->validate([
            'sesi'   => 'required|in:' . implode(',', array_keys(self::SESI)),
            'skor_1' => 'required|integer|min:1|max:5',
            'skor_2' => 'required|integer|min:1|max:5',
            'skor_3' => 'required|integer|min:1|max:5',
            'skor_4' => 'required|integer|min:1|max:5',
            'skor_5' => 'required|integer|min:1|max:5',
        ], [
            'sesi.required' => 'Sesi inspeksi tidak terbaca. Buka halaman Inspeksi Hari Ini lalu coba lagi.',
        ]);

        $sesi = $request->sesi;

        if (! self::bolehIsi($sesi)) {
            return back()->with('error', self::pesanTutupSesi($sesi));
        }

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
            ->where('kategori', $kamar->kategori)
            ->where('sesi', $sesi)
            ->where('status', 'draft')
            ->first();

        if (! $penilaian) {
            return back()->with('error', self::labelSesi($sesi) . ' divisi ' . ucfirst($kamar->kategori) . ' hari ini sudah difinalisasi (terkunci).');
        }

        $total_skor = $request->skor_1 + $request->skor_2 + $request->skor_3 + $request->skor_4 + $request->skor_5;

        AsramaPenilaianKamar::updateOrCreate(
            ['penilaian_id' => $penilaian->id, 'kamar_id' => $kamar->id],
            [
                'skor_1' => $request->skor_1, 'skor_2' => $request->skor_2, 'skor_3' => $request->skor_3,
                'skor_4' => $request->skor_4, 'skor_5' => $request->skor_5, 'total_skor' => $total_skor,
                'catatan' => $request->catatan,
            ]
        );

        return back()->with('success', '⭐ Skor kamar ' . $kamar->nama_kamar . ' (' . self::labelSesi($sesi) . ') tersimpan!');
    }

    // =========================================================
    // 5. FUNGSI KONFIRMASI PENGUNCIAN (per sesi)
    // =========================================================
    public function konfirmasi(Request $request)
    {
        $kategori = in_array($request->kategori, ['putra', 'putri'], true) ? $request->kategori : 'putra';
        $sesi = array_key_exists((string) $request->sesi, self::SESI) ? (string) $request->sesi : (self::sesiSekarang() ?: 'pagi');

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                    ->where('kategori', $kategori)
                    ->where('sesi', $sesi)
                    ->where('status', 'draft')
                    ->first();

        if (! $penilaian) {
            return redirect('/asrama/penilaian/hari-ini')->with('error', self::labelSesi($sesi) . ' divisi ' . ucfirst($kategori) . ' hari ini sudah dikunci (final).');
        }

        $dinilai = AsramaPenilaianKamar::with('kamar')->where('penilaian_id', $penilaian->id)->get();

        if ($dinilai->count() == 0) {
            return redirect('/asrama/penilaian/hari-ini')->with('error', 'Belum ada kamar ' . $kategori . ' yang dinilai pada ' . self::labelSesi($sesi) . ' hari ini.');
        }

        $dinilai = $dinilai->sortByDesc('total_skor')->values();

        $maxSkor = (int) $dinilai->max('total_skor');
        $minSkor = (int) $dinilai->min('total_skor');

        $kandidatBersih = $dinilai->where('total_skor', $maxSkor)->values();

        // --- Aturan batas bawah: hanya kamar di bawah 70% yang boleh disebut TERKOTOR ---
        $batas = self::batasTerkotor();
        $bawahAmbang = $dinilai->filter(fn ($k) => (float) $k->total_skor < $batas)
                               ->sortBy('total_skor')->values();
        $adaTerkotor = $bawahAmbang->isNotEmpty();
        $kandidatKotor = $adaTerkotor ? $bawahAmbang : collect();

        $terbawah = $dinilai->where('total_skor', $minSkor)->values();

        return view('asrama.penilaian.konfirmasi', compact(
            'penilaian', 'kategori', 'sesi', 'kandidatBersih', 'kandidatKotor', 'maxSkor', 'minSkor',
            'adaTerkotor', 'terbawah', 'batas'
        ));
    }

    // =========================================================
    // 6. FUNGSI FINALISASI (per sesi)
    // =========================================================
    public function finalisasi(Request $request)
    {
        $request->validate([
            'kategori'           => 'required|in:putra,putri',
            'sesi'               => 'required|in:' . implode(',', array_keys(self::SESI)),
            'kamar_terbersih_id' => 'required|exists:asrama_kamars,id',
            'kamar_terkotor_id'  => 'nullable|exists:asrama_kamars,id',
            'catatan_inspektor'  => 'nullable|string|max:1000',
            'foto_terbersih'     => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
            'foto_terkotor'      => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
        ], [
            'kamar_terbersih_id.required' => 'Pilih dulu kamar terbersih sebelum mengunci finalisasi.',
            'foto_terbersih.mimes' => 'Foto kamar terbersih harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto_terkotor.mimes'  => 'Foto kamar terkotor harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto_terbersih.max'   => 'Foto kamar terbersih terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
            'foto_terkotor.max'    => 'Foto kamar terkotor terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
            'catatan_inspektor.max' => 'Catatan inspektor terlalu panjang (maksimal 1000 karakter).',
        ]);

        $sesi = $request->sesi;

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                        ->where('kategori', $request->kategori)
                        ->where('sesi', $sesi)
                        ->where('status', 'draft')
                        ->first();

        if (! $penilaian) {
            return back()->with('error', self::labelSesi($sesi) . ' divisi ' . ucfirst($request->kategori) . ' sudah difinalisasi sebelumnya.');
        }

        // ------------------------------------------------------------------
        // ATURAN BATAS BAWAH: kamar hanya boleh disebut TERKOTOR bila nilainya
        // < 70% skor maksimal. Skor dihitung ULANG di server.
        // ------------------------------------------------------------------
        $skorKamar = AsramaPenilaianKamar::where('penilaian_id', $penilaian->id)->pluck('total_skor', 'kamar_id');

        if ($skorKamar->isEmpty()) {
            return back()->with('error', 'Belum ada kamar yang dinilai pada lembar ini, finalisasi dibatalkan.');
        }

        $batas = self::batasTerkotor();
        $kandidatTerkotor = $skorKamar->filter(fn ($s) => (float) $s < $batas);

        $kamarTerkotorId = $request->filled('kamar_terkotor_id') ? (int) $request->kamar_terkotor_id : null;

        if ($kamarTerkotorId) {
            $skorPilih = $skorKamar[$kamarTerkotorId] ?? null;

            if ($skorPilih === null) {
                return back()->with('error', 'Kamar yang dipilih sebagai terkotor tidak dinilai pada lembar inspeksi ini.');
            }

            if ((float) $skorPilih >= $batas) {
                return back()->with('error', 'Kamar itu tidak bisa ditandai terkotor: nilainya ' . self::persenSkor($skorPilih) . '%, sedangkan batas terkotor adalah di bawah ' . self::BATAS_TERKOTOR_PERSEN . '%.');
            }
        } elseif ($kandidatTerkotor->isNotEmpty()) {
            return back()->with('error', 'Masih ada kamar bernilai di bawah ' . self::BATAS_TERKOTOR_PERSEN . '% — pilih salah satu sebagai kamar terkotor sebelum mengunci.');
        }

        $kamarPerhatianId = $kamarTerkotorId ?: (int) $skorKamar->sort()->keys()->first();

        DB::beginTransaction();
        try {
            $path_terbersih = null;
            if ($request->hasFile('foto_terbersih')) {
                $path_terbersih = $request->file('foto_terbersih')->store('asrama/' . now()->format('Y/m'), 'public');
            }

            $path_terkotor = null;
            if ($request->hasFile('foto_terkotor')) {
                $path_terkotor = $request->file('foto_terkotor')->store('asrama/' . now()->format('Y/m'), 'public');
            }

            $penilaian->update([
                'status' => 'final',
                'sesi' => $sesi,
                'musyrif_id' => Auth::id(),
                'kamar_terbersih_id' => $request->kamar_terbersih_id,
                'kamar_terkotor_id' => $kamarTerkotorId,
                'kamar_perhatian_id' => $kamarPerhatianId,
                'catatan_inspektor' => $request->filled('catatan_inspektor') ? trim($request->catatan_inspektor) : null,
                'foto_terbersih' => $path_terbersih,
                'foto_terkotor' => $path_terkotor,
            ]);

            // =====================================================
            // KRITERIA KHUSUS KEBERSIHAN ASRAMA (poin ke Student Root)
            // =====================================================
            $sesiJudul = self::labelSesi($sesi);

            $injeksiPoin = function ($kamar_id, $poin, $gelar) use ($sesiJudul) {
                $kamar = AsramaKamar::find($kamar_id);
                if (! $kamar) {
                    return;
                }

                $anggotaKamar = AsramaMember::where('kamar_id', $kamar_id)->whereNull('tanggal_keluar')->get();

                $namaKriteria = ($poin > 0) ? 'Kamar Terbersih Asrama' : 'Kamar Terkotor Asrama';
                $kategoriKriteria = ($poin > 0) ? 'positif' : 'negatif';

                $kriteriaAsrama = DB::table('sr_point_criteria')
                    ->where('nama_perilaku', $namaKriteria)
                    ->where('kategori', $kategoriKriteria)
                    ->where('poin', $poin)
                    ->first();

                if (! $kriteriaAsrama) {
                    $kriteriaIdBaru = Str::uuid()->toString();
                    DB::table('sr_point_criteria')->insert([
                        'id'             => $kriteriaIdBaru,
                        'nama_perilaku'  => $namaKriteria,
                        'kategori'       => $kategoriKriteria,
                        'deskripsi'      => 'Poin otomatis hasil inspeksi kebersihan asrama (finalisasi per sesi).',
                        'poin'           => $poin,
                        'tingkat'        => null,
                        'status'         => 'aktif',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    $kriteriaValid = $kriteriaIdBaru;
                } else {
                    $kriteriaValid = $kriteriaAsrama->id;
                }

                foreach ($anggotaKamar as $anggota) {
                    $grupSiswa = DB::table('sr_group_members')->where('student_id', $anggota->student_id)->whereNull('tanggal_keluar')->first();

                    DB::table('sr_point_entries')->insert([
                        'id' => Str::uuid()->toString(),
                        'student_id' => $anggota->student_id,
                        'group_id' => $grupSiswa ? $grupSiswa->group_id : null,
                        'criteria_id' => $kriteriaValid,
                        'input_by' => Auth::id(),
                        'tanggal_kejadian' => now()->toDateString(),
                        'poin' => $poin,
                        // Pakai tanda hubung biasa (DB server latin1) supaya aman di semua kolom.
                        'catatan' => $gelar . ' Asrama (' . $kamar->nama_kamar . ') - ' . $sesiJudul,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            };

            $KategoriJudul = ucfirst($request->kategori);
            $injeksiPoin($request->kamar_terbersih_id, 1, "Kamar Terbersih $KategoriJudul");

            if ($kamarTerkotorId) {
                $injeksiPoin($kamarTerkotorId, -1, "Kamar Terkotor $KategoriJudul");
            }

            DB::commit();

            $namaPerhatian = optional(AsramaKamar::find($kamarPerhatianId))->nama_kamar;

            if ($kamarTerkotorId) {
                $pesan = "✨ $sesiJudul Divisi $KategoriJudul selesai! Poin +1 (kamar terbersih) dan −1 (kamar terkotor) sudah disuntikkan, bukti foto dikirim ke TV Display.";
            } else {
                $pesan = "✅ $sesiJudul Divisi $KategoriJudul selesai — semua kamar bernilai " . self::BATAS_TERKOTOR_PERSEN . "% ke atas, jadi tidak ada kamar terkotor dan tidak ada poin pengurangan. Kamar " . ($namaPerhatian ?: '-') . " masuk daftar perlu diperhatikan.";
            }

            return redirect('/asrama/penilaian')->with('success', $pesan);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi Kesalahan Sistem: ' . $e->getMessage() . ' (pada baris ' . $e->getLine() . ')');
        }
    }

    // =========================================================
    // 7. FUNGSI UPLOAD / GANTI FOTO (DARI HALAMAN HISTORI)
    // =========================================================
    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'jenis' => 'required|in:terbersih,terkotor',
            'foto'  => 'required|file|mimes:jpg,jpeg,png,webp,gif,heic,heif|max:8192',
        ], [
            'foto.required' => 'Pilih dulu file fotonya sebelum menekan Upload.',
            'foto.mimes'    => 'Berkas harus berupa gambar (jpg, png, webp, gif, heic).',
            'foto.max'      => 'Fotonya terlalu besar (maksimal 8 MB). Coba perkecil dulu atau pilih foto lain.',
        ]);

        $penilaian = AsramaPenilaian::findOrFail($id);
        $kolom = $request->jenis === 'terbersih' ? 'foto_terbersih' : 'foto_terkotor';

        DB::beginTransaction();
        try {
            $path = $request->file('foto')->store('asrama/' . now()->format('Y/m'), 'public');

            $lama = $penilaian->{$kolom};
            if ($lama && $lama !== $path && Storage::disk('public')->exists($lama)) {
                Storage::disk('public')->delete($lama);
            }

            $penilaian->update([$kolom => $path]);

            DB::commit();

            return back()->with('success', '📸 Foto ' . $request->jenis . ' berhasil diunggah dan disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mengunggah foto: ' . $e->getMessage());
        }
    }

    // =========================================================
    // 8. FUNGSI RESET PENILAIAN (per sesi, hanya lembar draft hari ini)
    // =========================================================
    public function reset(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:putra,putri',
            'sesi'     => 'required|in:' . implode(',', array_keys(self::SESI)),
        ]);

        $kategori = $request->kategori;
        $sesi = $request->sesi;

        $penilaian = AsramaPenilaian::where('tanggal', now()->toDateString())
                    ->where('kategori', $kategori)
                    ->where('sesi', $sesi)
                    ->where('status', 'draft')
                    ->first();

        if ($penilaian) {
            AsramaPenilaianKamar::where('penilaian_id', $penilaian->id)->delete();

            return back()->with('success', "🔄 Penilaian Divisi " . ucfirst($kategori) . ' (' . self::labelSesi($sesi) . ') berhasil direset. Silakan mulai ulang.');
        }

        return back()->with('error', 'Lembar ' . self::labelSesi($sesi) . ' divisi ' . ucfirst($kategori) . ' sudah difinalisasi atau belum ada data untuk direset.');
    }

    // =========================================================
    // 9. HAPUS LEMBAR DRAFT YANG MENGGANTUNG (bukan hari ini)
    // =========================================================
    public function hapusDraft($id)
    {
        $penilaian = AsramaPenilaian::findOrFail($id);

        if ($penilaian->status !== 'draft') {
            return back()->with('error', 'Hanya lembar berstatus draft yang boleh dihapus.');
        }

        if (Carbon::parse($penilaian->tanggal)->toDateString() >= now()->toDateString()) {
            return back()->with('error', 'Lembar draft hari ini jangan dihapus di sini — pakai tombol Reset pada halaman Inspeksi Hari Ini.');
        }

        $jumlah = $penilaian->rincianKamars()->count();
        $penilaian->delete();

        return back()->with('success', '🗑️ Lembar draft ' . Carbon::parse($penilaian->tanggal)->translatedFormat('d M Y')
            . ' (' . self::labelSesi($penilaian->sesi) . ' · divisi ' . ucfirst($penilaian->kategori) . ', ' . $jumlah . ' kamar belum difinalisasi) dihapus.');
    }
}
