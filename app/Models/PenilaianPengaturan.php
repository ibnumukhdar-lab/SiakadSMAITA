<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pengaturan ringan penilaian (dipakai untuk ambang predikat A/B/C/D).
 */
class PenilaianPengaturan extends Model
{
    protected $table = 'penilaian_pengaturan';

    protected $primaryKey = 'kunci';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public const BAWAAN = [
        'predikat_a' => 90,
        'predikat_b' => 80,
        'predikat_c' => 70,
    ];

    /** Ambang predikat: [A, B, C] */
    public static function ambang(): array
    {
        $baris = static::query()->pluck('nilai', 'kunci')->toArray();

        $ambil = function (string $kunci) use ($baris) {
            $nilai = $baris[$kunci] ?? self::BAWAAN[$kunci];
            $angka = (float) str_replace(',', '.', (string) $nilai);

            return $angka > 0 ? $angka : self::BAWAAN[$kunci];
        };

        return ['a' => $ambil('predikat_a'), 'b' => $ambil('predikat_b'), 'c' => $ambil('predikat_c')];
    }

    public static function simpanAmbang(float $a, float $b, float $c): void
    {
        foreach (['predikat_a' => $a, 'predikat_b' => $b, 'predikat_c' => $c] as $kunci => $nilai) {
            static::updateOrCreate(['kunci' => $kunci], ['nilai' => (string) $nilai]);
        }
    }

    /** Predikat huruf dari persentase (A/B/C/D). */
    public static function predikat(?float $persentase): ?string
    {
        if ($persentase === null) {
            return null;
        }

        $ambang = self::ambang();

        if ($persentase >= $ambang['a']) {
            return 'A';
        }
        if ($persentase >= $ambang['b']) {
            return 'B';
        }
        if ($persentase >= $ambang['c']) {
            return 'C';
        }

        return 'D';
    }

    /** Warna kelas Tailwind untuk lencana predikat. */
    public static function warnaPredikat(?string $predikat): string
    {
        return match ($predikat) {
            'A' => 'bg-green-50 text-green-700 border-green-200',
            'B' => 'bg-blue-50 text-blue-700 border-blue-200',
            'C' => 'bg-amber-50 text-amber-700 border-amber-200',
            'D' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-slate-50 text-slate-400 border-slate-200',
        };
    }
}
