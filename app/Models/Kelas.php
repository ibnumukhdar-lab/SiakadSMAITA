<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $guarded = ['id'];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan' => 'integer',
    ];

    public const TINGKAT = ['X', 'XI', 'XII', 'Lulus'];

    /** Kelas urut sesuai kolom urutan lalu nama. */
    public function scopeTerurut($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    /** Daftar nama kelas — untuk pilihan di form & filter Data Siswa. */
    public static function daftarNama(bool $hanyaAktif = true): array
    {
        $q = static::query()->terurut();
        if ($hanyaAktif) {
            $q->where('aktif', true);
        }

        return $q->pluck('nama')->all();
    }

    /** Jumlah siswa per nama kelas (bisa termasuk yang ada di tong sampah). */
    public static function jumlahSiswaPerKelas(bool $denganTongSampah = false): array
    {
        $q = DB::table('siswas')->whereNotNull('kelas')->where('kelas', '!=', '');
        if (! $denganTongSampah) {
            $q->whereNull('deleted_at');
        }

        return $q->select('kelas', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kelas')
            ->pluck('jumlah', 'kelas')
            ->toArray();
    }

    /**
     * Jumlah siswa AKTIF per tingkat (X/XI/XII).
     * Kelas buatan sendiri ikut terhitung: "X IPA 1" (tingkat X) dihitung sebagai kelas X.
     */
    public static function jumlahPerTingkat(): array
    {
        $hasil = ['X' => 0, 'XI' => 0, 'XII' => 0];

        $petaNamaKeTingkat = static::all(['nama', 'tingkat'])
            ->mapWithKeys(fn ($k) => [trim((string) $k->nama) => strtoupper((string) $k->tingkat)])
            ->all();

        $baris = DB::table('siswas')
            ->where('status', 'Aktif')
            ->whereNull('deleted_at')
            ->whereNotNull('kelas')
            ->select('kelas', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('kelas')
            ->pluck('jumlah', 'kelas')
            ->toArray();

        foreach ($baris as $namaKelas => $jumlah) {
            $nama = trim((string) $namaKelas);
            $tingkat = $petaNamaKeTingkat[$nama] ?? strtoupper($nama);
            if (array_key_exists($tingkat, $hasil)) {
                $hasil[$tingkat] += (int) $jumlah;
            }
        }

        return $hasil;
    }
}
