<?php

namespace App\Http\Controllers;

use App\Models\PenilaianPengaturan;
use App\Models\Siswa;
use App\Models\SrGroup;
use App\Models\SrProject;
use App\Models\SrProjectTahap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * RAPOR STUDENT ROOT (cetak per siswa).
 *
 * ATURAN (permintaan Fahri, 17 Sep 2026):
 * 1. Cetak per siswa dilakukan oleh MENTOR STUDENT ROOT masing-masing: mentor hanya bisa
 *    mencetak rapornya santri binaan grupnya sendiri (mentor lain → 403).
 * 2. Super Admin, Kepala Diniyah, Kepala Sekolah, dan Tata Usaha boleh mencetak semua santri
 *    (keperluan pengawasan & arsip).
 * 3. Rapor TIDAK menggabungkan nilai Karakter dan Project — keduanya ditampilkan terpisah.
 * 4. Tanda tangan: Kepala SMA IT Arafah · Orang Tua/Wali · Mentor Student Root (dengan NIPA).
 *
 * Isi nilai: Karakter = akumulasi poin perilaku apa adanya + predikat (ambang bisa diatur);
 * Project = rata-rata nilai akhir project (tahapan berbobot) pada periode yang dipilih.
 */
class SrRaporController extends Controller
{
    /** Periode rapor = PER SEMESTER saja (permintaan Fahri 18 Sep 2026). */
    public const SEMESTER = [
        's1' => 'Semester 1 · Juli–Desember',
        's2' => 'Semester 2 · Januari–Juni',
    ];

    public const AMBANG_BAWAAN = ['a' => 40, 'b' => 20, 'c' => 1];

    // =====================================================================
    //  Halaman pemilih: daftar santri yang boleh dicetak oleh pengguna ini
    // =====================================================================
    public function index(Request $request)
    {
        $this->pastikanBolehMasuk();

        [$dari, $sampai, $preset, $tahunAjaran] = $this->rentangTanggal($request);
        $bolehSemua = $this->bolehSemua();
        $grupSaya = $this->grupSaya();

        $grupIds = $bolehSemua ? SrGroup::where('status', 'aktif')->pluck('id')->all() : $grupSaya->pluck('id')->all();

        $query = Siswa::query()->where('status', 'Aktif');

        if ($grupId = (string) $request->input('grup')) {
            abort_unless(in_array($grupId, $grupIds, true), 403, 'Grup ini bukan binaan Anda.');
            $anggota = $this->anggotaGrup($grupId);
            $query->whereIn('id', $anggota ?: [0]);
        } elseif (! $bolehSemua) {
            $query->whereIn('id', $this->anggotaGrup($grupIds) ?: [0]);
        }

        if ($cari = trim((string) $request->input('q'))) {
            $query->where(function ($w) use ($cari) {
                $w->where('nama_lengkap', 'like', "%{$cari}%")->orWhere('nisn', 'like', "%{$cari}%");
            });
        }

        $siswa = $query->orderBy('nama_lengkap')->get(['id', 'nama_lengkap', 'nisn', 'kelas']);

        $poin = $this->rekapPoin($siswa->pluck('id')->all(), $dari, $sampai);
        $peringkat = $this->peringkat($dari, $sampai);
        $project = $this->rekapProject($siswa->pluck('id')->all(), $dari, $sampai);
        $grupSiswa = $this->petaGrupSiswa($siswa->pluck('id')->all());
        $kamarSiswa = $this->petaKamar($siswa->pluck('id')->all());

        $baris = $siswa->map(function ($s) use ($poin, $project, $grupSiswa, $peringkat, $kamarSiswa) {
            $p = $poin[$s->id] ?? ['total' => 0, 'pos' => 0, 'neg' => 0];
            $pr = $project[$s->id] ?? ['rata' => null, 'jumlah' => 0];

            return [
                'id' => $s->id,
                'nama' => $s->nama_lengkap,
                'nisn' => $s->nisn,
                'kelas' => $s->kelas,
                'grup' => $grupSiswa[$s->id]['grup'] ?? null,
                'mentor' => $grupSiswa[$s->id]['mentor'] ?? null,
                'kamar' => $kamarSiswa[$s->id] ?? null,
                'total' => (int) $p['total'],
                'pos' => (int) $p['pos'],
                'neg' => (int) $p['neg'],
                'predikat' => $this->predikatKarakter((int) $p['total']),
                'peringkat' => $peringkat[$s->id] ?? null,
                'project_rata' => $pr['rata'],
                'project_jumlah' => $pr['jumlah'],
            ];
        });

        return view('student-root.rapot-pilih', [
            'baris' => $baris,
            'preset' => $preset,
            'semesterList' => self::SEMESTER,
            'dari' => $dari,
            'sampai' => $sampai,
            'tahunAjaran' => $tahunAjaran,
            'bolehSemua' => $bolehSemua,
            'grupSaya' => $grupSaya,
            'grupList' => $bolehSemua ? SrGroup::where('status', 'aktif')->orderBy('nama_grup')->get() : $grupSaya,
            'grupTerpilih' => (string) $request->input('grup'),
            'q' => (string) $request->input('q'),
            'ambang' => $this->ambangKarakter(),
            'semuaSantri' => $bolehSemua ? Siswa::where('status', 'Aktif')->count() : $baris->count(),
        ]);
    }

