<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk Data Induk Siswa: filter/pencarian & statistik per kelas/status
 * kini diproses di sisi server (paginasi), jadi kolom yang sering disaring diberi indeks.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->index('kelas', 'siswas_kelas_index');
            $table->index('status', 'siswas_status_index');
            $table->index('nama_lengkap', 'siswas_nama_lengkap_index');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropIndex('siswas_kelas_index');
            $table->dropIndex('siswas_status_index');
            $table->dropIndex('siswas_nama_lengkap_index');
        });
    }
};
