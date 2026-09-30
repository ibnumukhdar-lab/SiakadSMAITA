<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * REKAM JEJEK SISWA (mulai 30 Sep 2026)
 * =====================================================================
 * Mengumpulkan satu siswa dari SEMUA program yang sudah ada di SIAKAD:
 *   1. identitas + kamar asrama + grup & mentor Student Root
 *   2. poin karakter Student Root (total, per kategori, perilaku terbanyak, terbaru)
 *   3. project Student Root (sedang berjalan & selesai, nilai per tahap, portofolio)
 *   4. asrama: inspeksi kebersihan kamarnya, absensi jam tidur, izin
 *   5. penilaian Adab & Keasramaan per periode (bisa kosong bila belum diisi)
 *   6. catatan rapot (adab & student root)
 *
 * Dipakai DUA halaman agar angkanya mustahil berbeda:
 *   - halaman guru/TU: Siswa > Rekam Jejak
 *   - portal orang tua: /ortu
 *
 * Semua nama kolom mengikuti basis data produksi apa adanya (banyak yang
 * berbeda dari dugaan: sr_point_entries.student_id, sr_groups.nama_grup,
 * asrama_kamars.nama_kamar, sr_project_nilai.siswa_id). JANGAN "perbaiki"
 * jadi nama yang lebih rapi tanpa mengubah DB — sudah pernah bikin error 1054.
 */
class RekamJejak
{
    /** Ambang kata "berulang" tidak dipakai di sini; ini murni modul SIAKAD. */
    public function susun(int $siswaId): ?array
    {
        $siswa = DB::table('siswas')->where('id', $siswaId)->whereNull('deleted_at')->first();

        if (! $siswa) {
            return null;
        }

        $kamar = DB::table('asrama_members as m')
            ->join('asrama_kamars as k', 'k.id', '=', 'm.kamar_id')
            ->leftJoin('users as u', 'u.id', '=', 'k.musyrif_id')
            ->where('m.student_id', $siswaId)
            ->whereNull('m.tanggal_keluar')
            ->select('k.id', 'k.nama_kamar', 'k.kategori', 'k.kapasitas', 'u.name as musyrif')
            ->first();

        $grup = DB::table('sr_group_members as gm')
            ->join('sr_groups as g', 'g.id', '=', 'gm.group_id')
            ->leftJoin('users as u', 'u.id', '=', 'g.mentor_id')
            ->where('gm.student_id', $siswaId)
            ->whereNull('gm.tanggal_keluar')
            ->select('g.id', 'g.nama_grup', 'g.warna_grup', 'u.name as mentor')
            ->first();

        return [
            'siswa'    => $siswa,
            'kamar'    => $kamar,
            'grup'     => $grup,
            'poin'     => $this->poin($siswaId),
            'project'  => $this->project($siswaId, $grup->id ?? null),
            'asrama'   => $this->asrama($siswaId, $kamar->id ?? null),
            'adab'     => $this->adab($siswaId),
            'catatan'  => $this->catatan($siswaId),
        ];
    }

