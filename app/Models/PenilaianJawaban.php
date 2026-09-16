<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenilaianJawaban extends Model
{
    protected $table = 'penilaian_jawaban';

    protected $guarded = ['id'];

    protected $casts = ['skor' => 'integer'];

    public function sesi()
    {
        return $this->belongsTo(PenilaianSesi::class, 'sesi_id');
    }

    public function kriteria()
    {
        return $this->belongsTo(PenilaianKriteria::class, 'kriteria_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
