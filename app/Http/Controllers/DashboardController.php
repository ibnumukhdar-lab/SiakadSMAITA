<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArsipSurat;  // Memanggil model e-Arsip
use App\Models\Siswa;       // Memanggil model Siswa
use App\Models\BeeWeek;
use App\Models\SrGroup;
use App\Models\AsramaKamar;
use App\Models\AsramaPenilaian;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard utama — konten dibedakan per peran (Fase 2: 2026-09-08).
     * Urutan prioritas: peran "tertinggi" dicek lebih dulu (Super Admin → ... → Guru).
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('Super Admin'))    return $this->indexSuperAdmin();
        if ($user->hasRole('Kepala Sekolah')) return $this->indexKepalaSekolah();
        if ($user->hasRole('Kepala Diniyah')) return $this->indexKepalaDiniyah();
        if ($user->hasRole('Musyrif'))        return $this->indexMusyrif();
        if ($user->hasRole('Tata Usaha'))     return $this->indexTataUsaha();
        return $this->indexGuru();
    }

    // =====================================================================
    // SUPER ADMIN — "Pusat Kendali" (desain kalibrasi 2026-09, Fase 1)
    // =====================================================================
    protected function indexSuperAdmin()
    {
        $totalAkun = User::count();

        // Jumlah akun per peran (tanpa Super Admin, itu peran pemilik)
        $roleRows = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', '!=', 'Super Admin')
            ->selectRaw('roles.name, COUNT(*) as jumlah')
            ->groupBy('roles.name')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'name')
            ->toArray();

        $peranAktif = count($roleRows);

        // Modul Bee Smart yang sedang tayang
        [$beeAktif, $beeKataAktif] = $this->beeAktif();

        // Inspeksi asrama bulan ini (sudah dikunci/final)
        $inspeksiBulan = $this->inspeksiFinalBulanIni();
        $inspeksiPutra = $inspeksiBulan['putra'];
        $inspeksiPutri = $inspeksiBulan['putri'];

        // Aktivitas poin terbaru (lintas siswa)
        $aktivitas = $this->aktivitasPoin(7);

        // Akun terbaru
        $usersBaru = User::orderByDesc('created_at')->limit(5)->get(['name', 'email', 'created_at']);

        return view('dashboard.super-admin', compact(
            'totalAkun', 'roleRows', 'peranAktif',
            'beeAktif', 'beeKataAktif',
            'inspeksiPutra', 'inspeksiPutri',
            'aktivitas', 'usersBaru'
        ));
    }

    // =====================================================================
    // KEPALA SEKOLAH — "Pantauan Umum" (monitoring lintas modul)
    // =====================================================================
    protected function indexKepalaSekolah()
    {
        [$totalAkun, $roleRows] = $this->statAkun();

        $siswa = $this->statSiswa();
        $arsip = $this->statArsip();

        $aktivitas = $this->aktivitasPoin(6);
        $topGrup   = $this->topGrup(4);

        return view('dashboard.kepala-sekolah', compact(
            'totalAkun', 'roleRows', 'siswa', 'arsip', 'aktivitas', 'topGrup'
        ));
    }

    // =====================================================================
    // KEPALA DINIYAH — kendali asrama & karakter (Student Root + Asrama + BEE)
    // =====================================================================
    protected function indexKepalaDiniyah()
    {
        // Kamar & penghuni aktif per divisi
        $kamar = DB::table('asrama_kamars')
            ->where('status', 'aktif')
            ->selectRaw('kategori, COUNT(*) as jml')
            ->groupBy('kategori')
            ->pluck('jml', 'kategori')
            ->toArray();
        $kamarPutra = $kamar['putra'] ?? 0;
        $kamarPutri = $kamar['putri'] ?? 0;

        $penghuni = $this->penghuniAktif(null);
        $penghuniPutra = $penghuni['putra'] ?? 0;
        $penghuniPutri = $penghuni['putri'] ?? 0;

        // Inspeksi final bulan ini + aktivitas hari ini
        $inspeksiBulan = $this->inspeksiFinalBulanIni();
        $inspeksiHariIni = AsramaPenilaian::whereDate('tanggal', now()->toDateString())->count();

        // Student Root: grup & poin bulan ini
        $grupAktif = SrGroup::where('status', 'aktif')->count();
        $anggotaBinaan = DB::table('sr_group_members as m')
            ->join('sr_groups as g', 'g.id', '=', 'm.group_id')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->where('g.status', 'aktif')
            ->whereNull('m.tanggal_keluar')
            ->where('s.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->count();

        $poinBulan = $this->poinBulanIni();

        [$beeAktif, $beeKataAktif] = $this->beeAktif();

        return view('dashboard.kepala-diniyah', compact(
            'kamarPutra', 'kamarPutri',
            'penghuniPutra', 'penghuniPutri',
            'inspeksiBulan', 'inspeksiHariIni',
            'grupAktif', 'anggotaBinaan', 'poinBulan',
            'beeAktif', 'beeKataAktif'
        ));
    }

    // =====================================================================
    // MUSYRIF — binaan asrama personal (kamar, inspeksi, riwayat)
    // =====================================================================
    protected function indexMusyrif()
    {
        $me = auth()->id();

        $kamarBinaan = AsramaKamar::where('musyrif_id', $me)
            ->where('status', 'aktif')
            ->orderBy('kategori')
            ->orderBy('nama_kamar')
            ->get(['id', 'nama_kamar', 'kategori', 'kapasitas']);

        $kamarIds = $kamarBinaan->pluck('id');

        // Penghuni aktif (masih menempati) di kamar binaan saya
        $penghuni = $kamarIds->isNotEmpty()
            ? $this->penghuniAktif($kamarIds->toArray())
            : [];
        $totalPenghuni = array_sum($penghuni);

        // Inspeksi: final bulan ini (punya saya) & status hari ini
        $finalBulanIni = AsramaPenilaian::where('musyrif_id', $me)
            ->where('status', 'final')
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();

        $finalHariIni = AsramaPenilaian::where('musyrif_id', $me)
            ->where('status', 'final')
            ->whereDate('tanggal', now()->toDateString())
            ->count();
        $draftHariIni = AsramaPenilaian::where('musyrif_id', $me)
            ->where('status', 'draft')
            ->whereDate('tanggal', now()->toDateString())
            ->count();

        // Riwayat inspeksi terakhir saya
        $riwayat = AsramaPenilaian::where('musyrif_id', $me)
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'tanggal', 'kategori', 'status']);

        // Progres Penilaian Adab & Keasramaan (wajib: seluruh santri divisi saya).
        $divisiSaya = AsramaKamar::where('musyrif_id', $me)
            ->where('status', 'aktif')
            ->distinct()
            ->pluck('kategori')
            ->all();
        $divisiSaya = count($divisiSaya) === 1 ? $divisiSaya[0] : null;

        $totalWajib = $divisiSaya
            ? DB::table('asrama_members as m')
                ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
                ->where('k.status', 'aktif')
                ->where('k.kategori', $divisiSaya)
                ->whereNull('m.tanggal_keluar')
                ->count()
            : 0;

        $periodeAktifPenilaian = PenilaianPeriode::aktifSekarang();

        $progresPenilaian = [];
        foreach (PenilaianSesi::JENIS as $jenis => $labelJenis) {
            $sesiSaya = PenilaianSesi::where('jenis', $jenis)
                ->where('penilai_id', $me)
                ->when($periodeAktifPenilaian, fn ($q) => $q->where('periode_id', $periodeAktifPenilaian->id))
                ->first();

            $terisi = $sesiSaya
                ? DB::table('penilaian_jawaban')->where('sesi_id', $sesiSaya->id)->distinct()->count('siswa_id')
                : 0;

            $progresPenilaian[$jenis] = [
                'jenis' => $jenis,
                'label' => $labelJenis,
                'terisi' => $terisi,
                'total' => $totalWajib,
                'kurang' => max(0, $totalWajib - $terisi),
                'persen' => $totalWajib > 0 ? min(100, (int) round($terisi / $totalWajib * 100)) : 0,
                'sesi_id' => $sesiSaya->id ?? null,
                'status' => $sesiSaya->status ?? null,
            ];
        }

        $divisiPenilaian = $divisiSaya;

        return view('dashboard.musyrif', compact(
            'kamarBinaan', 'kamarIds', 'penghuni', 'totalPenghuni',
            'finalBulanIni', 'finalHariIni', 'draftHariIni', 'riwayat',
            'progresPenilaian', 'divisiPenilaian'
        ));
    }

    // =====================================================================
    // TATA USAHA — ikhtisar administrasi (e-Arsip + siswa + aktivitas)
    // =====================================================================
    protected function indexTataUsaha()
    {
        $arsip = $this->statArsip();
        $arsip['bulan_ini'] = ArsipSurat::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $siswa = $this->statSiswa();
        $aktivitas = $this->aktivitasPoin(6);

        return view('dashboard.tata-usaha', compact('arsip', 'siswa', 'aktivitas'));
    }

    // =====================================================================
    // GURU — binaan & karakter (grup binaan saya + BEE aktif)
    // =====================================================================
    protected function indexGuru()
    {
        $me = auth()->id();

        // Anggota per grup (sub-query) — hindari duplikasi SUM saat join member × poin
        $anggotaSub = DB::table('sr_group_members')
            ->select('group_id', DB::raw('COUNT(*) as c'))
            ->whereNull('tanggal_keluar')
            ->groupBy('group_id');
        $poinSub = DB::table('sr_point_entries')
            ->select('group_id', DB::raw('COALESCE(SUM(poin), 0) as p'))
            ->whereNull('deleted_at')
            ->groupBy('group_id');

        $grupBinaan = DB::table('sr_groups as g')
            ->leftJoinSub($anggotaSub, 'gm', 'gm.group_id', '=', 'g.id')
            ->leftJoinSub($poinSub, 'pe', 'pe.group_id', '=', 'g.id')
            ->where('g.mentor_id', $me)
            ->where('g.status', 'aktif')
            ->select(
                'g.id',
                'g.nama_grup',
                'g.warna_grup',
                'g.tahun_ajaran_mulai',
                DB::raw('COALESCE(gm.c, 0) as anggota'),
                DB::raw('COALESCE(pe.p, 0) as poin')
            )
            ->orderBy('g.nama_grup')
            ->get();

        $totalAnggota = (int) $grupBinaan->sum('anggota');
        $totalPoinGrup = (int) $grupBinaan->sum('poin');

        [$beeAktif, $beeKataAktif] = $this->beeAktif();

        return view('dashboard.guru', compact(
            'grupBinaan', 'totalAnggota', 'totalPoinGrup',
            'beeAktif', 'beeKataAktif'
        ));
    }

    // =====================================================================
    // HELPER BERSAMA
    // =====================================================================

    /** Statistik akun: [total akun, [nama peran => jumlah]] */
    private function statAkun(): array
    {
        $totalAkun = User::count();
        $roleRows = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->selectRaw('roles.name, COUNT(*) as jumlah')
            ->groupBy('roles.name')
            ->orderByDesc('jumlah')
            ->get()
            ->pluck('jumlah', 'name')
            ->toArray();

        return [$totalAkun, $roleRows];
    }

    /** Statistik siswa: aktif per tingkat (dinamis dari Kelola Kelas) + alumni + total terdaftar */
    private function statSiswa(): array
    {
        $perTingkat = \App\Models\Kelas::jumlahPerTingkat();

        $alumni = Siswa::where(function ($q) {
            $q->where('status', 'Alumni')->orWhere('kelas', 'Lulus');
        })->count();

        return [
            'aktif'   => Siswa::where('status', 'Aktif')->count(),
            'terdaftar' => Siswa::count(),
            'x'       => $perTingkat['X'] ?? 0,
            'xi'      => $perTingkat['XI'] ?? 0,
            'xii'     => $perTingkat['XII'] ?? 0,
            'alumni'  => $alumni,
        ];
    }

    /** Statistik e-arsip */
    private function statArsip(): array
    {
        return [
            'total'  => ArsipSurat::count(),
            'masuk'  => ArsipSurat::where('jenis_surat', 'Surat Masuk')->count(),
            'keluar' => ArsipSurat::where('jenis_surat', 'Surat Keluar')->count(),
        ];
    }

    /** Modul Bee aktif + total kata yang tayang */
    private function beeAktif(): array
    {
        $beeAktif = BeeWeek::withCount('vocabs')
            ->where('status', 'aktif')
            ->orderBy('updated_at', 'desc')
            ->get();
        $beeKataAktif = DB::table('bee_vocabs')
            ->whereIn('bee_week_id', $beeAktif->pluck('id'))
            ->count();

        return [$beeAktif, $beeKataAktif];
    }

    /** Inspeksi asrama status final bulan ini: [putra, putri, total] */
    private function inspeksiFinalBulanIni(): array
    {
        $raw = DB::table('asrama_penilaians')
            ->where('status', 'final')
            ->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->selectRaw('kategori, COUNT(*) as jumlah')
            ->groupBy('kategori')
            ->pluck('jumlah', 'kategori')
            ->toArray();

        $putra = $raw['putra'] ?? 0;
        $putri = $raw['putri'] ?? 0;

        return ['putra' => $putra, 'putri' => $putri, 'total' => $putra + $putri];
    }

    /**
     * Penghuni asrama aktif (tanggal_keluar null, kamar aktif, siswa Aktif).
     * $kamarIds null = semua kamar; array = filter kamar tertentu.
     */
    private function penghuniAktif(?array $kamarIds): array
    {
        $q = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->whereNull('m.tanggal_keluar')
            ->where('k.status', 'aktif')
            ->where('s.status', 'Aktif')
            ->whereNull('s.deleted_at');

        if ($kamarIds !== null) {
            $q->whereIn('m.kamar_id', $kamarIds);
        }

        return $q->selectRaw('k.kategori, COUNT(*) as jml')
            ->groupBy('k.kategori')
            ->pluck('jml', 'kategori')
            ->toArray();
    }

    /** Poin Student Root bulan ini: [jml entri, net poin] */
    private function poinBulanIni(): array
    {
        $row = DB::table('sr_point_entries')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('COUNT(*) as jml, COALESCE(SUM(poin), 0) as net')
            ->first();

        return ['jml' => (int) $row->jml, 'net' => (int) $row->net];
    }

    /** Aktivitas poin terbaru lintas siswa */
    private function aktivitasPoin(int $limit)
    {
        return DB::table('sr_point_entries as e')
            ->leftJoin('siswas', 'siswas.id', '=', 'e.student_id')
            ->leftJoin('users', 'users.id', '=', 'e.input_by')
            ->whereNull('e.deleted_at')
            ->select('e.poin', 'e.catatan', 'e.created_at', 'e.tanggal_kejadian', 'siswas.nama_lengkap', 'users.name as guru')
            ->orderByDesc('e.created_at')
            ->limit($limit)
            ->get();
    }

    /** Peringkat grup binaan aktif berdasarkan total poin */
    private function topGrup(int $limit)
    {
        $poinSub = DB::table('sr_point_entries')
            ->select('group_id', DB::raw('COALESCE(SUM(poin), 0) as p'))
            ->whereNull('deleted_at')
            ->groupBy('group_id');

        return DB::table('sr_groups as g')
            ->leftJoinSub($poinSub, 'pe', 'pe.group_id', '=', 'g.id')
            ->leftJoin('users', 'users.id', '=', 'g.mentor_id')
            ->where('g.status', 'aktif')
            ->select('g.id', 'g.nama_grup', 'g.warna_grup', 'users.name as mentor', DB::raw('COALESCE(pe.p, 0) as poin'))
            ->orderByDesc('poin')
            ->limit($limit)
            ->get();
    }
}
