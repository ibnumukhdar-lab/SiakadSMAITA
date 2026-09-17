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

    /** Jam boleh keluar sebagai teks H:i (atau '-'). */
    public function jamKeluarText(): string
    {
        return $this->jam_keluar ? substr((string) $this->jam_keluar, 0, 5) : '-';
    }

    /** Jam wajib kembali sebagai teks H:i (atau '-'). */
    public function jamWajibKembaliText(): string
    {
        return $this->jam_wajib_kembali ? substr((string) $this->jam_wajib_kembali, 0, 5) : '-';
    }

    /** Waktu mulai boleh keluar = tanggal mulai + jam keluar. */
    public function bolehKeluarPada(): Carbon
    {
        $t = Carbon::parse($this->mulai)->startOfDay();

        if ($this->jam_keluar) {
            [$h, $m] = array_pad(explode(':', substr((string) $this->jam_keluar, 0, 5)), 2, '0');
            $t->setTime((int) $h, (int) $m);
        }

        return $t;
    }

    /** Batas waktu wajib kembali = tanggal selesai + jam wajib kembali. */
    public function batasKembaliPada(): Carbon
    {
        $t = Carbon::parse($this->sampai)->startOfDay();

        if ($this->jam_wajib_kembali) {
            [$h, $m] = array_pad(explode(':', substr((string) $this->jam_wajib_kembali, 0, 5)), 2, '0');
            $t->setTime((int) $h, (int) $m);
        } else {
            $t->endOfDay();
        }

        return $t;
    }

    /** Menit keterlambatan: 0 bila tepat waktu / lebih awal. */
    public function menitTerlambat($waktuKembali = null): int
    {
        $waktu = $waktuKembali ? Carbon::parse($waktuKembali) : ($this->kembali_at ? Carbon::parse($this->kembali_at) : now());
        $selisih = $this->batasKembaliPada()->diffInMinutes($waktu, false);

        return $selisih > 0 ? (int) round($selisih) : 0;
    }

    /** Sudah lewat batas jam kembali tapi belum dicatat kembali. */
    public function terlambat(): bool
    {
        return $this->status === 'disetujui' && now()->timestamp > $this->batasKembaliPada()->timestamp;
    }

    /** Sedang di luar asrama (sudah jam keluar, belum lewat batas kembali). */
    public function sedangDiLuar(): bool
    {
        return $this->status === 'disetujui'
            && now()->timestamp >= $this->bolehKeluarPada()->timestamp
            && now()->timestamp <= $this->batasKembaliPada()->timestamp;
    }

    /** Ringkasan jam untuk tampilan: "Keluar 06.00 · wajib kembali 17.30". */
    public function labelWaktu(): string
    {
        return 'Keluar ' . str_replace(':', '.', $this->jamKeluarText())
            . ' · wajib kembali ' . str_replace(':', '.', $this->jamWajibKembaliText());
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
