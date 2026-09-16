<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PenilaianPeriode extends Model
{
    protected $table = 'penilaian_periode';

    protected $guarded = ['id'];

    protected $casts = [
        'aktif' => 'boolean',
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date',
    ];

    public static function aktifSekarang(): ?self
    {
        return static::query()->where('aktif', true)->orderByDesc('id')->first();
    }

    /** Jadikan periode ini satu-satunya yang aktif. */
    public function jadikanAktif(): void
    {
        DB::transaction(function () {
            static::query()->where('id', '!=', $this->id)->update(['aktif' => false]);
            $this->update(['aktif' => true]);
        });
    }

    public function sesi()
    {
        return $this->hasMany(PenilaianSesi::class, 'periode_id');
    }
}
