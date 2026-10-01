<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianKriteria extends Model
{
    protected $table = 'penilaian_kriteria';

    protected $guarded = ['id'];

    protected $casts = ['aktif' => 'boolean', 'urutan' => 'integer'];

    public const JENIS = ['adab' => 'Adab', 'keasramaan' => 'Keasramaan'];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public static function daftarAktif(string $jenis)
    {
        return static::query()->jenis($jenis)->aktif()->terurut()->get();
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }
}
