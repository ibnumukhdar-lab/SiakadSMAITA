<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SrGroup extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sr_groups';
    
    // Saya sudah tambahkan 'warna_grup' di bagian paling belakang array ini:
    protected $fillable = ['nama_grup', 'mentor_id', 'tahun_ajaran_mulai', 'status', 'warna_grup'];

    // Relasi ke Guru (Mentor)
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Relasi ke Anggota Grup
    public function members()
    {
        return $this->hasMany(SrGroupMember::class, 'group_id');
    }
}