<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AsramaIzin extends Model
{
    protected $table = 'asrama_izins';

    protected $guarded = [];

    public const JENIS = [
        'pulang'  => 'Pulang / Bermalam',
        'keluar'  => 'Keluar Sementara',
        'sakit'   => 'Sakit / Berobat',
        'lainnya' => 'Lainnya',
    ];

    public const STATUS = [
        'diajukan'  => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak'   => 'Ditolak',
        'selesai'   => 'Sudah Kembali',
    ];

    public function student()
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function kamar()
    {
        return $this->belongsTo(AsramaKamar::class, 'kamar_id');
    }

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /** Status absensi yang otomatis dipakai saat siswa berizin pada tanggal tertentu. */
    public function statusAbsensi(): string
    {
        return match ($this->jenis) {
            'pulang' => 'pulang',
            'sakit'  => 'sakit',
            default  => 'izin',
        };
    }

    /** Apakah izin ini mencakup tanggal tertentu (dan sudah disetujui). */
    public function mencakup($tanggal): bool
    {
        if ($this->status !== 'disetujui') {
            return false;
        }

        $t = Carbon::parse($tanggal)->toDateString();

        return $t >= Carbon::parse($this->mulai)->toDateString()
            && $t <= Carbon::parse($this->sampai)->toDateString();
    }

    /** Sudah lewat tanggal kembali tapi belum dicatat kembali. */
    public function terlambat(): bool
    {
        return $this->status === 'disetujui'
            && Carbon::parse($this->sampai)->lt(now()->startOfDay());
    }

    public function labelJenis(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