    // =====================================================================
    //  Halaman cetak: satu siswa (atau seluruh anggota grup) → 1 halaman per siswa
    // =====================================================================
    public function cetak(Request $request)
    {
        $this->pastikanBolehMasuk();

        [$dari, $sampai, $preset, $tahunAjaran] = $this->rentangTanggal($request);

        $siswaId = (int) $request->input('siswa', 0);
        $grupId = (string) $request->input('grup');

        if ($siswaId === 0 && $grupId === '') {
            return redirect()->route('sr.rapot')->with('error', 'Pilih dulu santri atau grup yang mau dicetak rapornya.');
        }

        if ($siswaId > 0) {
            $this->pastikanBolehSiswa($siswaId);
            $daftarSiswa = Siswa::where('id', $siswaId)->get();
            $grupCetak = null;
        } else {
            $grupCetak = $this->grupTerpilih($grupId);
            $anggota = $this->anggotaGrup($grupId);
            $daftarSiswa = Siswa::whereIn('id', $anggota ?: [0])->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        }

        $ids = $daftarSiswa->pluck('id')->all();
        $poin = $this->rekapPoin($ids, $dari, $sampai);
        $peringkat = $this->peringkat($dari, $sampai);
        $project = $this->rekapProject($ids, $dari, $sampai);
        $grupSiswa = $this->petaGrupSiswa($ids);
        $kamarSiswa = $this->petaKamar($ids);

        $daftar = $daftarSiswa->map(function ($s) use ($poin, $peringkat, $project, $grupSiswa, $kamarSiswa, $dari, $sampai) {
            $p = $poin[$s->id] ?? ['total' => 0, 'pos' => 0, 'neg' => 0];

            return [
                'siswa' => $s,
                'grup' => $grupSiswa[$s->id]['grup'] ?? null,
                'mentor' => $grupSiswa[$s->id]['mentor'] ?? null,
                'mentor_nipa' => $grupSiswa[$s->id]['mentor_nipa'] ?? null,
                'kamar' => $kamarSiswa[$s->id] ?? null,
                'total' => (int) $p['total'],
                'predikat' => $this->predikatKarakter((float) $p['total']),
                'pos' => (int) $p['pos'],
                'neg' => (int) $p['neg'],
                'peringkat' => $peringkat[$s->id] ?? null,
                'bulanan' => $this->trenBulanan($s->id, $dari, $sampai),
                'positif' => $this->perilaku($s->id, $dari, $sampai, positif: true),
                'negatif' => $this->perilaku($s->id, $dari, $sampai, positif: false),
                'project' => $project[$s->id]['daftar'] ?? [],
                'project_rata' => $project[$s->id]['rata'] ?? null,
                'project_nilai' => $project[$s->id]['jumlah'] ?? 0,
            ];
        });

        $kepala = $this->kepalaSekolah();

        // Catatan mentor per santri untuk semester yang dicetak (18 Sep 2026)
        $catatan = \App\Models\CatatanRaport::untukSr($daftarSiswa->pluck('id')->all(), $tahunAjaran, $preset);

        return view('student-root.rapot', [
            'daftar' => $daftar,
            'catatan' => $catatan,
            'preset' => $preset,
            'semesterList' => self::SEMESTER,
            'dari' => $dari,
            'sampai' => $sampai,
            'tahunAjaran' => $tahunAjaran,
            'grupCetak' => $grupCetak,
            'ambang' => $this->ambangKarakter(),
            'kepala' => $kepala,
            'tahapKolom' => SrProjectTahap::daftarAktif()->map(fn ($t) => ['nama' => $t->nama])->all(),
            'jumlahSantri' => $daftarSiswa->count(),
            'semuaSantri' => Siswa::where('status', 'Aktif')->count(),
            'pengaturan' => \App\Models\Pengaturan::first(),
        ]);
    }

