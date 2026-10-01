<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $guarded = ['id'];

    protected $casts = [
        'aktif' => 'boolean',
        'awal' => 'date',
        'akhir' => 'date',
    ];

    /** Tahun ajaran yang sedang aktif (dipakai sebagai nilai bawaan aplikasi). */
    public static function aktifSekarang(): ?self
    {
        return static::query()->where('aktif', true)->orderByDesc('id')->first();
    }

    /** Nama tahun ajaran aktif — dipakai form siswa, import, dan kenaikan kelas. */
    public static function namaAktif(): ?string
    {
        return static::aktifSekarang()?->nama;
    }

    /** Jadikan tahun ini satu-satunya yang aktif. */
    public function jadikanAktif(): void
    {
        DB::transaction(function () {
            static::query()->where('id', '!=', $this->id)->update(['aktif' => false]);
            $this->update(['aktif' => true]);
        });
    }

    public static function daftarNama(): array
    {
        return static::query()->orderByDesc('nama')->pluck('nama')->all();
    }
}
