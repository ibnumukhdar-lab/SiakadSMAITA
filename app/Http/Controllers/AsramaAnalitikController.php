<?php

namespace App\Http\Controllers;

use App\Models\AsramaAbsensi;
use App\Models\AsramaIzin;
use App\Models\AsramaKamar;
use App\Models\AsramaMember;
use App\Models\AsramaPenilaian;
use App\Models\AsramaPenilaianKamar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ANALITIK KAMAR ASRAMA (Tahap C, 17 Sep 2026)
 * - rata-rata skor tiap kamar (bukan hanya hitungan juara harian)
 * - rincian per kriteria: kamar ini lemahnya di mana
 * - tren beberapa bulan
 * - pemeriksaan data (anomali) sebagai DAFTAR TEMUAN untuk admin — tidak mengubah data
 * - rapor asrama per siswa per kamar (siap cetak)
 */
class AsramaAnalitikController extends Controller
{
    public const KRITERIA = [
        'skor_1' => 'Kerapian tempat tidur',
        'skor_2' => 'Kebersihan lantai',
        'skor_3' => 'Kerapian lemari',
        'skor_4' => 'Barang pribadi & sepatu',
        'skor_5' => 'Sirkulasi udara & aroma',
    ];

    private function bolehSemuaKamar(): bool
    {
        $u = Auth::user();

        return (bool) ($u && $u->can('buka-menu-manajemen-kamar'));
    }

    private function kamarTerlihat()
    {
        $q = AsramaKamar::query();

        if (! $this->bolehSemuaKamar()) {
            $q->where('musyrif_id', Auth::id());
        }

        return $q;
    }

    // =========================================================
    // 1. Analitik
    // =========================================================
    public function index(Request $request)
    {
        $kategori = in_array($request->input('kategori'), ['putra', 'putri'], true) ? $request->input('kategori') : null;

        // ---- inspeksi final 6 bulan terakhir (biaya query kecil: puluhan baris) ----
        $sejak = now()->subMonths(5)->startOfMonth()->toDateString();

        $inspeksi = AsramaPenilaian::where('status', 'final')
            ->where('tanggal', '>=', $sejak)
            ->orderBy('tanggal')
            ->get(['id', 'tanggal', 'kategori']);

        $detail = AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksi->pluck('id')->all() ?: [0])->get();

        // ---- daftar bulan ----
        $daftarBulan = $inspeksi->map(fn ($i) => substr((string) $i->tanggal, 0, 7))->unique()->sortDesc()->values()->all();
        $bulanTerpilih = (string) $request->input('bulan');
        if (! preg_match('/^\d{4}-\d{2}$/', $bulanTerpilih) || ! in_array($bulanTerpilih, $daftarBulan, true)) {
            $bulanTerpilih = in_array(now()->format('Y-m'), $daftarBulan, true)
                ? now()->format('Y-m')
                : ($daftarBulan[0] ?? now()->format('Y-m'));
        }

        $kamar = AsramaKamar::with('musyrif')->withCount(['members as penghuni_count' => fn ($q) => $q->whereNull('tanggal_keluar')])
            ->when($kategori, fn ($q) => $q->where('kategori', $kategori))
            ->orderBy('kategori')->orderBy('nama_kamar')->get();

        $kamarIds = $kamar->pluck('id')->all() ?: [0];

        // ---- detail bulan terpilih dikelompokkan per kamar ----
        $bulanDetail = $detail->filter(function ($d) use ($inspeksi, $bulanTerpilih) {
            $i = $inspeksi->firstWhere('id', $d->penilaian_id);

            return $i && substr((string) $i->tanggal, 0, 7) === $bulanTerpilih;
        });