    // =====================================================================
    //  Hak akses
    // =====================================================================
    private function pastikanBolehMasuk(): void
    {
        abort_unless($this->bolehSemua() || $this->grupSaya()->isNotEmpty(), 403, 'Hanya mentor Student Root (atau pengawas) yang bisa membuka rapor ini.');
    }

    private function bolehSemua(): bool
    {
        $u = Auth::user();

        foreach (['Super Admin', 'Kepala Diniyah', 'Kepala Sekolah', 'Tata Usaha'] as $peran) {
            if ($u->hasRole($peran)) {
                return true;
            }
        }

        return false;
    }

    private function grupSaya()
    {
        return SrGroup::where('mentor_id', Auth::id())->where('status', 'aktif')->orderBy('nama_grup')->get();
    }

    private function pastikanBolehSiswa(int $siswaId): void
    {
        if ($this->bolehSemua()) {
            return;
        }

        $grupIds = $this->grupSaya()->pluck('id')->all();
        $anggota = $this->anggotaGrup($grupIds);

        abort_unless(in_array($siswaId, array_map('intval', $anggota), true), 403, 'Santri ini bukan anggota grup binaan Anda.');
    }

    private function grupTerpilih(string $grupId): SrGroup
    {
        $grup = SrGroup::findOrFail($grupId);
        abort_unless($this->bolehSemua() || (int) $grup->mentor_id === (int) Auth::id(), 403, 'Grup ini bukan binaan Anda.');

        return $grup;
    }

    private function anggotaGrup($grupIds): array
    {
        $ids = is_array($grupIds) ? $grupIds : [$grupIds];

        return DB::table('sr_group_members')->whereIn('group_id', $ids ?: ['-'])
            ->whereNull('tanggal_keluar')->pluck('student_id')->all();
    }

    // =====================================================================
    //  Periode / rentang tanggal
    // =====================================================================
    private function rentangTanggal(Request $request): array
    {
        // Tahun ajaran berjalan: Juli–Desember = semester 1, Januari–Juni = semester 2.
        $tahun = (int) now()->format('Y');
        $awalTahunAjaran = (int) now()->format('n') >= 7 ? $tahun : $tahun - 1;
        $semesterAktif = $awalTahunAjaran === $tahun ? 's1' : 's2';

        $preset = (string) $request->input('semester', $semesterAktif);
        if (! array_key_exists($preset, self::SEMESTER)) {
            $preset = $semesterAktif;
        }

        [$dari, $sampai] = $preset === 's1'
            ? ["{$awalTahunAjaran}-07-01", "{$awalTahunAjaran}-12-31"]
            : [($awalTahunAjaran + 1) . '-01-01', ($awalTahunAjaran + 1) . '-06-30'];

        $tahunAjaran = "{$awalTahunAjaran}/" . ($awalTahunAjaran + 1);

        return [$dari, $sampai, $preset, $tahunAjaran];
    }

