<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsramaPenilaianKamar extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Menginduk ke Penilaian Harian
    public function penilaian()
    {
        return $this->belongsTo(AsramaPenilaian::class, 'penilaian_id');
    }

    // Relasi ke Kamar yang dinilai
    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }
}