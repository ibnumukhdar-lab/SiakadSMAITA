<?php

namespace App\Http\Controllers;

use App\Models\AsramaKamar;
use App\Models\Kelas;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPengaturan;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * RAPOT CETAK Penilaian Adab & Keasramaan (17 Sep 2026).
 *
 * Satu halaman A4 per siswa: kop sekolah, identitas santri, nilai akhir + predikat
 * untuk Adab dan Keasramaan, rincian per aspek (skor 1–5 = rata-rata antar penilai),
 * catatan, dan kolom tanda tangan.
 *
 * Sumber angka sama dengan halaman Rekap (nilai akhir = rata-rata persentase antar
 * penilai pada periode terpilih) supaya tidak ada selisih antara layar dan cetakan.
 */
class PenilaianRaporController extends Controller
{
    /** Label skala 1–5 untuk keterangan per aspek. */
    public const SKALA = [
        1 => 'Sangat kurang',
        2 => 'Kurang',
        3 => 'Cukup',
        4 => 'Baik',
        5 => 'Sangat baik',
    ];

    public static function labelSkala($skor): string
    {
        $bulat = (int) round((float) $skor);

        return self::SKALA[$bulat] ?? '-';
    }

    /** Halaman pemilih: pilih periode + kamar/kelas, lalu cetak per anak atau per kamar. */
    public function index(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? $periodeList->first()?->id ?? 0);
        }

        $kamarId = (int) $request->input('kamar', 0);
        $q = trim((string) $request->input('q', ''));

        $kamarList = AsramaKamar::with('musyrif')->where('status', 'aktif')->orderBy('kategori')->orderBy('nama_kamar')->get();

        $hasilAdab = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'adab')) : [];
        $hasilAsrama = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'keasramaan')) : [];
        $musyrifDivisi = $this->musyrifPerDivisi();

        $query = Siswa::query()->where('status', 'Aktif');

        if ($kamarId > 0) {
            $anggota = DB::table('asrama_members')->where('kamar_id', $kamarId)->whereNull('tanggal_keluar')->pluck('student_id')->all();
            $query->whereIn('id', $anggota ?: [0]);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $siswa = $query->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")->orderBy('nama_lengkap')->get();

        $petaKamar = $siswa->isEmpty() ? [] : DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereIn('m.student_id', $siswa->pluck('id'))
            ->whereNull('m.tanggal_keluar')
            ->pluck('k.nama_kamar', 'm.student_id')
            ->toArray();

        $kategoriSiswa = $siswa->isEmpty() ? [] : DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereIn('m.student_id', $siswa->pluck('id'))
            ->whereNull('m.tanggal_keluar')
            ->pluck('k.kategori', 'm.student_id')
            ->toArray();

        $baris = $siswa->map(function ($s) use ($hasilAdab, $hasilAsrama, $petaKamar, $musyrifDivisi, $kategoriSiswa) {
            return [
                'id' => $s->id,
                'nama' => $s->nama_lengkap,
                'nisn' => $s->nisn,
                'kelas' => $s->kelas,
                'kamar' => $petaKamar[$s->id] ?? null,
                'kategori' => $kategoriSiswa[$s->id] ?? null,
                'wajib' => $musyrifDivisi[$kategoriSiswa[$s->id] ?? ''] ?? 0,
                'adab' => $this->ringkas($hasilAdab[$s->id] ?? []),
                'keasramaan' => $this->ringkas($hasilAsrama[$s->id] ?? []),
            ];
        });

        // Catatan musyrif per santri (isian langsung di daftar) — 18 Sep 2026
        $catatan = \App\Models\CatatanRaport::untukAdab($siswa->pluck('id')->all(), $periodeId);

        return view('penilaian.rapot-pilih', [
            'catatan' => $catatan,
            'divisiSaya' => \App\Models\CatatanRaport::divisiMusyrif((int) \Illuminate\Support\Facades\Auth::id()),
            'bolehCatatanSemua' => \Illuminate\Support\Facades\Auth::user()->hasRole('Super Admin')
                || \Illuminate\Support\Facades\Auth::user()->hasRole('Kepala Diniyah'),
            'periodeList' => $periodeList,
            'periodeId' => $periodeId,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'kamarId' => $kamarId,
            'kamarList' => $kamarList,
            'q' => $q,
            'baris' => $baris,
        ]);
    }

    /**
     * Halaman "Cetak Rapor" (19 Sep 2026) — untuk musyrif/musyrifah: langsung kamar
     * binaannya sendiri; untuk pengelola (TU/Kepsek/Kepala Diniyah/Super Admin): semua kamar.
     * Tombolnya mengarah ke halaman cetak yang sudah ada (satu santri = satu halaman A4).
     */
    public function cetakPilih(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('id')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) ($periodeList->firstWhere('aktif', true)?->id ?? $periodeList->first()?->id ?? 0);
        }

        $semua = $this->bolehCetakSemua();

        $kamarList = AsramaKamar::with('musyrif')
            ->where('status', 'aktif')
            ->when(! $semua, fn ($q) => $q->where('musyrif_id', Auth::id()))
            ->orderBy('kategori')
            ->orderBy('nama_kamar')
            ->get();

        $hasilAdab = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'adab')) : [];
        $hasilAsrama = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'keasramaan')) : [];

        $anggota = DB::table('asrama_members as m')
            ->join('siswas as s', 's.id', '=', 'm.student_id')
            ->whereIn('m.kamar_id', $kamarList->pluck('id')->all() ?: [0])
            ->whereNull('m.tanggal_keluar')
            ->where('s.status', 'Aktif')
            ->whereNull('s.deleted_at')
            ->orderBy('s.nama_lengkap')
            ->select('m.kamar_id', 's.id', 's.nama_lengkap', 's.kelas', 's.nisn')
            ->get();

        $siswaIds = $anggota->pluck('id')->all();
        $catatan = \App\Models\CatatanRaport::untukAdab($siswaIds, $periodeId);

        $perKamar = $kamarList->map(function ($k) use ($anggota, $hasilAdab, $hasilAsrama, $catatan) {
            $penghuni = $anggota->where('kamar_id', $k->id)->map(function ($s) use ($hasilAdab, $hasilAsrama, $catatan) {
                $adab = $this->ringkas($hasilAdab[$s->id] ?? []);
                $asrama = $this->ringkas($hasilAsrama[$s->id] ?? []);

                return (object) [
                    'id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'kelas' => $s->kelas,
                    'nisn' => $s->nisn,
                    'adab' => $adab['rata'],
                    'adab_predikat' => $adab['predikat'],
                    'asrama' => $asrama['rata'],
                    'asrama_predikat' => $asrama['predikat'],
                    'dinilai' => $adab['rata'] !== null && $asrama['rata'] !== null,
                    'ada_catatan' => isset($catatan[$s->id]),
                ];
            })->values();

            return (object) [
                'id' => $k->id,
                'nama' => $k->nama_kamar,
                'kategori' => $k->kategori,
                'musyrif' => optional($k->musyrif)->name,
                'penghuni' => $penghuni,
                'total' => $penghuni->count(),
                'dinilai' => $penghuni->where('dinilai', true)->count(),
            ];
        });

        return view('penilaian.cetak-pilih', [
            'periodeList' => $periodeList,
            'periodeId' => $periodeId,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'semuaKamar' => $semua,
            'perKamar' => $perKamar,
            'totalSantri' => $perKamar->sum('total'),
        ]);
    }

    /** Halaman cetak: satu siswa, atau seluruh penghuni satu kamar (satu halaman per anak). */
    public function cetak(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? $periodeList->first()?->id ?? 0);
        }

        $siswaId = (int) $request->input('siswa', 0);
        $kamarId = (int) $request->input('kamar', 0);

        if ($siswaId === 0 && $kamarId === 0) {
            return redirect()->route('penilaian.rapot', ['periode' => $periodeId])
                ->with('error', 'Pilih dulu kamar atau siswa yang mau dicetak rapotnya.');
        }

        $kamar = $kamarId > 0 ? AsramaKamar::with('musyrif')->find($kamarId) : null;

        if ($siswaId > 0) {
            $daftarSiswa = Siswa::where('id', $siswaId)->get();
        } else {
            $anggota = DB::table('asrama_members')->where('kamar_id', $kamarId)->whereNull('tanggal_keluar')->pluck('student_id')->all();
            $daftarSiswa = Siswa::whereIn('id', $anggota ?: [0])->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        }

        $this->pastikanBolehCetak($kamarId, $daftarSiswa);

        $hasilAdab = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'adab')) : [];
        $hasilAsrama = $periodeId ? $this->tanpaAdmin(PenilaianSesi::hasilPeriode($periodeId, 'keasramaan')) : [];
        $musyrifDivisi = $this->musyrifPerDivisi();

        $petaKamar = $daftarSiswa->isEmpty() ? [] : DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->leftJoin('users as u', 'u.id', '=', 'k.musyrif_id')
            ->whereIn('m.student_id', $daftarSiswa->pluck('id'))
            ->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'k.id as kamar_id', 'k.nama_kamar', 'k.kategori', 'u.name as musyrif', 'u.nipa as musyrif_nipa')
            ->get()
            ->keyBy('student_id');

        $daftar = $daftarSiswa->map(function ($s) use ($hasilAdab, $hasilAsrama, $petaKamar, $periodeId, $musyrifDivisi) {
            $info = $petaKamar[$s->id] ?? null;

            return [
                'siswa' => $s,
                'kamar' => $info->nama_kamar ?? null,
                'kategori' => $info->kategori ?? null,
                'musyrif' => $info->musyrif ?? null,
                'musyrif_nipa' => $info->musyrif_nipa ?? null,
                'wajib' => $musyrifDivisi[$info->kategori ?? ''] ?? 0,
                'adab' => $this->rincian($s->id, $periodeId, 'adab', $hasilAdab[$s->id] ?? []),
                'keasramaan' => $this->rincian($s->id, $periodeId, 'keasramaan', $hasilAsrama[$s->id] ?? []),
            ];
        });

        // Catatan musyrif per santri untuk periode yang dicetak (18 Sep 2026)
        $catatan = \App\Models\CatatanRaport::untukAdab($daftarSiswa->pluck('id')->all(), $periodeId);

        return view('penilaian.rapot', [
            'daftar' => $daftar,
            'catatan' => $catatan,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'periodeId' => $periodeId,
            'periodeList' => $periodeList,
            'kamar' => $kamar,
            'pengaturan' => \App\Models\Pengaturan::first(),
            'ambang' => PenilaianPengaturan::ambang(),
            'kepalaDiniyah' => $this->namaKepalaDiniyah(),
            'kepalaKulliyyah' => $this->kepalaKulliyyah(),
        ]);
    }

    /**
     * Buang lembar milik Super Admin dari hitungan rapor.
     * Rapor = rata-rata musyrif/musyrifah pembina (Super Admin hanya jalan perbaikan data).
     */
    private function tanpaAdmin(array $hasil): array
    {
        $adminIds = $this->adminIds();

        foreach ($hasil as $siswaId => $entri) {
            $siswa = array_values(array_filter($entri, fn ($e) => ! in_array((int) ($e['penilai_id'] ?? 0), $adminIds, true)));

            if ($siswa === []) {
                unset($hasil[$siswaId]);
            } else {
                $hasil[$siswaId] = $siswa;
            }
        }

        return $hasil;
    }

    private function adminIds(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = \App\Models\User::role('Super Admin')->pluck('id')->map(fn ($v) => (int) $v)->all();
        }

        return $ids;
    }

    /** Jumlah penilai WAJIB per divisi = musyrif/musyrifah pemilik kamar aktif divisi itu. */
    private function musyrifPerDivisi(): array
    {
        $baris = DB::table('asrama_kamars')
            ->where('status', 'aktif')
            ->whereNotNull('musyrif_id')
            ->select('kategori', DB::raw('COUNT(DISTINCT musyrif_id) as jml'))
            ->groupBy('kategori')
            ->pluck('jml', 'kategori');

        return [
            'putra' => (int) ($baris['putra'] ?? 0),
            'putri' => (int) ($baris['putri'] ?? 0),
        ];
    }

    /** Nilai akhir satu jenis dari daftar sesi (rata-rata persentase antar penilai). */
    private function ringkas(array $entri): array
    {
        $angka = [];
        $pengisi = [];
        $adaDraft = false;

        foreach ($entri as $e) {
            if ($e['persentase'] !== null) {
                $angka[] = $e['persentase'];
            }
            if (! empty($e['penilai_nama'])) {
                $pengisi[] = $e['penilai_nama'];
            }
            if (($e['status'] ?? 'draft') === 'draft') {
                $adaDraft = true;
            }
        }

        $rata = $angka ? round(array_sum($angka) / count($angka), 2) : null;

        return [
            'rata' => $rata,
            'predikat' => PenilaianPengaturan::predikat($rata),
            'penilai' => array_values(array_unique($pengisi)),
            'ada_draft' => $adaDraft,
        ];
    }

    /** Ringkasan + rincian per aspek satu siswa untuk satu jenis. */
    private function rincian(int $siswaId, int $periodeId, string $jenis, array $entri): array
    {
        $kriteria = PenilaianKriteria::daftarAktif($jenis);

        // Skor rata-rata per aspek dari SEMUA penilai pada periode ini.
        $rataAspek = [];
        if ($kriteria->isNotEmpty()) {
            $baris = DB::table('penilaian_jawaban as j')
                ->join('penilaian_sesi as s', 's.id', '=', 'j.sesi_id')
                ->where('s.periode_id', $periodeId)
                ->where('s.jenis', $jenis)
                ->where('j.siswa_id', $siswaId)
                ->whereIn('j.kriteria_id', $kriteria->pluck('id'))
                ->select('j.kriteria_id', DB::raw('AVG(j.skor) as rata'), DB::raw('COUNT(*) as jumlah'))
                ->groupBy('j.kriteria_id')
                ->get();

            foreach ($baris as $b) {
                $rataAspek[$b->kriteria_id] = ['rata' => (float) $b->rata, 'jumlah' => (int) $b->jumlah];
            }
        }

        // Rapor TIDAK menampilkan skor 1-5: tiap aspek langsung dikonversi ke NILAI 0-100.
        $aspek = $kriteria->map(function ($k) use ($rataAspek) {
            $data = $rataAspek[$k->id] ?? null;
            $rataSkor = $data ? round($data['rata'], 2) : null;
            $nilai = $rataSkor !== null ? round($rataSkor / 5 * 100, 2) : null;

            return [
                'pertanyaan' => $k->pertanyaan,
                'rata' => $rataSkor,
                'nilai' => $nilai,
                'predikat' => PenilaianPengaturan::predikat($nilai),
                'label' => $data ? self::labelSkala($data['rata']) : null,
                'jumlah_penilai' => $data['jumlah'] ?? 0,
            ];
        });

        $terisi = $aspek->filter(fn ($a) => $a['nilai'] !== null)->count();
        $nilaiAspekSemua = $terisi > 0
            ? round($aspek->sum(fn ($a) => $a['nilai'] ?? 0) / $terisi, 2)
            : null;

        return array_merge($this->ringkas($entri), [
            'jenis' => $jenis,
            'label_jenis' => PenilaianSesi::JENIS[$jenis] ?? ucfirst($jenis),
            'aspek' => $aspek,
            'jumlah_aspek' => $kriteria->count(),
            'aspek_terisi' => $terisi,
            'nilai_aspek' => $nilaiAspekSemua,
            'predikat_aspek' => PenilaianPengaturan::predikat($nilaiAspekSemua),
            'rata_aspek' => $nilaiAspekSemua === null ? null : round($nilaiAspekSemua / 100 * 5, 2),
        ]);
    }

    /**
     * Siapa yang boleh mencetak rapor SEMUA kamar: Super Admin, Tata Usaha, Kepala Sekolah,
     * Kepala Diniyah, dan pemegang izin `kelola-sesi-rapor`/`buka-menu-manajemen-kamar`.
     */
    private function bolehCetakSemua(): bool
    {
        $user = Auth::user();

        if ($user->hasRole('Super Admin') || $user->hasRole('Tata Usaha')
            || $user->hasRole('Kepala Sekolah') || $user->hasRole('Kepala Diniyah')) {
            return true;
        }

        return $user->can('kelola-sesi-rapor') || $user->can('buka-menu-manajemen-kamar');
    }

    /**
     * Penjagaan cetak (19 Sep 2026): musyrif/musyrifah HANYA boleh mencetak rapor santri
     * pada kamar binaannya (kamar dengan musyrif_id = dia). Pengelola bebas.
     */
    private function pastikanBolehCetak(int $kamarId, $daftarSiswa): void
    {
        if ($this->bolehCetakSemua()) {
            return;
        }

        $user = Auth::user();

        abort_unless(
            $user->can('nilai-adab') || $user->can('nilai-keasramaan'),
            403,
            'Anda tidak berhak mencetak rapor ini.'
        );

        $kamarBinaan = AsramaKamar::where('musyrif_id', $user->id)->where('status', 'aktif')->pluck('id')->all();

        if ($kamarId > 0) {
            abort_unless(in_array($kamarId, $kamarBinaan, true), 403, 'Kamar ini bukan kamar binaan Anda. Minta TU/Kepala Diniyah mencetaknya.');
        }

        if ($kamarId === 0 && $daftarSiswa->isNotEmpty()) {
            $diLuar = DB::table('asrama_members')
                ->whereIn('student_id', $daftarSiswa->pluck('id')->all())
                ->whereNull('tanggal_keluar')
                ->whereNotIn('kamar_id', $kamarBinaan ?: [0])
                ->distinct()
                ->pluck('student_id')
                ->all();

            abort_if($diLuar !== [], 403, 'Santri ini tidak berada di kamar binaan Anda. Minta TU/Kepala Diniyah mencetak rapornya.');
        }
    }

    private function namaKepalaDiniyah(): ?string
    {
        return optional($this->kepalaKulliyyah())->nama;
    }

    /** Nama + NIPA Kepala Kulliyyat Diiniyyah Al-Arafah (role 'Kepala Diniyah' di database). */
    private function kepalaKulliyyah(): ?object
    {
        $baris = DB::table('users as u')
            ->join('model_has_roles as mr', 'mr.model_id', '=', 'u.id')
            ->join('roles as r', function ($j) {
                $j->on('r.id', '=', 'mr.role_id')->where('r.name', '=', 'Kepala Diniyah');
            })
            ->select('u.name as nama', 'u.nipa')
            ->first();

        return $baris ?: null;
    }
}
