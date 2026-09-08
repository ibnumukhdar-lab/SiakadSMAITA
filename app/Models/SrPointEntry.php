<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SrPointEntry extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'sr_point_entries';
    protected $fillable = ['student_id', 'group_id', 'criteria_id', 'poin', 'catatan', 'input_by', 'tanggal_kejadian'];

    // Relasi ke Siswa yang melanggar/berprestasi
    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    // Relasi ke Grup saat kejadian (Snapshot)
    public function group()
    {
        return $this->belongsTo(SrGroup::class, 'group_id');
    }

    // Relasi ke Kriteria
    public function criteria()
    {
        return $this->belongsTo(SrPointCriteria::class, 'criteria_id');
    }

    // Relasi ke Guru yang menginput poin
    public function inputter()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}