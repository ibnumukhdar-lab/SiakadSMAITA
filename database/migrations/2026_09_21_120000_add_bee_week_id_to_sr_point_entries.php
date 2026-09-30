<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menautkan entri poin ke modul BEE Smart-nya.
 *
 * Sebelumnya klaim hanya dikenali dari TEKS catatan ("Menyelesaikan Kuis Bee Smart: <judul>"),
 * sehingga laporan per modul rapuh. Kolom ini membuat laporan tegas dan tahan penggantian judul.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sr_point_entries', 'bee_week_id')) {
            return;
        }

        Schema::table('sr_point_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('bee_week_id')->nullable()->after('criteria_id');
            $table->index('bee_week_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sr_point_entries', 'bee_week_id')) {
            return;
        }

        Schema::table('sr_point_entries', function (Blueprint $table) {
            $table->dropIndex(['bee_week_id']);
            $table->dropColumn('bee_week_id');
        });
    }
};
