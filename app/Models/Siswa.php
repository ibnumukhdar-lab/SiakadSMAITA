<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    // Memberi tahu Laravel nama tabel yang digunakan
    protected $table = 'siswas';

    // Mengizinkan semua kolom diisi secara massal (keamanan)
    protected $guarded = ['id'];

    // Otomatis mengubah JSON menjadi Array saat ditarik, dan sebaliknya saat disimpan
    protected $casts = [
        'prestasi' => 'array',
        'pelanggaran' => 'array',
    ];
}