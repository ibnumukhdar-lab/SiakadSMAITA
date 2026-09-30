<?php

namespace App\Models;

use App\Support\DiniyahArab;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * NILAI akhir satu santri pada satu mapel dalam satu periode.
 * Keputusan Kepala Sekolah (30 Sep 2026): satu nilai akhir per mapel per
 * semester (tidak dipecah tugas/UTS/UAS). Diisi musyrif/musyrifah; guru
 * pengampu boleh MENGOREKSI (dikoreksi_oleh + dikoreksi_pada).
 */
class DiniyahNilai extends Model
{
    protected $table = 'diniyah_nilai';

    protected $guarded = ['id'];

    protected $casts = [
        'nilai' => 'integer',
        'dikoreksi_pada' => 'datetime',
    ];

    public function mapel()
    {
        return $this->belongsTo(DiniyahMapel::class, 'mapel_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /** Predikat Arab dari nilai (ambang bisa disetel di sini bila kebijakan berubah). */
    public static function predikatArab(?int $nilai): ?string
    {
        return DiniyahArab::predikatArab($nilai);
    }

    /**
     * Daftar nilai satu santri pada satu periode, lengkap dengan mapel yang
     * SUDAH ditetapkan Kepala Diniyah walau nilainya belum diisi (supaya rapor
     * tetap menampilkan seluruh mata pelajaran, bukan hanya yang terisi).
     */
    public static function untukRapor(int $siswaId, ?int $periodeId): array
    {
        $siswa = Siswa::find($siswaId);

        if (! $siswa) {
            return [];
        }

        $mapel = DiniyahMapel::aktif()
            ->whereHas('kelas', fn ($q) => $q->where('kelas', $siswa->kelas))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $tersimpan = $periodeId
            ? static::where('siswa_id', $siswaId)->where('periode_id', $periodeId)->get()->keyBy('mapel_id')
            : collect();

        $baris = [];
        $jumlah = 0;
        $terisi = 0;

        foreach ($mapel as $m) {
            $n = $tersimpan[$m->id] ?? null;
            $nilai = $n?->nilai;

            if ($nilai !== null) {
                $jumlah += $nilai;
                $terisi++;
            }

            $baris[] = [
                'mapel'   => $m,
                'nilai'   => $nilai,
                'arab'    => $nilai !== null ? self::predikatArab($nilai) : null,
                'catatan' => $n?->catatan,
            ];
        }

        return [
            'baris'    => $baris,
            'rata'     => $terisi > 0 ? round($jumlah / $terisi) : null,
            'terisi'   => $terisi,
            'jumlah_mapel' => count($baris),
        ];
    }

    /** Jumlah santri yang sudah dinilai untuk satu mapel+periode (progres di daftar mapel). */
    public static function jumlahTerisi(int $mapelId, ?int $periodeId, array $santriIds): int
    {
        if ($periodeId === null || $santriIds === []) {
            return 0;
        }

        return (int) DB::table('diniyah_nilai')
            ->where('mapel_id', $mapelId)
            ->where('periode_id', $periodeId)
            ->whereNotNull('nilai')
            ->whereIn('siswa_id', $santriIds)
            ->count();
    }
}
