<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * ABSENSI kehadiran santri pada satu pertemuan.
 * Status: hadir | sakit | izin | alpa  (rekapnya masuk rapor, bukan diketik manual).
 */
class DiniyahAbsensi extends Model
{
    protected $table = 'diniyah_absensi';

    protected $guarded = ['id'];

    public const STATUS = [
        'hadir' => 'Hadir',
        'sakit' => 'Sakit',
        'izin' => 'Izin',
        'alpa' => 'Alpa',
    ];

    public const STATUS_ARAB = [
        'hadir' => 'حاضر',
        'sakit' => 'مريض',
        'izin' => 'بإذن',
        'alpa' => 'غائب بلا عذر',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(DiniyahPertemuan::class, 'pertemuan_id');
    }

    /**
     * Rekap kehadiran satu santri pada satu periode (semua mapel).
     * Dipakai rapor: jumlah hadir/sakit/izin/alpa + total pertemuan.
     */
    public static function rekapSiswa(int $siswaId, ?int $periodeId = null): array
    {
        $q = DB::table('diniyah_absensi as a')
            ->join('diniyah_pertemuan as p', 'p.id', '=', 'a.pertemuan_id')
            ->where('a.siswa_id', $siswaId);

        if ($periodeId) {
            $q->where('p.periode_id', $periodeId);
        }

        $hasil = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        foreach ($q->selectRaw('a.status, COUNT(*) as j')->groupBy('a.status')->get() as $b) {
            if (array_key_exists($b->status, $hasil)) {
                $hasil[$b->status] = (int) $b->j;
            }
        }

        $hasil['total'] = array_sum($hasil);

        return $hasil;
    }

    /** Rekap per mapel untuk satu santri (rapor bisa merinci per mata pelajaran). */
    public static function rekapSiswaPerMapel(int $siswaId, ?int $periodeId = null): array
    {
        $q = DB::table('diniyah_absensi as a')
            ->join('diniyah_pertemuan as p', 'p.id', '=', 'a.pertemuan_id')
            ->join('diniyah_mapels as m', 'm.id', '=', 'p.mapel_id')
            ->where('a.siswa_id', $siswaId);

        if ($periodeId) {
            $q->where('p.periode_id', $periodeId);
        }

        $kumpulan = [];

        foreach ($q->selectRaw('p.mapel_id, m.nama, a.status, COUNT(*) as j')->groupBy('p.mapel_id', 'm.nama', 'a.status')->get() as $b) {
            $kumpulan[$b->mapel_id]['nama'] = $b->nama;
            $kumpulan[$b->mapel_id][$b->status] = (int) $b->j;
        }

        return $kumpulan;
    }
}
