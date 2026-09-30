<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PERIODE (semester) penilaian diniyah — sama polanya dengan PenilaianPeriode.
 * Hanya Kepala Diniyah (izin `kelola-periode-diniyah`) yang membuka/menutup.
 */
class DiniyahPeriode extends Model
{
    protected $table = 'diniyah_periode';

    protected $guarded = ['id'];

    protected $casts = [
        'terbuka' => 'boolean',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    public const SEMESTER = [
        's1' => 'Semester 1 · Juli–Desember',
        's2' => 'Semester 2 · Januari–Juni',
    ];

    /** Periode yang sedang terbuka (biasanya satu). */
    public static function terbukaSekarang(): ?self
    {
        return static::where('terbuka', true)->orderByDesc('id')->first();
    }

    /** Periode aktif = yang terbuka, kalau tidak ada pakai yang terakhir. */
    public static function aktifSekarang(): ?self
    {
        return static::terbukaSekarang() ?? static::orderByDesc('id')->first();
    }

    public function buka(?int $userId = null): void
    {
        $this->update([
            'terbuka' => true,
            'dibuka_oleh' => $userId,
            'dibuka_pada' => now(),
        ]);
    }

    public function tutup(?int $userId = null): void
    {
        $this->update([
            'terbuka' => false,
            'ditutup_oleh' => $userId,
            'ditutup_pada' => now(),
        ]);
    }

    public function label(): string
    {
        // Hindari pengulangan tahun ajaran bila namanya sudah memuatnya
        // (mis. nama "Semester 1 · 2026/2027" + tahun_ajaran "2026/2027").
        if ($this->tahun_ajaran && ! str_contains((string) $this->nama, (string) $this->tahun_ajaran)) {
            return $this->nama . ' · ' . $this->tahun_ajaran;
        }

        return $this->nama;
    }

    /** Semester berjalan menurut kalender (Juli–Desember = s1). */
    public static function semesterKalender(): string
    {
        return (int) now()->format('n') >= 7 ? 's1' : 's2';
    }

    /** Tahun ajaran berjalan, mis. "2026/2027". */
    public static function tahunAjaranKalender(): string
    {
        $tahun = (int) now()->format('Y');
        $awal = (int) now()->format('n') >= 7 ? $tahun : $tahun - 1;

        return $awal . '/' . ($awal + 1);
    }
}