        $perKamar = [];
        foreach ($kamar as $k) {
            $rows = $bulanDetail->where('kamar_id', $k->id);

            $rataKriteria = [];
            foreach (array_keys(self::KRITERIA) as $kolom) {
                $rataKriteria[$kolom] = $rows->count() ? round((float) $rows->avg($kolom), 2) : null;
            }

            $valid = array_filter($rataKriteria, fn ($v) => $v !== null);
            $terlemah = null;
            if ($valid) {
                $kolomTerlemah = array_search(min($valid), $valid, true);
                $terlemah = self::KRITERIA[$kolomTerlemah] ?? $kolomTerlemah;
            }

            // tren rata-rata skor per bulan (6 bulan)
            $tren = [];
            foreach ($daftarBulan as $b) {
                $idsBulan = $inspeksi->filter(fn ($i) => substr((string) $i->tanggal, 0, 7) === $b)->pluck('id')->all();
                $barisBulan = $detail->whereIn('penilaian_id', $idsBulan)->where('kamar_id', $k->id);
                $tren[$b] = $barisBulan->count() ? round((float) $barisBulan->avg('total_skor'), 2) : null;
            }

            $perKamar[] = [
                'kamar'         => $k,
                'sidak'         => $rows->count(),
                'rata'          => $rows->count() ? round((float) $rows->avg('total_skor'), 2) : null,
                'persen'        => $rows->count() ? AsramaPenilaianController::persenSkor($rows->avg('total_skor')) : null,
                'kriteria'      => $rataKriteria,
                'terlemah'      => $terlemah,
                'terbersih'     => DB::table('asrama_penilaians')->where('status', 'final')
                                    ->where('kamar_terbersih_id', $k->id)
                                    ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanTerpilih])->count(),
                'terkotor'      => DB::table('asrama_penilaians')->where('status', 'final')
                                    ->where('kamar_terkotor_id', $k->id)
                                    ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanTerpilih])->count(),
                'tren'          => $tren,
            ];
        }

        // urutkan: kamar dengan rata-rata terendah lebih dulu (yang perlu dibenahi)
        usort($perKamar, function ($a, $b) {
            if ($a['rata'] === null && $b['rata'] === null) { return 0; }
            if ($a['rata'] === null) { return 1; }
            if ($b['rata'] === null) { return -1; }

            return $a['rata'] <=> $b['rata'];
        });

        // ---- rata-rata kriteria keseluruhan (bulan terpilih) ----
        $rataKriteriaGlobal = [];
        foreach (array_keys(self::KRITERIA) as $kolom) {
            $rataKriteriaGlobal[$kolom] = $bulanDetail->count() ? round((float) $bulanDetail->avg($kolom), 2) : null;
        }

        $ringkasan = [
            'sidak'      => $bulanDetail->count() ? $inspeksi->filter(fn ($i) => substr((string) $i->tanggal, 0, 7) === $bulanTerpilih)->count() : 0,
            'rata'       => $bulanDetail->count() ? round((float) $bulanDetail->avg('total_skor'), 2) : null,
            'kamar'      => $kamar->count(),
            'kamarKosong'=> $kamar->filter(fn ($k) => (int) $k->penghuni_count === 0)->count(),
        ];
        $ringkasan['persen'] = $ringkasan['rata'] !== null ? AsramaPenilaianController::persenSkor($ringkasan['rata']) : null;

        // ---- PEMERIKSAAN DATA (daftar temuan, tidak mengubah apa pun) ----
        $temuan = $this->temuanData($kamarIds);

        return view('asrama.analitik.index', compact(
            'kategori', 'daftarBulan', 'bulanTerpilih', 'perKamar', 'rataKriteriaGlobal', 'ringkasan', 'temuan'
        ));
    }

    /**
     * Daftar anomali data kamar — hanya LAPORAN untuk admin/TU, tidak memperbaiki otomatis.
     */
    private function temuanData(array $kamarIds): array
    {
        $gender = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->whereNull('m.tanggal_keluar')
            ->whereIn('m.kamar_id', $kamarIds)
            ->where(function ($w) {
                $w->where(function ($x) {
                    $x->where('k.kategori', 'putra')->where('s.jk', '!=', 'Laki-laki');
                })->orWhere(function ($x) {
                    $x->where('k.kategori', 'putri')->where('s.jk', '!=', 'Perempuan');
                });
            })
            ->selectRaw('s.id, s.nama_lengkap, s.kelas, s.jk, k.nama_kamar, k.kategori')
            ->get();

        $ganda = DB::table('asrama_members as m')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->whereNull('m.tanggal_keluar')
            ->whereIn('m.kamar_id', $kamarIds)
            ->selectRaw('m.student_id, s.nama_lengkap, s.kelas, COUNT(*) AS jumlah')
            ->groupBy('m.student_id', 's.nama_lengkap', 's.kelas')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $over = AsramaKamar::withCount(['members as penghuni_count' => fn ($q) => $q->whereNull('tanggal_keluar')])
            ->whereIn('id', $kamarIds)
            ->get()
            ->filter(fn ($k) => (int) $k->kapasitas > 0 && (int) $k->penghuni_count > (int) $k->kapasitas)
            ->values();

        $tidakAktif = DB::table('asrama_members as m')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereNull('m.tanggal_keluar')
            ->whereIn('m.kamar_id', $kamarIds)
            ->where(function ($w) {
                $w->where('s.status', '!=', 'Aktif')->orWhereNotNull('s.deleted_at');
            })
            ->selectRaw('s.nama_lengkap, s.kelas, s.status, k.nama_kamar')
            ->get();

        $tanpaMusyrif = AsramaKamar::whereIn('id', $kamarIds)->whereNull('musyrif_id')->pluck('nama_kamar')->all();

        $kosong = AsramaKamar::withCount(['members as penghuni_count' => fn ($q) => $q->whereNull('tanggal_keluar')])
            ->whereIn('id', $kamarIds)
            ->get()
            ->filter(fn ($k) => (int) $k->penghuni_count === 0)
            ->pluck('nama_kamar')
            ->all();

        $tanpaKamar = DB::table('siswas as s')
            ->where('s.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('asrama_members as m')
                    ->whereColumn('m.student_id', 's.id')
                    ->whereNull('m.tanggal_keluar');
            })
            ->count();

        $tanpaGender = DB::table('siswas as s')
            ->where('s.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->where(function ($w) {
                $w->whereNull('s.jk')->orWhereNotIn('s.jk', ['Laki-laki', 'Perempuan']);
            })
            ->count();

        return [
            'gender'      => $gender,
            'ganda'       => $ganda,
            'over'        => $over,
            'tidak_aktif' => $tidakAktif,
            'tanpa_musyrif' => $tanpaMusyrif,
            'kosong'      => $kosong,
            'tanpa_kamar' => $tanpaKamar,
            'tanpa_gender' => $tanpaGender,
        ];
    }

    // =========================================================
    // 2. Rapor asrama per siswa (per kamar, siap cetak)
    // =========================================================
    public function rapor(Request $request)
    {
        $kamarId = (int) $request->input('kamar_id');
        $daftarKamar = $this->kamarTerlihat()->with('musyrif')->orderBy('kategori')->orderBy('nama_kamar')->get();

        $bulan = (string) $request->input('bulan');
        if (! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $bulan = now()->format('Y-m');
        }

        $kamar = $kamarId ? $daftarKamar->firstWhere('id', $kamarId) : null;

        if ($kamarId && ! $kamar) {
            abort(403, 'Kamar itu bukan wilayah akses Anda.');
        }

        $anggota = collect();
        $ringkasAbsensi = collect();
        $poinAsrama = collect();
        $izinBulan = collect();
        $skorKamar = null;
        $terbersih = 0;
        $terkotor = 0;

        if ($kamar) {
            $awal  = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->toDateString();
            $akhir = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->toDateString();

            $anggota = AsramaMember::with('student')
                ->where('kamar_id', $kamar->id)
                ->whereNull('tanggal_keluar')
                ->get()
                ->sortBy(fn ($a) => optional($a->student)->nama_lengkap)
                ->values();

            $anggotaIds = $anggota->pluck('student_id')->all() ?: [0];

            $ringkasAbsensi = DB::table('asrama_absensi')
                ->whereIn('student_id', $anggotaIds)
                ->whereBetween('tanggal', [$awal, $akhir])
                ->selectRaw("student_id,
                    COUNT(*) AS catatan,
                    SUM(status = 'hadir') AS hadir,
                    SUM(status = 'telat') AS telat,
                    SUM(status = 'izin') AS izin,
                    SUM(status = 'sakit') AS sakit,
                    SUM(status = 'pulang') AS pulang,
                    SUM(status = 'alpa') AS alpa")
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');

            // Pakai MODEL (bukan DB::table) supaya helper labelJenis()/labelStatus() tersedia di view.
            $izinBulan = AsramaIzin::whereIn('student_id', $anggotaIds)
                ->where('mulai', '<=', $akhir)
                ->where('sampai', '>=', $awal)
                ->orderBy('mulai')
                ->get()
                ->groupBy('student_id');

            $poinAsrama = DB::table('sr_point_entries as e')
                ->join('sr_point_criteria as c', 'c.id', '=', 'e.criteria_id')
                ->whereIn('e.student_id', $anggotaIds)
                ->whereNull('e.deleted_at')
                ->whereYear('e.tanggal_kejadian', Carbon::createFromFormat('Y-m', $bulan)->year)
                ->whereMonth('e.tanggal_kejadian', Carbon::createFromFormat('Y-m', $bulan)->month)
                ->where(function ($w) {
                    $w->where('c.nama_perilaku', 'like', '%Asrama%')
                      ->orWhere('e.catatan', 'like', '%Asrama%');
                })
                ->selectRaw('e.student_id, SUM(e.poin) AS poin, COUNT(*) AS jml')
                ->groupBy('e.student_id')
                ->get()
                ->keyBy('student_id');

            $idsPenilaian = AsramaPenilaian::where('status', 'final')
                ->whereBetween('tanggal', [$awal, $akhir])
                ->where('kategori', $kamar->kategori)
                ->pluck('id')
                ->all() ?: [0];

            $barisKamar = AsramaPenilaianKamar::whereIn('penilaian_id', $idsPenilaian)->where('kamar_id', $kamar->id);
            $skorKamar = $barisKamar->count() ? round((float) $barisKamar->avg('total_skor'), 2) : null;

            $terbersih = AsramaPenilaian::where('status', 'final')->where('kamar_terbersih_id', $kamar->id)
                ->whereBetween('tanggal', [$awal, $akhir])->count();
            $terkotor = AsramaPenilaian::where('status', 'final')->where('kamar_terkotor_id', $kamar->id)
                ->whereBetween('tanggal', [$awal, $akhir])->count();
        }

        $daftarBulan = AsramaPenilaian::where('status', 'final')
            ->orderByDesc('tanggal')->pluck('tanggal')
            ->map(fn ($t) => substr((string) $t, 0, 7))->unique()->values()->all();

        return view('asrama.analitik.rapor', [
            'daftarKamar'    => $daftarKamar,
            'kamar'          => $kamar,
            'bulan'          => $bulan,
            'daftarBulan'    => $daftarBulan,
            'anggota'        => $anggota,
            'ringkasAbsensi' => $ringkasAbsensi,
            'izinBulan'      => $izinBulan,
            'poinAsrama'     => $poinAsrama,
            'skorKamar'      => $skorKamar,
            'terbersih'      => $terbersih,
            'terkotor'       => $terkotor,
            'pengaturan'     => \App\Models\Pengaturan::first(),
        ]);
    }
}
