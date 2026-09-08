<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 2026-09: target jumlah kosakata per modul BEE Smart dibuat KUSTOM (bukan patokan 10).
     * batas_min & batas_maks bisa diubah per modul 1-500 (default 5-20 saat modul baru).
     * Bersifat informasi (tidak memblokir penyimpanan).
     */
    public function up(): void
    {
        Schema::table('bee_weeks', function (Blueprint $table) {
            $table->unsignedInteger('batas_min')->default(5)->after('status');
            $table->unsignedInteger('batas_maks')->default(20)->after('batas_min');
        });
    }

    public function down(): void
    {
        Schema::table('bee_weeks', function (Blueprint $table) {
            $table->dropColumn(['batas_min', 'batas_maks']);
        });
    }
};
