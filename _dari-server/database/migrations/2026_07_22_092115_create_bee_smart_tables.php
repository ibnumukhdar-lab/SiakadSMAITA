<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Master (Mingguan)
        Schema::create('bee_weeks', function (Blueprint $table) {
            $table->id();
            $table->string('judul'); // Contoh: "Minggu 1: Lingkungan Sekolah"
            $table->date('tanggal_mulai')->nullable();
            $table->enum('status', ['draft', 'aktif', 'arsip'])->default('draft');
            $table->timestamps();
        });

        // 2. Tabel Rincian Kosakata (Tabel Interaktif)
        Schema::create('bee_vocabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bee_week_id')->constrained('bee_weeks')->cascadeOnDelete();
            
            // Kolom Bahasa Indonesia
            $table->string('kosakata_id'); // Kolom: Kosakata
            
            // Kolom Bahasa Inggris
            $table->string('vocab_en')->nullable();    // Kolom: Vocab
            $table->text('sentence_en')->nullable();   // Kolom: Sentence
            
            // Kolom Bahasa Arab
            $table->string('mufrodat_ar')->nullable(); // Kolom: Mufrodat
            $table->text('jumlah_ar')->nullable();     // Kolom: Al Jumlah
            
            // Kolom Audio (Path file)
            $table->string('audio_vocab_en')->nullable();
            $table->string('audio_sentence_en')->nullable();
            $table->string('audio_mufrodat_ar')->nullable();
            $table->string('audio_jumlah_ar')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bee_vocabs');
        Schema::dropIfExists('bee_weeks');
    }
};