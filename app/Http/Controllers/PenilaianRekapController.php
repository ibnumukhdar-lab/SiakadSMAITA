<?php

namespace App\Http\Controllers;

use App\Models\PenilaianJawaban;
use App\Models\PenilaianKriteria;
use App\Models\PenilaianPengaturan;
use App\Models\PenilaianPeriode;
use App\Models\PenilaianSesi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Rekap penilaian karakter: nilai akhir per siswa (Adab dirata-ratakan dari dua penilai,
 * Keasramaan dari penanggungjawab asrama), predikat A-D, ekspor CSV, dan rincian per siswa.
 */
class PenilaianRekapController extends Controller
{
    public function index(Request $request)
    {
        $periodeList = PenilaianPeriode::orderByDesc('aktif')->orderByDesc('nama')->get();
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? $periodeList->first()?->id ?? 0);
        }

        $filter = [
            'q' => trim((string) $request->input('q', '')),
            'kelas' => trim((string) $request->input('kelas', '')),
        ];

        $baris = $this->susunBaris($periodeId, $filter);

        $ringkasan = [
            'siswa' => $baris->count(),
            'adab_terisi' => $baris->filter(fn ($r) => $r['adab']['rata'] !== null)->count(),
            'asrama_terisi' => $baris->filter(fn ($r) => $r['keasramaan']['rata'] !== null)->count(),
            'predikat' => collect(['A', 'B', 'C', 'D'])->mapWithKeys(function ($p) use ($baris) {
                return [$p => $baris->filter(function ($r) use ($p) {
                    return $r['adab']['predikat'] === $p || $r['keasramaan']['predikat'] === $p;
                })->count()];
            })->toArray(),
        ];

        $sesiPeriode = PenilaianSesi::with('penilai')
            ->where('periode_id', $periodeId)
            ->orderBy('jenis')
            ->get()
            ->groupBy('jenis');

        return view('penilaian.rekap', [
            'periodeList' => $periodeList,
            'periodeId' => $periodeId,
            'periode' => $periodeList->firstWhere('id', $periodeId),
            'baris' => $baris,
            'filter' => $filter,
            'daftarKelas' => \App\Models\Kelas::daftarNama(true),
            'ringkasan' => $ringkasan,
            'sesiPeriode' => $sesiPeriode,
            'ambang' => PenilaianPengaturan::ambang(),
        ]);
    }

    public function ekspor(Request $request)
    {
        $periodeId = (int) $request->input('periode', 0);
        if ($periodeId === 0) {
            $periodeId = (int) (PenilaianPeriode::aktifSekarang()?->id ?? 0);
        }

        $filter = [
            'q' => trim((string) $request->input('q', '')),
            'kelas' => trim((string) $request->input('kelas', '')),
        ];

        $periode = PenilaianPeriode::find($periodeId);
        $baris = $this->susunBaris($periodeId, $filter);

        $namaBerkas = 'rekap-penilaian-' . str_replace(['/', ' '], ['-', '_'], (string) ($periode->nama ?? 'periode')) . '.csv';

        return response()->streamDownload(function () use ($baris) {
            $keluaran = fopen('php://output', 'w');
            fwrite($keluaran, "\xEF\xBB\xBF");
            fputcsv($keluaran, [
                'NISN', 'Nama Siswa', 'Kelas', 'Kamar',
                'Adab (%)', 'Predikat Adab', 'Dinilai oleh (Adab)',
                'Keasramaan (%)', 'Predikat Keasramaan', 'Dinilai oleh (Keasramaan)',
            ]);

            foreach ($baris as $r) {
                fputcsv($keluaran, [
                    $r['nisn'],
                    $r['nama'],
                    $r['kelas'],
                    $r['kamar'] ?? '',
                    $r['adab']['rata'] ?? '',
                    $r['adab']['predikat'] ?? '',
                    implode(' | ', $r['adab']['pengisi'] ?? []),
                    $r['keasramaan']['rata'] ?? '',
                    $r['keasramaan']['predikat'] ?? '',
                    implode(' | ', $r['keasramaan']['pengisi'] ?? []),
                ]);
            }

            fclose($keluaran);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function siswa($id)
    {
        $siswa = Siswa::findOrFail($id);

        $sesi = PenilaianSesi::with(['periode', 'penilai'])
            ->whereIn('id', PenilaianJawaban::where('siswa_id', $siswa->id)->pluck('sesi_id')->unique())
            ->orderByDesc('id')
            ->get();

        $jawaban = PenilaianJawaban::with('kriteria')
            ->where('siswa_id', $siswa->id)
            ->get()
            ->groupBy('sesi_id');

        $rincian = $sesi->map(function ($s) use ($jawaban) {
            $jumlahKriteria = max(1, PenilaianKriteria::daftarAktif($s->jenis)->count());

            $jawabanSesi = ($jawaban[$s->id] ?? collect());
            $total = $jawabanSesi->sum('skor');
            $rata = $jawabanSesi->isNotEmpty() ? round($total / ($jawabanSesi->count() * 5) * 100, 2) : null;

            return [
                'sesi' => $s,
                'jawaban' => $jawabanSesi->keyBy('kriteria_id'),
                'persentase' => $rata,
                'predikat' => PenilaianPengaturan::predikat($rata),
                'lengkap' => $jawabanSesi->count() >= $jumlahKriteria,
            ];
        });

        // Nilai akhir per periode (rata-rata seluruh penilai)
        $perPeriode = [];
        foreach ($sesi->groupBy('periode_id') as $periodeId => $kumpulan) {
            foreach (['adab', 'keasramaan'] as $jenis) {
                $milikJenis = $kumpulan->where('jenis', $jenis);
                if ($milikJenis->isEmpty()) {
                    continue;
                }
                $angka = [];
                foreach ($milikJenis as $s) {
                    $j = $jawaban[$s->id] ?? collect();
                    if ($j->isNotEmpty()) {
                        $angka[] = round($j->sum('skor') / ($j->count() * 5) * 100, 2);
                    }
                }
                $rata = $angka ? round(array_sum($angka) / count($angka), 2) : null;
                $perPeriode[$periodeId][$jenis] = [
                    'periode' => $kumpulan->first()->periode->nama ?? '-',
                    'rata' => $rata,
                    'predikat' => PenilaianPengaturan::predikat($rata),
                    'jumlah_penilai' => count($angka),
                ];
            }
        }

        // ---- Nilai Project Student Root (5 tahap) ----
        $grupIds = DB::table('sr_group_members')
            ->where('student_id', $siswa->id)
            ->whereNull('tanggal_keluar')
            ->pluck('group_id')
            ->all();

        $projects = $grupIds
            ? \App\Models\SrProject::aktif()->with('grup')->whereIn('grup_id', $grupIds)->orderBy('id')->get()
            : collect();

        $projectBaris = [];
        $angkaProject = [];
        foreach ($projects as $p) {
            $n = $p->nilaiPerSiswa()[$siswa->id] ?? null;
            $projectBaris[] = ['project' => $p, 'nilai' => $n];
            if ($n && $n['rata'] !== null) {
                $angkaProject[] = $n['rata'];
            }
        }
        $projectRata = $angkaProject ? round(array_sum($angkaProject) / count($angkaProject), 2) : null;

        return view('penilaian.siswa', [
            'siswa' => $siswa,
            'rincian' => $rincian,
            'perPeriode' => $perPeriode,
            'projectBaris' => $projectBaris,
            'projectRata' => $projectRata,
            'projectPredikat' => PenilaianPengaturan::predikat($projectRata),
            'projectTahap' => \App\Models\SrProjectTahap::daftarAktif(),
            'kamar' => DB::table('asrama_members as m')->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
                ->where('m.student_id', $siswa->id)->value('k.nama_kamar'),
            'ambang' => PenilaianPengaturan::ambang(),
        ]);
    }

    /** Baris rekap: satu baris per siswa dengan nilai adab (rata 2 penilai) & keasramaan. */
    private function susunBaris(int $periodeId, array $filter)
    {
        $hasilAdab = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'adab') : [];
        $hasilAsrama = $periodeId ? PenilaianSesi::hasilPeriode($periodeId, 'keasramaan') : [];

        $query = Siswa::query()->where('status', 'Aktif');

        if ($filter['kelas'] !== '') {
            $query->where('kelas', $filter['kelas']);
        }
        if ($filter['q'] !== '') {
            $q = $filter['q'];
            $query->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $siswa = $query->orderByRaw("FIELD(kelas, 'X', 'XI', 'XII', 'Lulus')")->orderBy('nama_lengkap')->get();

        $petaKamar = [];
        if ($siswa->isNotEmpty()) {
            $petaKamar = DB::table('asrama_members as m')
                ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
                ->whereIn('m.student_id', $siswa->pluck('id'))
                ->whereNull('m.tanggal_keluar')
                ->pluck('k.nama_kamar', 'm.student_id')
                ->toArray();
        }

        return $siswa->map(function ($s) use ($hasilAdab, $hasilAsrama, $petaKamar) {
            return [
                'id' => $s->id,
                'nisn' => $s->nisn,
                'nama' => $s->nama_lengkap,
                'kelas' => $s->kelas,
                'kamar' => $petaKamar[$s->id] ?? null,
                'adab' => $this->ringkasJenis($hasilAdab[$s->id] ?? []),
                'keasramaan' => $this->ringkasJenis($hasilAsrama[$s->id] ?? []),
            ];
        });
    }

    /** Ubah daftar hasil sesi (bisa beberapa penilai musyrif) menjadi nilai akhir + predikat. */
    private function ringkasJenis(array $entri): array
    {
        $perPenilai = [];
        $angka = [];
        $pengisi = [];
        $adaDraft = false;

        foreach ($entri as $e) {
            $peran = $e['penilai_peran'] ?: 'lainnya';
            $perPenilai[$peran] = $e;
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
            'per_penilai' => $perPenilai,
            'rata' => $rata,
            'predikat' => PenilaianPengaturan::predikat($rata),
            'jumlah_penilai' => count($angka),
            'pengisi' => array_values(array_unique($pengisi)),
            'ada_draft' => $adaDraft,
        ];
    }
}
