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
        'terbuka' => 'boolean',
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
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

    /**
     * Periode yang sedang DIBUKA untuk pengisian (dipakai halaman Isi Rapor).
     * Bila pengelola belum pernah membuka/menutup sama sekali, jatuh ke periode aktif —
     * supaya pengisian tidak mati mendadak pada sekolah yang belum memakai tombolnya.
     * Kalau periode aktif sudah pernah DITUTUP, hasilnya null (pengisian memang berhenti).
     */
    public static function terbukaSekarang(): ?self
    {
        $terbuka = static::query()->where('terbuka', true)->orderByDesc('aktif')->orderByDesc('id')->first();

        if ($terbuka) {
            return $terbuka;
        }

        $aktif = static::aktifSekarang();

        return ($aktif && $aktif->dibuka_pada === null && $aktif->ditutup_pada === null) ? $aktif : null;
    }

    /**
     * Buka sesi isi rapor + kembalikan seluruh lembar periode ini ke status draft.
     * Hanya SATU sesi yang boleh terbuka: periode lain yang masih terbuka ditutup dulu
     * (mis. Semester 1 ditutup otomatis saat Semester 2 dibuka). Periode yang dibuka
     * sekaligus dijadikan periode berjalan (`aktif`) supaya default rapor/rekap ikut pindah.
     */
    public function buka(int $olehUserId): void
    {
        DB::transaction(function () use ($olehUserId) {
            foreach (static::query()->where('terbuka', true)->where('id', '!=', $this->id)->get() as $lain) {
                $lain->tutup($olehUserId);
            }

            $this->update([
                'terbuka' => true,
                'dibuka_oleh' => $olehUserId,
                'dibuka_pada' => now(),
            ]);

            PenilaianSesi::where('periode_id', $this->id)
                ->update(['status' => 'draft', 'difinalkan_pada' => null]);
        });

        $this->jadikanAktif();
    }

    /** Tutup sesi isi rapor + kunci seluruh lembar periode ini (status final). */
    public function tutup(int $olehUserId): void
    {
        DB::transaction(function () use ($olehUserId) {
            $this->update([
                'terbuka' => false,
                'ditutup_oleh' => $olehUserId,
                'ditutup_pada' => now(),
            ]);

            PenilaianSesi::where('periode_id', $this->id)
                ->update(['status' => 'final', 'difinalkan_pada' => now()]);
        });
    }

    public function pembuka()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    public function penutup()
    {
        return $this->belongsTo(User::class, 'ditutup_oleh');
    }

    /** Keterangan singkat status sesi untuk tampilan. */
    public function labelStatus(): string
    {
        return $this->terbuka ? 'Terbuka' : 'Ditutup';
    }

    public function sesi()
    {
        return $this->hasMany(PenilaianSesi::class, 'periode_id');
    }
}