    // =====================================================================
    //  Rekap data
    // =====================================================================
    private function rekapPoin(array $siswaIds, ?string $dari, ?string $sampai): array
    {
        if ($siswaIds === []) {
            return [];
        }

        $q = DB::table('sr_point_entries')->whereNull('deleted_at')->whereIn('student_id', $siswaIds)
            ->selectRaw('student_id, SUM(poin) as total, SUM(poin > 0) as pos, SUM(poin < 0) as neg')
            ->groupBy('student_id');

        if ($dari) {
            $q->whereDate('tanggal_kejadian', '>=', $dari);
        }
        if ($sampai) {
            $q->whereDate('tanggal_kejadian', '<=', $sampai);
        }

        $hasil = [];
        foreach ($q->get() as $r) {
            $hasil[$r->student_id] = ['total' => (float) $r->total, 'pos' => (int) $r->pos, 'neg' => (int) $r->neg];
        }

        return $hasil;
    }

    /** Peringkat poin di antara SELURUH santri (periode yang sama). */
    private function peringkat(?string $dari, ?string $sampai): array
    {
        $q = DB::table('sr_point_entries')->whereNull('deleted_at')
            ->selectRaw('student_id, SUM(poin) as total')->groupBy('student_id');

        if ($dari) {
            $q->whereDate('tanggal_kejadian', '>=', $dari);
        }
        if ($sampai) {
            $q->whereDate('tanggal_kejadian', '<=', $sampai);
        }

        $urutan = $q->get()->sortByDesc('total')->values();
        $hasil = [];
        foreach ($urutan as $i => $r) {
            $hasil[$r->student_id] = $i + 1;
        }

        return $hasil;
    }

    private function trenBulanan(int $siswaId, ?string $dari, ?string $sampai): array
    {
        $q = DB::table('sr_point_entries')->whereNull('deleted_at')->where('student_id', $siswaId)
            ->selectRaw("DATE_FORMAT(tanggal_kejadian, '%Y-%m') as bulan, SUM(poin > 0) as pos, SUM(poin < 0) as neg, SUM(poin) as total")
            ->groupBy('bulan')->orderBy('bulan');

        if ($dari) {
            $q->whereDate('tanggal_kejadian', '>=', $dari);
        }
        if ($sampai) {
            $q->whereDate('tanggal_kejadian', '<=', $sampai);
        }

        $namaBulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

        return $q->get()->map(function ($r) use ($namaBulan) {
            [$tahun, $bulan] = explode('-', $r->bulan);

            return [
                'label' => ($namaBulan[(int) $bulan] ?? $bulan) . ' ' . $tahun,
                'pos' => (int) $r->pos,
                'neg' => (int) $r->neg,
                'total' => (int) $r->total,
            ];
        })->all();
    }

