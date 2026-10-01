<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrProjectTahap extends Model
{
    protected $table = 'sr_project_tahap';

    protected $guarded = ['id'];

    protected $casts = ['bobot' => 'float', 'urutan' => 'integer', 'aktif' => 'boolean'];

    public const STATUS_TAHAP = [
        'belum' => 'Belum',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
        'tidak_dipakai' => 'Tidak dipakai',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public static function daftarAktif()
    {
        return static::query()->aktif()->terurut()->get();
    }

    public static function bobotTotal(): float
    {
        return (float) static::query()->aktif()->sum('bobot');
    }
}
