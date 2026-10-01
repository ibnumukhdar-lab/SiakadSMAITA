<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SrProjectTahapStatus extends Model
{
    protected $table = 'sr_project_tahap_status';

    protected $guarded = ['id'];

    protected $casts = ['tanggal' => 'date'];

    public function project()
    {
        return $this->belongsTo(SrProject::class, 'project_id');
    }

    public function tahap()
    {
        return $this->belongsTo(SrProjectTahap::class, 'tahap_id');
    }
}