    /**
     * Perilaku menonjol (positif/negatif) untuk rapor.
     *
     * Maksimal 6 baris (permintaan Fahri 17 Sep 2026): 5 perilaku terbanyak + 1 baris "Lain-lain"
     * yang menggabungkan sisanya, supaya tabel tetap rapi dan muat satu halaman.
     */
    private function perilaku(int $siswaId, ?string $dari, ?string $sampai, bool $positif): array
    {
        $batas = 5;

        $dasar = function () use ($siswaId, $dari, $sampai, $positif) {
            $q = DB::table('sr_point_entries as e')
                ->join('sr_point_criteria as c', 'c.id', '=', 'e.criteria_id')
                ->whereNull('e.deleted_at')->where('e.student_id', $siswaId)
                ->where('e.poin', $positif ? '>' : '<', 0);

            if ($dari) {
                $q->whereDate('e.tanggal_kejadian', '>=', $dari);
            }
            if ($sampai) {
                $q->whereDate('e.tanggal_kejadian', '<=', $sampai);
            }

            return $q;
        };

        $teratas = $dasar()
            ->selectRaw('c.id as criteria_id, c.kategori, c.nama_perilaku, COUNT(*) as jumlah, SUM(e.poin) as poin, MAX(e.catatan) as catatan')
            ->groupBy('c.id', 'c.kategori', 'c.nama_perilaku')
            ->orderBy('poin', $positif ? 'desc' : 'asc')
            ->limit($batas)
            ->get();

        $hasil = $teratas->map(function ($r) use ($positif) {
            // Kalau kategori kriteria tidak sesuai tanda poin (data lama), pakai catatannya.
            $cocok = $positif ? $r->kategori === 'positif' : $r->kategori === 'negatif';

            return [
                'nama' => $cocok ? $r->nama_perilaku : (trim((string) $r->catatan) ?: $r->nama_perilaku),
                'jumlah' => (int) $r->jumlah,
                'poin' => (int) $r->poin,
            ];
        })->all();

        // Sisa di luar 5 teratas -> satu baris "Lain-lain"
        $sisa = $dasar()->whereNotIn('c.id', $teratas->pluck('criteria_id')->all() ?: ['-'])
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(e.poin), 0) as poin')
            ->first();

        if ($sisa && (int) $sisa->jumlah > 0) {
            $hasil[] = [
                'nama' => 'Lain-lain',
                'jumlah' => (int) $sisa->jumlah,
                'poin' => (int) $sisa->poin,
            ];
        }

