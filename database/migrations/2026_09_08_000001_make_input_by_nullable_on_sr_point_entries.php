<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 2026-09: entri poin hasil KLAIM MANDIRI siswa (Buku Saku Bee Smart)
     * tidak punya guru pencatat, sehingga input_by harus boleh NULL.
     * (Sebelumnya klaim Bee memakai kolom yang tidak ada dan selalu gagal.)
     */
    public function up(): void
    {
        Schema::table('sr_point_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('input_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sr_point_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('input_by')->nullable(false)->change();
        });
    }
};
