<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sr_display_settings', function (Blueprint $table) {
            $table->id();
            $table->string('judul_utama')->default('STUDENT ROOT AGREGATOR');
            $table->string('logo')->nullable(); // Path penyimpanan logo
            $table->string('background_image')->nullable(); // Path penyimpanan background
            $table->integer('durasi_slide')->default(60); // Durasi per slide dalam detik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_display_settings');
    }
};