<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsramaKamar extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke Guru/Musyrif
    public function musyrif()
    {
        return $this->belongsTo(User::class, 'musyrif_id');
    }

    // Relasi ke Histori Seluruh Anggota Kamar
    public function members()
    {
        return $this->hasMany(AsramaMember::class, 'kamar_id');
    }

    // Mengambil anggota kamar yang SAAT INI MASIH AKTIF di kamar tersebut
    public function activeMembers()
    {
        return $this->hasMany(AsramaMember::class, 'kamar_id')->whereNull('tanggal_keluar');
    }
}