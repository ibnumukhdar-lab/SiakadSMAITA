<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            // Cek satu-satu, kalau belum ada, baru ditambahkan
            if (!Schema::hasColumn('asrama_penilaians', 'kamar_terbersih_id')) {
                $table->unsignedBigInteger('kamar_terbersih_id')->nullable()->after('musyrif_id');
            }
            
            if (!Schema::hasColumn('asrama_penilaians', 'kamar_terkotor_id')) {
                $table->unsignedBigInteger('kamar_terkotor_id')->nullable()->after('kamar_terbersih_id');
            }
            
            if (!Schema::hasColumn('asrama_penilaians', 'foto_terbersih')) {
                $table->string('foto_terbersih')->nullable()->after('kamar_terkotor_id');
            }
            
            if (!Schema::hasColumn('asrama_penilaians', 'foto_terkotor')) {
                $table->string('foto_terkotor')->nullable()->after('foto_terbersih');
            }
        });
    }

    /**
     * Batalkan migration (Rollback).
     */
    public function down(): void
    {
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            if (Schema::hasColumn('asrama_penilaians', 'kamar_terbersih_id')) {
                $table->dropColumn('kamar_terbersih_id');
            }
            if (Schema::hasColumn('asrama_penilaians', 'kamar_terkotor_id')) {
                $table->dropColumn('kamar_terkotor_id');
            }
            if (Schema::hasColumn('asrama_penilaians', 'foto_terbersih')) {
                $table->dropColumn('foto_terbersih');
            }
            if (Schema::hasColumn('asrama_penilaians', 'foto_terkotor')) {
                $table->dropColumn('foto_terkotor');
            }
        });
    }
};