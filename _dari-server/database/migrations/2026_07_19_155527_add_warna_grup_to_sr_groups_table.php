<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sr_groups', function (Blueprint $table) {
            // Menambahkan kolom warna_grup setelah kolom tahun_ajaran_mulai
            $table->string('warna_grup', 10)->default('#1e3a8a')->after('tahun_ajaran_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sr_groups', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn('warna_grup');
        });
    }
};