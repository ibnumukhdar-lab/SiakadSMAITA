<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor Induk Pegawai Arafah (NIPA) untuk SEMUA akun pegawai
 * (guru, musyrif/musyrifah, kepala sekolah, kepala diniyah, dst).
 * Format penulisan: "YMA. 0000 000" -> 7 angka setelah awalan YMA.
 * Dikosongkan (nullable) karena diisi bertahap oleh masing-masing pemilik akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nipa', 30)->nullable()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nipa');
        });
    }
};