    /** 2. Poin karakter Student Root. */
    private function poin(int $siswaId): array
    {
        $dasar = DB::table('sr_point_entries')
            ->where('student_id', $siswaId)
            ->whereNull('deleted_at');

        $total = (clone $dasar)->selectRaw('COUNT(*) as entri, COALESCE(SUM(poin),0) as total, SUM(poin > 0) as positif, SUM(poin < 0) as negatif')->first();

        $terbanyak = DB::table('sr_point_entries as e')
            ->join('sr_point_criteria as c', 'c.id', '=', 'e.criteria_id')
            ->where('e.student_id', $siswaId)
            ->whereNull('e.deleted_at')
            ->groupBy('c.id', 'c.nama_perilaku', 'c.poin', 'c.kategori')
            ->selectRaw('c.nama_perilaku, c.poin, c.kategori, COUNT(*) as jumlah, SUM(e.poin) as sumbangan')
            ->orderByDesc('jumlah')
            ->orderByDesc('sumbangan')
            ->limit(6)
            ->get();

        $terbaru = DB::table('sr_point_entries as e')
            ->join('sr_point_criteria as c', 'c.id', '=', 'e.criteria_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.input_by')
            ->where('e.student_id', $siswaId)
            ->whereNull('e.deleted_at')
            ->orderByDesc('e.tanggal_kejadian')
            ->orderByDesc('e.id')
            ->limit(10)
            ->select('c.nama_perilaku', 'c.kategori', 'e.poin', 'e.catatan', 'e.tanggal_kejadian', 'u.name as pencatat')
            ->get();

        return [
            'entri'     => (int) ($total->entri ?? 0),
            'total'     => (int) ($total->total ?? 0),
            'positif'   => (int) ($total->positif ?? 0),
            'negatif'   => (int) ($total->negatif ?? 0),
            'terbanyak' => $terbanyak,
            'terbaru'   => $terbaru,
        ];
    }

