<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Rapikan data induk siswa:
 *  1. Seragamkan jenis kelamin (mis. "laki - laki" hasil impor Excel → "Laki-laki").
 *  2. Buang spasi / karakter tak terlihat (NBSP) pada NISN dan NIS.
 *  3. Siapkan izin 'hapus-permanen-siswa' (hanya Super Admin yang memilikinya,
 *     karena Super Admin lolos lewat Gate::before di AppServiceProvider).
 *
 * Aman dijalankan berulang & tidak menyentuh id siswa, relasi asrama, maupun poin.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Jenis kelamin tidak baku → baku
        DB::table('siswas')
            ->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(jk, ' ', ''), '-', ''), '_', '')) LIKE 'laki%'")
            ->update(['jk' => 'Laki-laki']);

        DB::table('siswas')
            ->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(jk, ' ', ''), '-', ''), '_', '')) LIKE 'lk%'")
            ->update(['jk' => 'Laki-laki']);

        DB::table('siswas')
            ->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(jk, ' ', ''), '-', ''), '_', '')) LIKE 'perempuan%'")
            ->update(['jk' => 'Perempuan']);

        DB::table('siswas')
            ->whereRaw("LOWER(REPLACE(REPLACE(REPLACE(jk, ' ', ''), '-', ''), '_', '')) LIKE 'pr%'")
            ->update(['jk' => 'Perempuan']);

        // 2. NISN / NIS dibersihkan (tanpa menebak angka yang hilang)
        $catatan = [];

        foreach (DB::table('siswas')->select('id', 'nisn', 'nis', 'nama_lengkap')->get() as $baris) {
            $ubah = [];

            $nisnBaru = preg_replace('/\D+/', '', (string) $baris->nisn);
            if ($nisnBaru !== '' && $nisnBaru !== (string) $baris->nisn) {
                $bentrok = DB::table('siswas')->where('nisn', $nisnBaru)->where('id', '!=', $baris->id)->exists();
                if ($bentrok) {
                    $catatan[] = "id {$baris->id} ({$baris->nama_lengkap}): NISN '{$baris->nisn}' tidak dibersihkan karena bentrok dengan NISN {$nisnBaru}";
                } else {
                    $ubah['nisn'] = $nisnBaru;
                }
            }

            $nisBaru = str_replace(' ', '', trim((string) $baris->nis));
            if ($nisBaru !== (string) $baris->nis) {
                $ubah['nis'] = $nisBaru;
            }

            if (! empty($ubah)) {
                DB::table('siswas')->where('id', $baris->id)->update($ubah);
            }
        }

        if (! empty($catatan)) {
            // Simpan jejak supaya bisa diperiksa manual dari server: storage/logs/laravel.log
            foreach ($catatan as $pesan) {
                logger()->warning('[rapikan-data-induk] ' . $pesan);
            }
        }

        // 3. Izin hapus permanen
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'hapus-permanen-siswa']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Tidak ada pembalikan: data yang sudah dirapikan tidak dikembalikan ke bentuk kotor.
    }
};
