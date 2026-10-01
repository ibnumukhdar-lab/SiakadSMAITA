<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    // Izinkan kolom-kolom ini diisi form
    protected $fillable = [
        'nama_sekolah', 
        'motto', 
        'logo_path', 
        'sampul_path'
    ];
}