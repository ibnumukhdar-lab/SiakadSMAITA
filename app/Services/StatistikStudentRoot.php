<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\SrGroup;
use App\Models\SrPointCriteria;
use Illuminate\Support\Facades\DB;

/**
 * STATISTIK STUDENT ROOT (1 Okt 2026).
 *
 * Satu tempat untuk SEMUA angka statistik poin karakter, supaya halaman layar,
 * hasil cetak, dan ekspor CSV mustahil berbeda.
 *
 * Catatan teknis penting: seluruh agregasi dikerjakan di PHP, BUKAN lewat
 * DATE_FORMAT()/FIELD() milik MySQL — supaya bisa diuji dengan sqlite
 * (`php artisan test` memakai sqlite :memory:). Jumlah baris kecil (ribuan),
 * jadi aman ditarik sekaligus.
 */
class StatistikStudentRoot
{
    /** Batas baris daftar aktivitas/catatan yang dikirim ke halaman. */
    public const BATAS_AKTIVITAS = 10;

    public const BATAS_CATATAN = 12;

    /**
     * Susun seluruh statistik untuk satu rentang data.
     *
     * @param  array{dari?:?string, sampai?:?string, kelas?:?string, grup?:?string, siswa_ids?:?array, label?:?string}  $opsi
     */
    public function susun(array $opsi = []): array
    {
        $dari = $opsi['dari'] ?? null;
        $sampai = $opsi['sampai'] ?? null;
        $kelas = $opsi['kelas'] ?? null;
        $grupId = $opsi['grup'] ?? null;
        $batasSiswa = $opsi['siswa_ids'] ?? null;   // null = semua (pengawas); array = batasan (mentor)

        // ------------------------------------------------------------------
        // 1. Santri yang masuk hitungan (dasar semua angka)
        // ------------------------------------------------------------------
        $querySiswa = Siswa::query()->where('status', 'Aktif');
        if ($kelas) {
            $querySiswa->where('kelas', $kelas);
        }
        $semuaSiswa = $querySiswa->get(['id', 'nama_lengkap', 'nisn', 'kelas']);

        if ($batasSiswa !== null) {
            $semuaSiswa = $semuaSiswa->whereIn('id', array_map('intval', $batasSiswa));
        }

        $petaSiswa = $semuaSiswa->keyBy('id');
        $idsSiswa = $semuaSiswa->pluck('id')->map(fn ($i) => (int) $i)->all();

        if ($grupId) {
            $anggota = $this->anggotaGrup([$grupId]);
            $idsSiswa = array_values(array_intersect($idsSiswa, array_map('intval', $anggota)));
            $petaSiswa = $petaSiswa->only($idsSiswa);
        }

        // ------------------------------------------------------------------
        // 2. Semua entri poin pada rentang itu
        // ------------------------------------------------------------------
        $entri = $this->entri($idsSiswa, $dari, $sampai);

        $ringkasan = $this->ringkasan($entri, $petaSiswa);
        $bulanan = $this->bulanan($entri);

        return [
            'dari' => $dari,
            'sampai' => $sampai,
            'kelas' => $kelas,
            'grup' => $grupId,
            'jumlah_santri' => $semuaSiswa->count(),
            'ringkasan' => $ringkasan,
            'bulanan' => $bulanan,
            'aktivitas' => [
                'positif' => $this->aktivitas($entri, true),
                'negatif' => $this->aktivitas($entri, false),
            ],
            'catatan' => $this->catatanTeratas($entri),
            'per_kelas' => $this->perKelas($entri, $petaSiswa),
            'per_grup' => $this->perGrup($entri),
            'kualitas' => $this->kualitasData($entri, $idsSiswa),
            'siswa' => $this->papanSiswa($entri, $petaSiswa),
        ];
    }