    /** 3. Project Student Root (milik grup siswa) + nilai pribadi per tahap. */
    private function project(int $siswaId, ?string $grupId): array
    {
        $tahap = DB::table('sr_project_tahap')->where('aktif', 1)->orderBy('urutan')->get();

        $project = DB::table('sr_projects')
            ->when($grupId, fn ($q) => $q->where('grup_id', $grupId), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderByDesc('aktif')
            ->orderByDesc('id')
            ->get();

        $hasil = [];

        foreach ($project as $p) {
            $statusTahap = DB::table('sr_project_tahap_status')
                ->where('project_id', $p->id)
                ->pluck('status', 'tahap_id');

            $nilai = DB::table('sr_project_nilai')
                ->where('project_id', $p->id)
                ->where('siswa_id', $siswaId)
                ->pluck('skor', 'tahap_id');

            $punyaPortofolio = DB::table('sr_project_portofolio')->where('project_id', $p->id)->exists();

            $bobotTerpakai = 0;
            $jumlahNilai = 0;
            $barisTahap = [];

            foreach ($tahap as $t) {
                $status = $statusTahap[$t->id] ?? 'belum';
                $dipakai = $status !== 'tidak_dipakai';

                if ($dipakai && isset($nilai[$t->id])) {
                    $bobotTerpakai += (float) $t->bobot;
                    $jumlahNilai += (float) $nilai[$t->id] * (float) $t->bobot;
                }

                $barisTahap[] = [
                    'nama'   => $t->nama,
                    'bobot'  => (float) $t->bobot,
                    'status' => $status,
                    'skor'   => isset($nilai[$t->id]) ? (float) $nilai[$t->id] : null,
                ];
            }

            $hasil[] = [
                'nama'        => $p->nama,
                'status'      => $p->status,
                'mulai'       => $p->tanggal_mulai,
                'selesai'     => $p->tanggal_selesai,
                'deskripsi'   => $p->deskripsi,
                'tahap'       => $barisTahap,
                'nilai_akhir' => $bobotTerpakai > 0 ? round($jumlahNilai / $bobotTerpakai, 1) : null,
                'portofolio'  => $punyaPortofolio,
            ];
        }

        return $hasil;
    }

    /** 4. Asrama: kebersihan kamar, absensi jam tidur, izin. */
    private function asrama(int $siswaId, ?int $kamarId): array
    {
        $inspeksi = [
            'lembar' => 0, 'rata' => null, 'terbaik' => null,
            'terbersih' => 0, 'terkotor' => 0, 'perhatian' => 0, 'pagi' => 0, 'sore' => 0,
        ];

        if ($kamarId) {
            $nilaiKamar = DB::table('asrama_penilaian_kamars')->where('kamar_id', $kamarId);
            $inspeksi['lembar'] = (clone $nilaiKamar)->count();
            $inspeksi['rata'] = round((float) ((clone $nilaiKamar)->avg('total_skor') ?? 0), 1) ?: null;
            $inspeksi['terbaik'] = (clone $nilaiKamar)->max('total_skor');

            $inspeksi['terbersih'] = DB::table('asrama_penilaians')->where('kamar_terbersih_id', $kamarId)->count();
            $inspeksi['terkotor'] = DB::table('asrama_penilaians')->where('kamar_terkotor_id', $kamarId)->count();
            $inspeksi['perhatian'] = DB::table('asrama_penilaians')->where('kamar_perhatian_id', $kamarId)->count();
            $inspeksi['pagi'] = DB::table('asrama_penilaian_kamars as pk')
                ->join('asrama_penilaians as p', 'p.id', '=', 'pk.penilaian_id')
                ->where('pk.kamar_id', $kamarId)->where('p.sesi', 'pagi')->count();
            $inspeksi['sore'] = DB::table('asrama_penilaian_kamars as pk')
                ->join('asrama_penilaians as p', 'p.id', '=', 'pk.penilaian_id')
                ->where('pk.kamar_id', $kamarId)->where('p.sesi', 'sore')->count();
        }

        $absensi = DB::table('asrama_absensi')
            ->where('student_id', $siswaId)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $izin = DB::table('asrama_izins')
            ->where('student_id', $siswaId)
            ->orderByDesc('mulai')
            ->limit(5)
            ->get(['jenis', 'mulai', 'sampai', 'status', 'terlambat_menit']);

        return [
            'inspeksi' => $inspeksi,
            'absensi'  => $absensi,
            'izin'     => $izin,
        ];
    }

    /** 5. Penilaian Adab & Keasramaan (kosong = belum ada yang mengisi). */
    private function adab(int $siswaId): array
    {
        $periode = DB::table('penilaian_periode')->orderByDesc('aktif')->orderByDesc('id')->get(['id', 'nama', 'tahun_ajaran', 'terbuka']);
        $hasil = [];

        foreach ($periode as $per) {
            $perJenis = [];

            foreach (['adab' => 'Adab', 'keasramaan' => 'Keasramaan'] as $jenis => $label) {
                $aspek = DB::table('penilaian_jawaban as j')
                    ->join('penilaian_kriteria as k', 'k.id', '=', 'j.kriteria_id')
                    ->join('penilaian_sesi as s', 's.id', '=', 'j.sesi_id')
                    ->where('j.siswa_id', $siswaId)
                    ->where('s.periode_id', $per->id)
                    ->where('s.jenis', $jenis)
                    ->groupBy('k.id', 'k.pertanyaan', 'k.urutan')
                    ->orderBy('k.urutan')
                    ->selectRaw('k.pertanyaan, ROUND(AVG(j.skor),2) as rata, COUNT(*) as penilai')
                    ->get();

                if ($aspek->isEmpty()) {
                    continue;
                }

                $persen = round(((float) $aspek->avg('rata')) / 5 * 100, 1);

                $perJenis[$jenis] = [
                    'label'   => $label,
                    'aspek'   => $aspek,
                    'persen'  => $persen,
                    'predikat' => $this->predikat($persen),
                ];
            }

            if ($perJenis !== []) {
                $hasil[] = ['periode' => $per, 'jenis' => $perJenis];
            }
        }

        return $hasil;
    }

    /** 6. Catatan rapot (adab & student root). */
    private function catatan(int $siswaId): array
    {
        return DB::table('catatan_rapot as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.penulis_id')
            ->where('c.siswa_id', $siswaId)
            ->orderByDesc('c.id')
            ->get(['c.jenis', 'c.isi', 'c.tahun_ajaran', 'c.semester', 'u.name as penulis'])
            ->all();
    }

    private function predikat(float $persen): string
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('penilaian_pengaturan')
            && class_exists(\App\Models\PenilaianPengaturan::class)) {
            return \App\Models\PenilaianPengaturan::predikat($persen);
        }

        return $persen >= 90 ? 'A' : ($persen >= 80 ? 'B' : ($persen >= 70 ? 'C' : 'D'));
    }
}
