<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            
            // 1. Identitas Pribadi & Akademik
            $table->string('foto')->nullable();
            $table->string('nama_lengkap');
            $table->string('nis')->nullable();
            $table->string('nisn')->unique();
            $table->string('ttl')->nullable();
            $table->string('jk')->nullable(); // Laki-laki / Perempuan
            $table->string('status')->default('Aktif');
            $table->string('kelas')->nullable(); // X, XI, XII, Lulus
            $table->string('thn_masuk')->nullable();
            $table->string('thn_lulus')->nullable();
            $table->string('tahun_ajaran')->nullable(); // Tambahan fitur: Tahun Ajaran Berlangsung

            // 2. Data Keluarga & Wali
            $table->string('nama_ayah')->nullable();
            $table->string('status_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            
            $table->string('nama_ibu')->nullable();
            $table->string('status_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            
            $table->string('nama_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('hp_wali')->nullable();

            // 3. Domisili & Standar Dapodik
            $table->string('hp_ortu')->nullable();
            $table->string('tinggal_bersama')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jarak')->nullable();
            $table->string('transportasi')->nullable();
            $table->string('kesejahteraan')->nullable(); // No KIP/PKH
            $table->string('asal_sekolah')->nullable();
            $table->string('penyakit')->nullable();

            // 4. Logbook (Menggunakan JSON agar identik dengan cara kerja post_meta array di WordPress)
            $table->json('prestasi')->nullable();
            $table->json('pelanggaran')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};