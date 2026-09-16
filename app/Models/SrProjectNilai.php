<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SrProjectNilai extends Model
{
    protected $table = 'sr_project_nilai';

    protected $guarded = ['id'];

    protected $casts = ['skor' => 'integer'];

    public function project()
    {
        return $this->belongsTo(SrProject::class, 'project_id');
    }

    public function tahap()
    {
        return $this->belongsTo(SrProjectTahap::class, 'tahap_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }
}
