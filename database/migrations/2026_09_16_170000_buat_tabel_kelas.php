<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master KELAS (pengelompokan kelas).
 *
 * Sengaja TIDAK mengubah kolom `siswas.kelas` (tetap teks) supaya modul yang sudah
 * berjalan membaca data siswa apa adanya: Asrama (kamar & penilaian), Student Root
 * (grup binaan & poin), Bee Smart, dan dashboard. Tabel ini hanya menjadi DAFTAR
 * kelas yang dipakai form/filter Data Siswa.
 *
 * Nilai lama (X, XI, XII, Lulus) di-seed supaya 130 data siswa yang ada langsung
 * punya pasangan di daftar kelas — tidak ada data siswa yang disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kelas')) {
            Schema::create('kelas', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 60)->unique();
                $table->string('tingkat', 10)->nullable();   // X / XI / XII / Lulus
                $table->unsignedInteger('urutan')->default(0);
                $table->string('keterangan', 150)->nullable();
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }

        $bawaan = [
            ['nama' => 'X',     'tingkat' => 'X',     'urutan' => 1],
            ['nama' => 'XI',    'tingkat' => 'XI',    'urutan' => 2],
            ['nama' => 'XII',   'tingkat' => 'XII',   'urutan' => 3],
            ['nama' => 'Lulus', 'tingkat' => 'Lulus', 'urutan' => 4],
        ];

        foreach ($bawaan as $baris) {
            DB::table('kelas')->updateOrInsert(
                ['nama' => $baris['nama']],
                $baris + ['aktif' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