        return array_slice($hasil, 0, 6);
    }

    /** Rekap project per siswa: daftar project grupnya + nilai akhir (terbobot, tahap terisi). */
    private function rekapProject(array $siswaIds, ?string $dari, ?string $sampai): array
    {
        if ($siswaIds === []) {
            return [];
        }

        $petaGrup = DB::table('sr_group_members')->whereIn('student_id', $siswaIds)->whereNull('tanggal_keluar')
            ->select('student_id', 'group_id')->get()->groupBy('student_id');

        $grupIds = $petaGrup->flatten(1)->pluck('group_id')->unique()->values()->all();

        $project = SrProject::whereIn('grup_id', $grupIds ?: ['-'])->where('aktif', 1)
            ->orderBy('tanggal_mulai')->get()->groupBy('grup_id');

        $tahap = SrProjectTahap::daftarAktif();
        $projectIds = $project->flatten(1)->pluck('id')->all() ?: [0];

        $nilai = DB::table('sr_project_nilai')->whereIn('project_id', $projectIds)->whereIn('siswa_id', $siswaIds)
            ->select('project_id', 'siswa_id', 'tahap_id', 'skor')->get()
            ->groupBy(fn ($r) => $r->siswa_id . '|' . $r->project_id);

        $hasil = [];
        foreach ($siswaIds as $sid) {
            $grupSiswa = $petaGrup[$sid] ?? collect();
            $daftar = [];

            foreach ($grupSiswa as $anggota) {
                foreach ($project[$anggota->group_id] ?? [] as $p) {
                    $skor = [];
                    foreach ($nilai->get($sid . '|' . $p->id, collect()) as $n) {
                        $skor[$n->tahap_id] = (int) $n->skor;
                    }

                    $terisi = [];
                    foreach ($tahap as $t) {
                        if (array_key_exists($t->id, $skor)) {
                            $terisi[$t->id] = ['nama' => $t->nama, 'skor' => $skor[$t->id]];
                        }
                    }

                    $nilaiAkhir = null;
                    if ($terisi !== []) {
                        $bobot = 0;
                        $jumlah = 0;
                        foreach ($terisi as $tid => $info) {
                            $b = (float) ($tahap->firstWhere('id', $tid)->bobot ?? 0);
                            $bobot += $b;
                            $jumlah += $b * $info['skor'];
                        }
                        $nilaiAkhir = $bobot > 0 ? round($jumlah / $bobot, 2) : null;
                    }

                    $daftar[] = [
                        'nama' => $p->nama,
                        'mentor' => optional($p->mentor)->name,
                        'status' => SrProject::STATUS[$p->status] ?? $p->status,
                        'tahap' => collect($tahap)->map(fn ($t) => [
                            'nama' => $t->nama,
                            'skor' => $terisi[$t->id]['skor'] ?? null,
                        ])->all(),
                        'nilai' => $nilaiAkhir,
                        'terisi' => count($terisi),
                        'jumlah_tahap' => $tahap->count(),
                    ];
                }
            }

            $angka = array_values(array_filter(array_column($daftar, 'nilai'), fn ($n) => $n !== null));

            $hasil[$sid] = [
                'daftar' => $daftar,
                'rata' => $angka ? round(array_sum($angka) / count($angka), 2) : null,
                'jumlah' => count($daftar),
            ];
        }

        return $hasil;
    }

    /** siswa_id => ['grup' => nama, 'mentor' => nama, 'mentor_nipa' => ...] */
    private function petaGrupSiswa(array $siswaIds): array
    {
        if ($siswaIds === []) {
            return [];
        }

        $baris = DB::table('sr_group_members as m')
            ->join('sr_groups as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('users as u', 'u.id', '=', 'g.mentor_id')
            ->whereIn('m.student_id', $siswaIds)->whereNull('m.tanggal_keluar')
            ->select('m.student_id', 'g.nama_grup', 'u.name as mentor', 'u.nipa as mentor_nipa')
            ->get();

        $hasil = [];
        foreach ($baris as $b) {
            $hasil[$b->student_id] = [
                'grup' => $b->nama_grup,
                'mentor' => $b->mentor,
                'mentor_nipa' => $b->mentor_nipa,
            ];
        }

        return $hasil;
    }

    /** siswa_id => nama kamar asrama (kalau ada). */
    private function petaKamar(array $siswaIds): array
    {
        if ($siswaIds === []) {
            return [];
        }

        return DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->whereIn('m.student_id', $siswaIds)->whereNull('m.tanggal_keluar')
            ->pluck('k.nama_kamar', 'm.student_id')->toArray();
    }

    private function kepalaSekolah(): ?object
    {
        return DB::table('users as u')
            ->join('model_has_roles as mr', 'mr.model_id', '=', 'u.id')
            ->join('roles as r', function ($j) {
                $j->on('r.id', '=', 'mr.role_id')->where('r.name', '=', 'Kepala Sekolah');
            })
            ->select('u.name as nama', 'u.nipa')
            ->first();
    }

    public function ambangKarakter(): array
    {
        $baris = PenilaianPengaturan::query()->whereIn('kunci', ['sr_karakter_a', 'sr_karakter_b', 'sr_karakter_c'])
            ->pluck('nilai', 'kunci')->toArray();

        $ambil = function (string $kunci) use ($baris) {
            $angka = (float) str_replace(',', '.', (string) ($baris[$kunci] ?? ''));

            return $angka > 0 || $angka === 0.0 && isset($baris[$kunci]) ? $angka : self::AMBANG_BAWAAN[str_replace('sr_karakter_', '', $kunci)];
        };

        return ['a' => $ambil('sr_karakter_a'), 'b' => $ambil('sr_karakter_b'), 'c' => $ambil('sr_karakter_c')];
    }

    public function predikatKarakter(?float $total): ?string
    {
        if ($total === null) {
            return null;
        }

        $a = $this->ambangKarakter();

        if ($total >= $a['a']) {
            return 'A';
        }
        if ($total >= $a['b']) {
            return 'B';
        }
        if ($total >= $a['c']) {
            return 'C';
        }

        return 'D';
    }
}
