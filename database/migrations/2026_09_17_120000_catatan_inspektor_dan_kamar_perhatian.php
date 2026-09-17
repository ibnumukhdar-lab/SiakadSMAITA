<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aturan baru finalisasi sidak asrama (permintaan Fahri, 17 Sep 2026):
 * kamar hanya boleh ditandai TERKOTOR bila nilainya di bawah 70% dari skor maksimal.
 * Bila semua kamar >= 70%, statusnya "bersih" (tanpa poin -1) dan kamar paling bawah
 * masuk daftar "perlu diperhatikan" — dengan catatan dari inspektor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            if (! Schema::hasColumn('asrama_penilaians', 'kamar_perhatian_id')) {
                $table->unsignedBigInteger('kamar_perhatian_id')->nullable()->after('kamar_terkotor_id');
            }
            if (! Schema::hasColumn('asrama_penilaians', 'catatan_inspektor')) {
                $table->text('catatan_inspektor')->nullable()->after('kamar_perhatian_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            foreach (['kamar_perhatian_id', 'catatan_inspektor'] as $kolom) {
                if (Schema::hasColumn('asrama_penilaians', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
