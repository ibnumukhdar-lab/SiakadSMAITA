<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Periode evaluasi BEE Smart (triwulan / semester). */
class BeeEvaluasi extends Model
{
    protected $table = 'bee_evaluasi';

    protected $fillable = [
        'judul', 'jenis', 'kelas', 'tahun_ajaran', 'semester', 'mulai', 'selesai', 'aktif',
        'jumlah_soal', 'durasi_menit', 'kkm', 'poin_lulus', 'maks_percobaan',
        'modul', 'catatan', 'dibuat_oleh',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'modul' => 'array',
        'mulai' => 'date',
        'selesai' => 'date',
    ];

    public function percobaan()
    {
        return $this->hasMany(BeeEvaluasiPercobaan::class, 'bee_evaluasi_id');
    }

    public function getLabelJenisAttribute(): string
    {
        return $this->jenis === 'semester' ? 'Semesteran' : 'Triwulan (3 bulan)';
    }

    public function scopeDibuka($q)
    {
        return $q->where('aktif', true)
            ->whereDate('mulai', '<=', now()->toDateString())
            ->whereDate('selesai', '>=', now()->toDateString());
    }

    /**
     * Sesi yang berlaku untuk satu kelas.
     * 'SEMUA' = sesi bersama untuk semua kelas.
     */
    public function scopeUntukKelas($q, ?string $kelas)
    {
        $kelas = trim((string) $kelas);

        if ($kelas === '') {
            return $q->whereRaw('1 = 0');   // tanpa kelas → tidak ada sesi sama sekali
        }

        return $q->where(fn ($x) => $x->where('kelas', $kelas)->orWhere('kelas', 'SEMUA'));
    }

    public function getLabelKelasAttribute(): string
    {
        $kelas = trim((string) $this->kelas);

        return match ($kelas) {
            '' => 'Belum ditentukan',
            'SEMUA' => 'Semua kelas',
            default => 'Kelas '.$kelas,
        };
    }
}
