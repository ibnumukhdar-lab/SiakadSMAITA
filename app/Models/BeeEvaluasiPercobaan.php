<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satu percobaan mengerjakan evaluasi oleh seorang siswa. */
class BeeEvaluasiPercobaan extends Model
{
    protected $table = 'bee_evaluasi_percobaan';

    protected $fillable = [
        'bee_evaluasi_id', 'siswa_id', 'bahasa', 'kode', 'soal', 'kunci', 'jawaban',
        'benar', 'salah', 'nilai', 'status', 'mulai_pada', 'selesai_pada', 'ip',
    ];

    protected $casts = [
        'soal' => 'array',
        'kunci' => 'array',
        'jawaban' => 'array',
        'mulai_pada' => 'datetime',
        'selesai_pada' => 'datetime',
    ];

    /** Kunci jawaban tidak boleh ikut terserialisasi ke peramban. */
    protected $hidden = ['kunci'];

    public function evaluasi()
    {
        return $this->belongsTo(BeeEvaluasi::class, 'bee_evaluasi_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
