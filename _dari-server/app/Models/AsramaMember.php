<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsramaMember extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke Data Kamar
    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }

    // Relasi ke Data Siswa
    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}