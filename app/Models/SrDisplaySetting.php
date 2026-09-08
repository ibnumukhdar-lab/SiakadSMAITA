<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrDisplaySetting extends Model
{
    use HasFactory;

    protected $table = 'sr_display_settings';
    
    protected $fillable = [
        'judul_utama',
        'logo',
        'background_image',
        'durasi_slide',
    ];
}