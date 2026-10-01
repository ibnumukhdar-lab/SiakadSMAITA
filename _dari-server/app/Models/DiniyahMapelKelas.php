<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Mapel berlaku untuk kelas mana (X / XI / XII). */
class DiniyahMapelKelas extends Model
{
    protected $table = 'diniyah_mapel_kelas';

    protected $guarded = ['id'];
}
