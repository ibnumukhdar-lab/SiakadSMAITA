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
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('SMA IT ARAFAH');
            $table->string('motto')->default('Cerdas & Beradab');
            $table->string('logo_path')->nullable(); // Untuk simpan file logo
            $table->string('sampul_path')->nullable(); // Untuk simpan file sampul
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