    /** Daftar entri poin mentah (dengan nama kriteria) sesuai batasan. */
    private function entri(array $idsSiswa, ?string $dari, ?string $sampai)
    {
        if ($idsSiswa === []) {
            return collect();
        }

        return DB::table('sr_point_entries as e')
            ->join('sr_point_criteria as c', 'c.id', '=', 'e.criteria_id')
            ->whereNull('e.deleted_at')
            ->whereIn('e.student_id', $idsSiswa)
            ->when($dari, fn ($q) => $q->whereDate('e.tanggal_kejadian', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('e.tanggal_kejadian', '<=', $sampai))
            ->select([
                'e.id', 'e.student_id', 'e.poin', 'e.catatan', 'e.tanggal_kejadian', 'e.bee_week_id',
                'c.id as criteria_id', 'c.nama_perilaku', 'c.kategori', 'c.poin as poin_kriteria',
                'c.status as status_kriteria',
            ])
            ->orderBy('e.tanggal_kejadian')
            ->get();
    }

    private function ringkasan($entri, $petaSiswa): array
    {
        $totalPoin = 0;
        $perSiswa = [];
        foreach ($entri as $e) {
            $totalPoin += (int) $e->poin;
            $perSiswa[$e->student_id] = ($perSiswa[$e->student_id] ?? 0) + (int) $e->poin;
        }

        $positif = count(array_filter($perSiswa, fn ($p) => $p > 0));
        $negatif = count(array_filter($perSiswa, fn ($p) => $p < 0));
        $nol = count($perSiswa) - $positif - $negatif;

        $urut = collect($perSiswa)->sortDesc();
        $teratas = $urut->take(5)->map(fn ($poin, $id) => [
            'nama' => $petaSiswa[$id]->nama_lengkap ?? '—',
            'kelas' => $petaSiswa[$id]->kelas ?? '—',
            'poin' => (int) $poin,
        ])->values()->all();

        $terbawah = $urut->sort()->take(5)->map(fn ($poin, $id) => [
            'nama' => $petaSiswa[$id]->nama_lengkap ?? '—',
            'kelas' => $petaSiswa[$id]->kelas ?? '—',
            'poin' => (int) $poin,
        ])->values()->all();

        $jumlahSantri = $petaSiswa->count();

        return [
            'entri' => $entri->count(),
            'poin' => $totalPoin,
            'santri_terlibat' => count($perSiswa),
            'santri_punya_poin' => $positif + $negatif,
            'santri_positif' => $positif,
            'santri_negatif' => $negatif,
            'santri_nol' => $nol,
            'rata_rata' => $jumlahSantri > 0 ? round($totalPoin / $jumlahSantri, 1) : 0,
            'teratas' => $teratas,
            'terbawah' => $terbawah,
        ];
    }

    /** Perbandingan antar bulan + selisih (naik/turun) terhadap bulan sebelumnya. */
    private function bulanan($entri): array
    {
        $peta = [];
        foreach ($entri as $e) {
            $kunci = substr((string) $e->tanggal_kejadian, 0, 7);   // YYYY-MM
            if ($kunci === '' || strlen($kunci) < 7) {
                continue;
            }
            if (! isset($peta[$kunci])) {
                $peta[$kunci] = [
                    'kunci' => $kunci, 'entri' => 0, 'poin' => 0,
                    'entri_plus' => 0, 'entri_min' => 0,
                    'siswa_plus' => [], 'siswa_min' => [], 'siswa' => [], 'poin_plus' => 0, 'poin_min' => 0,
                ];
            }
            $peta[$kunci]['entri']++;
            $peta[$kunci]['poin'] += (int) $e->poin;
            $peta[$kunci]['siswa'][$e->student_id] = true;
            if ((int) $e->poin > 0) {
                $peta[$kunci]['entri_plus']++;
                $peta[$kunci]['poin_plus'] += (int) $e->poin;
                $peta[$kunci]['siswa_plus'][$e->student_id] = true;
            } elseif ((int) $e->poin < 0) {
                $peta[$kunci]['entri_min']++;
                $peta[$kunci]['poin_min'] += (int) $e->poin;
                $peta[$kunci]['siswa_min'][$e->student_id] = true;
            }
        }

        ksort($peta);

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $hasil = [];
        $sebelumnya = null;
        foreach ($peta as $kunci => $b) {
            [$tahun, $bulan] = explode('-', $kunci);
            $baris = [
                'kunci' => $kunci,
                'label' => ($namaBulan[(int) $bulan] ?? $bulan) . ' ' . $tahun,
                'entri' => $b['entri'],
                'poin' => $b['poin'],
                'poin_plus' => $b['poin_plus'],
                'poin_min' => $b['poin_min'],
                'entri_plus' => $b['entri_plus'],
                'entri_min' => $b['entri_min'],
                'siswa_plus' => count($b['siswa_plus']),
                'siswa_min' => count($b['siswa_min']),
                'siswa_terlibat' => count($b['siswa']),
                'delta_poin' => $sebelumnya === null ? null : $b['poin'] - $sebelumnya['poin'],
                'delta_persen' => ($sebelumnya === null || $sebelumnya['poin'] === 0)
                    ? null
                    : round((($b['poin'] - $sebelumnya['poin']) / abs($sebelumnya['poin'])) * 100, 1),
                'delta_entri' => $sebelumnya === null ? null : $b['entri'] - $sebelumnya['entri'],
            ];
            $hasil[] = $baris;
            $sebelumnya = $baris;
        }

        // Skala grafik (poin positif & negatif terbesar) supaya tinggi batang sebanding.
        $maks = 1;
        foreach ($hasil as $b) {
            $maks = max($maks, $b['poin_plus'], abs($b['poin_min']));
        }
        foreach ($hasil as $i => $b) {
            $hasil[$i]['bar_plus'] = (int) round(($b['poin_plus'] / $maks) * 100);
            $hasil[$i]['bar_min'] = (int) round((abs($b['poin_min']) / $maks) * 100);
        }

        return $hasil;
    }

    /** Aktivitas (kriteria) terbanyak beserta catatan terbanyaknya. */
    private function aktivitas($entri, bool $positif): array
    {
        $kumpul = [];
        foreach ($entri as $e) {
            $tanda = (int) $e->poin > 0 ? 'positif' : ((int) $e->poin < 0 ? 'negatif' : 'nol');
            if (($positif && $tanda !== 'positif') || (! $positif && $tanda !== 'negatif')) {
                continue;
            }

            $k = $e->criteria_id;
            if (! isset($kumpul[$k])) {
                $kumpul[$k] = [
                    'criteria_id' => $k,
                    'nama' => $e->nama_perilaku,
                    'kategori' => $e->kategori,
                    'poin_kriteria' => (int) $e->poin_kriteria,
                    'jumlah' => 0, 'poin' => 0, 'siswa' => [], 'catatan' => [],
                ];
            }

            $kumpul[$k]['jumlah']++;
            $kumpul[$k]['poin'] += (int) $e->poin;
            $kumpul[$k]['siswa'][$e->student_id] = true;

            $catatan = trim((string) $e->catatan);
            if ($catatan !== '') {
                $kunci = mb_strtolower(preg_replace('/\s+/', ' ', $catatan));
                // Bentuk tulisan yang DIPERTAHANKAN = yang pertama muncul (jangan ditimpa
                // oleh variasi huruf besar/kecil berikutnya, mis. "qobliyah subuh").
                $kumpul[$k]['catatan'][$kunci]['tampil'] ??= preg_replace('/\s+/', ' ', $catatan);
                $kumpul[$k]['catatan'][$kunci]['jumlah'] =
                    ($kumpul[$k]['catatan'][$kunci]['jumlah'] ?? 0) + 1;
            }
        }

        $hasil = collect($kumpul)->map(function ($a) {
            $catatan = collect($a['catatan'])->sortByDesc('jumlah')->take(4)->values()
                ->map(fn ($c) => ['teks' => $c['tampil'], 'jumlah' => $c['jumlah']])->all();

            return [
                'nama' => $a['nama'],
                'kategori' => $a['kategori'],
                'poin_kriteria' => $a['poin_kriteria'],
                'jumlah' => $a['jumlah'],
                'poin' => $a['poin'],
                'siswa' => count($a['siswa']),
                'catatan' => $catatan,
            ];
        })->sortByDesc('jumlah')->values()->take(self::BATAS_AKTIVITAS)->all();

        return $hasil;
    }

    /** Catatan yang paling sering diketik (beserta kriteria terbanyak yang memakainya). */
    private function catatanTeratas($entri): array
    {
        $peta = [];
        foreach ($entri as $e) {
            $catatan = trim((string) $e->catatan);
            if ($catatan === '') {
                continue;
            }
            $kunci = mb_strtolower(preg_replace('/\s+/', ' ', $catatan));
            if (! isset($peta[$kunci])) {
                $peta[$kunci] = ['tampil' => preg_replace('/\s+/', ' ', $catatan), 'jumlah' => 0, 'kriteria' => []];
            }
            // tampil = bentuk pertama yang muncul (dibiarkan apa adanya)
            $peta[$kunci]['jumlah']++;
            $peta[$kunci]['kriteria'][$e->nama_perilaku] = ($peta[$kunci]['kriteria'][$e->nama_perilaku] ?? 0) + 1;
        }

        return collect($peta)->sortByDesc('jumlah')->take(self::BATAS_CATATAN)->values()
            ->map(function ($c) {
                arsort($c['kriteria']);

                return [
                    'teks' => $c['tampil'],
                    'jumlah' => $c['jumlah'],
                    'kriteria' => array_key_first($c['kriteria']),
                ];
            })->all();
    }

    private function perKelas($entri, $petaSiswa): array
    {
        $per = [];
        foreach ($petaSiswa as $s) {
            $k = (string) ($s->kelas ?: '—');
            $per[$k] = $per[$k] ?? ['kelas' => $k, 'siswa' => 0, 'entri' => 0, 'poin' => 0];
            $per[$k]['siswa']++;
        }
        foreach ($entri as $e) {
            $k = (string) ($petaSiswa[$e->student_id]->kelas ?? '—');
            if (! isset($per[$k])) {
                $per[$k] = ['kelas' => $k, 'siswa' => 0, 'entri' => 0, 'poin' => 0];
            }
            $per[$k]['entri']++;
            $per[$k]['poin'] += (int) $e->poin;
        }

        $urutan = ['X' => 1, 'XI' => 2, 'XII' => 3, 'Lulus' => 4, '—' => 9];

        return collect($per)->sortBy(fn ($b) => $urutan[$b['kelas']] ?? 5)->values()
            ->map(function ($b) {
                $b['rata'] = $b['siswa'] > 0 ? round($b['poin'] / $b['siswa'], 1) : 0;

                return $b;
            })->all();
    }

    private function perGrup($entri): array
    {
        $grup = SrGroup::query()->where('status', 'aktif')->get(['id', 'nama_grup', 'mentor_id']);
        $anggota = DB::table('sr_group_members')->whereNull('tanggal_keluar')
            ->select('group_id', 'student_id')->get();

        $petaAnggota = [];
        foreach ($anggota as $a) {
            $petaAnggota[$a->group_id][(int) $a->student_id] = true;
        }

        $siswaKeGrup = [];
        foreach ($petaAnggota as $gid => $daftar) {
            foreach (array_keys($daftar) as $sid) {
                $siswaKeGrup[$sid] = $gid;
            }
        }

        $hasil = [];
        foreach ($grup as $g) {
            $hasil[$g->id] = [
                'grup' => $g->nama_grup,
                'anggota' => count($petaAnggota[$g->id] ?? []),
                'entri' => 0,
                'poin' => 0,
            ];
        }

        foreach ($entri as $e) {
            $gid = $siswaKeGrup[(int) $e->student_id] ?? null;
            if ($gid === null || ! isset($hasil[$gid])) {
                continue;
            }
            $hasil[$gid]['entri']++;
            $hasil[$gid]['poin'] += (int) $e->poin;
        }

        return collect($hasil)->sortByDesc('poin')->values()->all();
    }

    /**
     * Pemeriksaan kualitas data — HANYA LAPORAN, tidak ada perbaikan otomatis.
     *
     * Aturan pemilik: perbaikan data pengguna adalah tugas admin/TU lewat UI,
     * jadi halaman ini cuma menandai sambil menunjukkan daftarnya.
     */
    private function kualitasData($entri, array $idsSiswa): array
    {
        $salahKriteria = [];
        $tanpaCatatan = 0;
        foreach ($entri as $e) {
            $catatan = trim((string) $e->catatan);
            if ($catatan === '') {
                $tanpaCatatan++;
            }
            // Entri berpoin POSITIF dengan catatan kegiatan asrama = sisa mekanisme lama.
            if ((int) $e->poin > 0
                && preg_match('/^kamar ter/i', $catatan)
                && ! str_starts_with((string) $e->nama_perilaku, 'Kamar Ter')) {
                $kunci = preg_replace('/\s+/', ' ', $catatan);
                $salahKriteria[$kunci] = ($salahKriteria[$kunci] ?? 0) + 1;
            }
        }

        arsort($salahKriteria);

        // Kriteria yang belum pernah dipakai (dihitung dari SELURUH data, bukan rentang terpilih).
        $terpakai = DB::table('sr_point_entries')->whereNull('deleted_at')
            ->distinct()->pluck('criteria_id')->all();
        $takTerpakai = SrPointCriteria::query()
            ->whereNotIn('id', $terpakai ?: ['-'])
            ->orderBy('kategori')->orderByDesc('poin')
            ->get(['id', 'kategori', 'poin', 'nama_perilaku'])
            ->map(fn ($c) => ['id' => $c->id, 'kategori' => $c->kategori, 'poin' => (int) $c->poin, 'nama' => $c->nama_perilaku])
            ->all();

        return [
            'entri_salah_kriteria' => array_sum($salahKriteria),
            'contoh_salah_kriteria' => array_slice($salahKriteria, 0, 5, true),
            'entri_tanpa_catatan' => $tanpaCatatan,
            'kriteria_tak_terpakai' => $takTerpakai,
        ];
    }

    /** Papan santri: paling banyak poin & paling banyak minus. */
    private function papanSiswa($entri, $petaSiswa): array
    {
        $perSiswa = [];
        foreach ($entri as $e) {
            $sid = (int) $e->student_id;
            $perSiswa[$sid] = $perSiswa[$sid] ?? ['poin' => 0, 'plus' => 0, 'min' => 0];
            $perSiswa[$sid]['poin'] += (int) $e->poin;
            if ((int) $e->poin > 0) {
                $perSiswa[$sid]['plus'] += (int) $e->poin;
            } elseif ((int) $e->poin < 0) {
                $perSiswa[$sid]['min'] += (int) $e->poin;
            }
        }

        $rapikan = function (array $kunci) use ($perSiswa, $petaSiswa) {
            return array_values(array_map(fn ($sid) => [
                'nama' => $petaSiswa[$sid]->nama_lengkap ?? '—',
                'kelas' => $petaSiswa[$sid]->kelas ?? '—',
                'poin' => $perSiswa[$sid]['poin'],
                'plus' => $perSiswa[$sid]['plus'],
                'min' => $perSiswa[$sid]['min'],
            ], $kunci));
        };

        $urut = collect($perSiswa)->sortByDesc('poin');
        $minPalingBanyak = collect($perSiswa)->filter(fn ($r) => $r['min'] < 0)->sortBy('min');

        return [
            'teratas' => $rapikan($urut->take(10)->keys()->all()),
            'terbawah' => $rapikan($urut->reverse()->take(10)->keys()->all()),
            'minus_teratas' => $rapikan($minPalingBanyak->take(10)->keys()->all()),
        ];
    }

    /** Anggota grup (untuk batasan mentor / filter grup). */
    public function anggotaGrup(array $grupIds): array
    {
        return DB::table('sr_group_members')->whereIn('group_id', $grupIds ?: ['-'])
            ->whereNull('tanggal_keluar')->pluck('student_id')->all();
    }
}
