<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * MATA PELAJARAN kajian diniyah — ditetapkan Kepala Diniyah.
 * Guru pengampu boleh berasal dari database guru (`guru_id`) atau diketik
 * manual (`guru_nama`) bila pengampunya bukan pengguna SIAKAD.
 */
class DiniyahMapel extends Model
{
    protected $table = 'diniyah_mapels';

    protected $guarded = ['id'];

    public function kelas()
    {
        return $this->hasMany(DiniyahMapelKelas::class, 'mapel_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function scopeAktif($q)
    {
        return $q->where('status', 'aktif');
    }

    /** Nama guru yang ditampilkan di daftar & rapor. */
    public function guruTampil(): string
    {
        if ($this->guru_id && $this->guru) {
            return $this->guru->name;
        }

        return $this->guru_nama ?: '— belum ditetapkan —';
    }

    public function guruNipaTampil(): ?string
    {
        if ($this->guru_id && $this->guru) {
            return $this->guru->nipa;
        }

        return $this->guru_nipa;
    }

    /** Daftar nama kelas tempat mapel ini berlaku, mis. ['X','XI']. */
    public function daftarKelas(): array
    {
        return $this->kelas()->orderBy('kelas')->pluck('kelas')->all();
    }

    /** ID santri yang ikut mapel ini (ditarik dari kelasnya, bukan diketik ulang). */
    public function santriIds(): array
    {
        $kelas = $this->daftarKelas();

        if ($kelas === []) {
            return [];
        }

        return Siswa::query()
            ->whereNull('deleted_at')
            ->where('status', 'Aktif')
            ->whereIn('kelas', $kelas)
            ->orderByRaw("CASE kelas WHEN 'X' THEN 1 WHEN 'XI' THEN 2 WHEN 'XII' THEN 3 ELSE 4 END")
            ->orderBy('nama_lengkap')
            ->pluck('id')
            ->all();
    }

    /** Santri yang boleh disentuh pengguna ini (musyrif/musyrifah = kamar binaannya). */
    public function santriUntukPengguna(?User $pengguna): array
    {
        $semua = $this->santriIds();

        if (! $pengguna || $pengguna->bolehSemuaDiniyah()) {
            return $semua;
        }

        $kamarIds = DB::table('asrama_kamars')->where('musyrif_id', $pengguna->id)->pluck('id')->all();

        if ($kamarIds === []) {
            return [];
        }

        return DB::table('asrama_members')
            ->whereIn('kamar_id', $kamarIds)
            ->whereNull('tanggal_keluar')
            ->whereIn('student_id', $semua)
            ->pluck('student_id')
            ->all();
    }
}
