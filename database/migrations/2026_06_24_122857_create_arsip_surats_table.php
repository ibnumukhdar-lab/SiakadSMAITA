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
        Schema::create('arsip_surats', function (Blueprint $table) {
            $table->id();
            // Kategori: Surat Masuk / Surat Keluar
            $table->enum('jenis_surat', ['Surat Masuk', 'Surat Keluar']);
            // Nomor Surat
            $table->string('nomor_surat');
            // Tanggal Surat
            $table->date('tanggal_surat');
            // Instansi Tujuan / Pengirim
            $table->string('pihak_terkait');
            // Perihal
            $table->text('perihal');
            // Path file PDF/Word yang diupload (boleh kosong)
            $table->string('file_surat')->nullable();
            // Link Google Drive jika tidak upload file (boleh kosong)
            $table->string('link_drive')->nullable();
            
            $table->timestamps(); // Otomatis mencatat waktu dibuat (created_at) & diupdate (updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_surats');
    }
};
