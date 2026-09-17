<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrProjectDokumen extends Model
{
    protected $table = 'sr_project_dokumen';

    protected $guarded = ['id'];

    protected $casts = ['urutan' => 'integer'];

    public function project()
    {
        return $this->belongsTo(SrProject::class, 'project_id');
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    /** URL file (dilayani rute /berkas/...). */
    public function getUrlAttribute(): string
    {
        return url('berkas/' . ltrim((string) $this->path, '/'));
    }
}
