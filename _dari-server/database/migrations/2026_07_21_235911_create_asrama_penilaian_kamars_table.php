<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('asrama_penilaian_kamars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('asrama_penilaians')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('asrama_kamars')->cascadeOnDelete();
            
            // 5 Kriteria Penilaian (Skala Likert 1-5)
            $table->integer('skor_1')->default(0); // Kerapian Tempat Tidur
            $table->integer('skor_2')->default(0); // Kebersihan Lantai
            $table->integer('skor_3')->default(0); // Kerapian Lemari
            $table->integer('skor_4')->default(0); // Barang Pribadi & Sepatu
            $table->integer('skor_5')->default(0); // Sirkulasi Udara & Aroma
            
            $table->integer('total_skor')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_penilaian_kamars');
    }
};
