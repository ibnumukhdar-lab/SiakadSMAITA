<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Evaluasi BEE Smart: periode penilaian (triwulan/semester) + percobaan siswa.
 * Siswa tidak perlu login — cukup NIS/NISN, jadi setiap percobaan diberi KODE unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bee_evaluasi')) {
            Schema::create('bee_evaluasi', function (Blueprint $table) {
                $table->id();
                $table->string('judul');
                $table->enum('jenis', ['triwulan', 'semester'])->default('triwulan');
                $table->string('tahun_ajaran', 20)->nullable();
                $table->unsignedTinyInteger('semester')->nullable();   // 1 atau 2 (untuk semesteran)
                $table->date('mulai');
                $table->date('selesai');
                $table->boolean('aktif')->default(false);              // dibuka guru saat siap
                $table->unsignedSmallInteger('jumlah_soal')->default(30);
                $table->unsignedSmallInteger('durasi_menit')->default(25);
                $table->unsignedSmallInteger('kkm')->default(70);      // nilai minimal lulus
                $table->unsignedSmallInteger('poin_lulus')->default(3); // poin Student Root bila lulus
                $table->unsignedTinyInteger('maks_percobaan')->default(2);
                $table->json('modul')->nullable();                     // daftar bee_week_id; null = otomatis dari tanggal
                $table->text('catatan')->nullable();
                $table->unsignedBigInteger('dibuat_oleh')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bee_evaluasi_percobaan')) {
            Schema::create('bee_evaluasi_percobaan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bee_evaluasi_id');
                $table->unsignedBigInteger('siswa_id');
                $table->string('kode', 16)->unique();                  // kunci akses tanpa login
                $table->json('soal');                                  // soal tanpa kunci jawaban
                $table->json('kunci')->nullable();                     // kunci jawaban (TIDAK pernah dikirim ke peramban)
                $table->json('jawaban')->nullable();                   // nomor => jawaban siswa
                $table->unsignedSmallInteger('benar')->default(0);
                $table->unsignedSmallInteger('salah')->default(0);
                $table->unsignedSmallInteger('nilai')->default(0);
                $table->enum('status', ['berjalan', 'selesai'])->default('berjalan');
                $table->timestamp('mulai_pada')->nullable();
                $table->timestamp('selesai_pada')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();

                $table->index(['bee_evaluasi_id', 'siswa_id']);
                $table->index(['siswa_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bee_evaluasi_percobaan');
        Schema::dropIfExists('bee_evaluasi');
    }
};
