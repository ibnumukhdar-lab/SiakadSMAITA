<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SrGroupMember extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'sr_group_members';
    protected $fillable = ['group_id', 'student_id', 'tanggal_gabung', 'tanggal_keluar'];

    // Relasi balik ke Grup
    public function group()
    {
        return $this->belongsTo(SrGroup::class, 'group_id');
    }

    // Relasi ke Siswa
    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }
}