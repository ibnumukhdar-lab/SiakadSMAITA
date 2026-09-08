<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SrPointCriteria extends Model
{
    use HasFactory, HasUuids;

    // Menyesuaikan dengan nama tabel yang di-generate Laravel sebelumnya
    protected $table = 'sr_point_criteria'; 
    protected $fillable = ['kategori', 'nama_perilaku', 'deskripsi', 'poin', 'tingkat', 'status'];
}