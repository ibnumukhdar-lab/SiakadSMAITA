<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsramaAbsensi extends Model
{
    protected $table = 'asrama_absensi';

    protected $guarded = [];

    /** Sesi absensi harian asrama. */
    public const SESI = [
        'subuh'   => 'Subuh',
        'pagi'    => 'Apel Pagi',
        'maghrib' => 'Maghrib',
        'isya'    => 'Isya',
        'tidur'   => 'Tidur',
    ];

    /** Status kehadiran yang dikenal sistem. */
    public const STATUS = [
        'hadir'  => 'Hadir',
        'telat'  => 'Telat',
        'izin'   => 'Izin',
        'sakit'  => 'Sakit',
        'pulang' => 'Pulang',
        'alpa'   => 'Alpa',
    ];

    /** Status yang TIDAK dihitung sebagai hadir. */
    public const BUKAN_HADIR = ['telat', 'izin', 'sakit', 'pulang', 'alpa'];

    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public static function labelSesi(?string $sesi): string
    {
        return self::SESI[$sesi] ?? ($sesi ?: '-');
    }

    public static function labelStatus(?string $status): string
    {
        return self::STATUS[$status] ?? ($status ?: '-');
    }
}
