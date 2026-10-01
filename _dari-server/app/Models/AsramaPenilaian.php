<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsramaPenilaian extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function musyrif() {
        return $this->belongsTo(User::class, 'musyrif_id');
    }

    public function kamarTerbersih() {
        return $this->belongsTo(AsramaKamar::class, 'kamar_terbersih_id');
    }

    public function kamarTerkotor() {
        return $this->belongsTo(AsramaKamar::class, 'kamar_terkotor_id');
    }

    // Kamar yang masuk daftar "perlu diperhatikan" (terendah, walau semua kamar bersih)
    public function kamarPerhatian() {
        return $this->belongsTo(AsramaKamar::class, 'kamar_perhatian_id');
    }

    public function rincianKamars() {
        return $this->hasMany(AsramaPenilaianKamar::class, 'penilaian_id');
    }
}