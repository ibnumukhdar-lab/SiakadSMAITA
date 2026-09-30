<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesi evaluasi diatur PER KELAS (permintaan Fahri 21 Sep 2026).
 * Satu sesi = satu kelas + daftar week yang dicentang.
 * Kelas siswa dibaca dari tabel siswas.kelas saat siswa memasukkan NIS, sehingga
 * bila kelasnya diubah di Data Siswa, sesi yang tampil ikut menyesuaikan sendiri.
 *
 * Nilai kolom:
 *   'X' | 'XI' | 'XII'  → hanya untuk kelas itu
 *   'SEMUA'             → untuk semua kelas (evaluasi bersama)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bee_evaluasi', 'kelas')) {
            Schema::table('bee_evaluasi', function (Blueprint $table) {
                $table->string('kelas', 20)->nullable()->after('jenis');
                $table->index(['kelas', 'aktif']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bee_evaluasi', 'kelas')) {
            Schema::table('bee_evaluasi', function (Blueprint $table) {
                $table->dropIndex(['kelas', 'aktif']);
                $table->dropColumn('kelas');
            });
        }
    }
};
