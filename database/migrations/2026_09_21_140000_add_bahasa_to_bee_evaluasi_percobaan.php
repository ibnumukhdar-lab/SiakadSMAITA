<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sesinya dipisah: tiap percobaan punya BAHASA sendiri (inggris / arab).
 * Satu periode evaluasi karena itu berisi DUA sesi, dan siswa memilih mau mulai dari mana.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bee_evaluasi_percobaan', 'bahasa')) {
            Schema::table('bee_evaluasi_percobaan', function (Blueprint $table) {
                $table->enum('bahasa', ['inggris', 'arab'])->default('inggris')->after('siswa_id');
                $table->index(['bee_evaluasi_id', 'siswa_id', 'bahasa']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bee_evaluasi_percobaan', 'bahasa')) {
            Schema::table('bee_evaluasi_percobaan', function (Blueprint $table) {
                $table->dropIndex(['bee_evaluasi_id', 'siswa_id', 'bahasa']);
                $table->dropColumn('bahasa');
            });
        }
    }
};
