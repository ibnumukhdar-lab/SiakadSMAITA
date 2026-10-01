<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrProjectPortofolio extends Model
{
    protected $table = 'sr_project_portofolio';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_presentasi' => 'date',
        'diselesaikan_pada' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(SrProject::class, 'project_id');
    }

    public function penyusun()
    {
        return $this->belongsTo(User::class, 'disusun_oleh');
    }

    /** Bagian narasi yang sudah terisi (untuk indikator kelengkapan). */
    public function bagianTerisi(): int
    {
        $isi = 0;
        foreach (['ringkasan', 'latar_belakang', 'tujuan', 'pelaksanaan', 'hasil', 'refleksi'] as $kolom) {
            if (trim((string) $this->{$kolom}) !== '') {
                $isi++;
            }
        }

        return $isi;
    }
}
