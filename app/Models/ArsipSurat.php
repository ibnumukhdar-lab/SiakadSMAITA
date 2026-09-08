<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipSurat extends Model
{
 // Mengizinkan kolom-kolom ini diisi data dari form
    protected $fillable = [
        'jenis_surat', 
        'nomor_surat', 
        'tanggal_surat', 
        'pihak_terkait', 
        'perihal', 
        'file_surat', 
        'link_drive'
    ];
}
