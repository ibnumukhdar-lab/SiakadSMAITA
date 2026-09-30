<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * PERTEMUAN kajian diniyah: satu tanggal + satu mapel + jam ke-.
 * Dasar pencatatan absensi; materi dicatat di sini juga.
 */
class DiniyahPertemuan extends Model
{
    protected $table = 'diniyah_pertemuan';

    protected $guarded = ['id'];

    protected $casts = ['tanggal' => 'date'];

    public function mapel()
    {
        return $this->belongsTo(DiniyahMapel::class, 'mapel_id');
    }

    public function absensi()
    {
        return $this->hasMany(DiniyahAbsensi::class, 'pertemuan_id');
    }

    /** Rekap status pada pertemuan ini. */
    public function rekap(): array
    {
        $hasil = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        foreach ($this->absensi()->selectRaw('status, COUNT(*) as j')->groupBy('status')->get() as $b) {
            $hasil[$b->status] = (int) $b->j;
        }

        return $hasil;
    }

    /** Ringkas: '12 hadir · 1 sakit' untuk daftar pertemuan. */
    public function ringkasRekap(): string
    {
        $r = $this->rekap();

        return "{$r['hadir']} hadir · {$r['sakit']} sakit · {$r['izin']} izin · {$r['alpa']} alpa";
    }
}
