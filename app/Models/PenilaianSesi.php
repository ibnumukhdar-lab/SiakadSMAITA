<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenilaianSesi extends Model
{
    protected $table = 'penilaian_sesi';

    protected $guarded = ['id'];

    protected $casts = ['difinalkan_pada' => 'datetime'];

    public const JENIS = ['adab' => 'Adab', 'keasramaan' => 'Keasramaan'];

    public const PERAN = [
        'kepala_diniyah' => 'Kepala Diniyah',
        'penanggungjawab_asrama' => 'Penanggungjawab Asrama',
    ];

    public function periode()
    {
        return $this->belongsTo(PenilaianPeriode::class, 'periode_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    public function jawaban()
    {
        return $this->hasMany(PenilaianJawaban::class, 'sesi_id');
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    /**
     * Ringkasan hasil satu sesi: siswa_id => [
     *   total, jumlah_jawaban, persentase, predikat, lengkap
     * ]
     * Persentase dihitung dari pertanyaan yang SUDAH dijawab
     * (persentase = total skor / (jumlah jawaban x 5) x 100) supaya penilaian yang
     * belum lengkap tetap terbaca, dan ditandai lewat `lengkap`.
     */
    public function hasilPerSiswa(): array
    {
        $jumlahKriteria = PenilaianKriteria::daftarAktif($this->jenis)->count();

        $baris = DB::table('penilaian_jawaban')
            ->where('sesi_id', $this->id)
            ->select('siswa_id', DB::raw('SUM(skor) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('siswa_id')
            ->get();

        $hasil = [];
        foreach ($baris as $b) {
            $jumlah = (int) $b->jumlah;
            $maks = $jumlah * 5;
            $persen = $maks > 0 ? round(((float) $b->total / $maks) * 100, 2) : null;

            $hasil[$b->siswa_id] = [
                'total' => (int) $b->total,
                'jumlah' => $jumlah,
                'lengkap' => $jumlah >= $jumlahKriteria,
                'persentase' => $persen,
                'predikat' => PenilaianPengaturan::predikat($persen),
            ];
        }

        return $hasil;
    }

    /**
     * Hasil satu PERIODE untuk satu jenis, dikelompokkan per siswa:
     * siswa_id => [ [sesi_id, penilai_id, penilai_peran, status, jumlah, total, persentase, predikat], ... ]
     * Satu siswa bisa punya dua entri (Kepala Diniyah & Penanggungjawab Asrama).
     */
    public static function hasilPeriode(int $periodeId, string $jenis): array
    {
        $sesi = static::query()
            ->where('periode_id', $periodeId)
            ->jenis($jenis)
            ->get()
            ->keyBy('id');

        if ($sesi->isEmpty()) {
            return [];
        }

        $baris = DB::table('penilaian_jawaban as j')
            ->join('penilaian_sesi as s', 's.id', '=', 'j.sesi_id')
            ->where('s.periode_id', $periodeId)
            ->where('s.jenis', $jenis)
            ->select('j.sesi_id', 'j.siswa_id', DB::raw('SUM(j.skor) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('j.sesi_id', 'j.siswa_id')
            ->get();

        $hasil = [];
        foreach ($baris as $b) {
            $jumlah = (int) $b->jumlah;
            $maks = $jumlah * 5;
            $persen = $maks > 0 ? round(((float) $b->total / $maks) * 100, 2) : null;
            $induk = $sesi[$b->sesi_id] ?? null;

            $hasil[$b->siswa_id][] = [
                'sesi_id' => (int) $b->sesi_id,
                'penilai_id' => $induk?->penilai_id,
                'penilai_peran' => $induk?->penilai_peran,
                'status' => $induk?->status,
                'jumlah' => $jumlah,
                'total' => (int) $b->total,
                'persentase' => $persen,
                'predikat' => PenilaianPengaturan::predikat($persen),
            ];
        }

        return $hasil;
    }
}
