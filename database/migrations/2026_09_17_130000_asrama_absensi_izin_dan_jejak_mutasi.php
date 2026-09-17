<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TAHAP A–C PENGEMBANGAN ASRAMA (17 Sep 2026)
 * A: kolom catatan + dicatat_oleh di asrama_members (mutasi penghuni berjejak)
 * B: tabel asrama_absensi (absensi per sesi) + asrama_izins (izin pulang/keluar)
 * C: tidak butuh tabel baru (analitik memakai data yang sudah ada)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- A. Jejak mutasi penghuni kamar ----------
        Schema::table('asrama_members', function (Blueprint $table) {
            if (! Schema::hasColumn('asrama_members', 'catatan')) {
                $table->string('catatan', 255)->nullable()->after('tanggal_keluar');
            }
            if (! Schema::hasColumn('asrama_members', 'dicatat_oleh')) {
                $table->unsignedBigInteger('dicatat_oleh')->nullable()->after('catatan');
            }
        });

        // ---------- B1. Absensi asrama per sesi ----------
        if (! Schema::hasTable('asrama_absensi')) {
            Schema::create('asrama_absensi', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('sesi', 20)->default('tidur');        // subuh|pagi|maghrib|isya|tidur
                $table->foreignId('kamar_id')->constrained('asrama_kamars')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('siswas')->cascadeOnDelete();
                $table->string('status', 20)->default('hadir');      // hadir|telat|izin|sakit|pulang|alpa
                $table->string('keterangan', 255)->nullable();
                $table->unsignedBigInteger('dicatat_oleh')->nullable();
                $table->timestamps();

                $table->unique(['tanggal', 'sesi', 'student_id'], 'asrama_absensi_unik');
                $table->index(['tanggal', 'sesi', 'kamar_id'], 'asrama_absensi_tanggal_sesi');
            });
        }

        // ---------- B2. Izin pulang / keluar santri ----------
        if (! Schema::hasTable('asrama_izins')) {
            Schema::create('asrama_izins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('siswas')->cascadeOnDelete();
                $table->unsignedBigInteger('kamar_id')->nullable();
                $table->string('jenis', 20)->default('pulang');       // pulang|keluar|sakit|lainnya
                $table->date('mulai');
                $table->date('sampai');
                $table->text('alasan')->nullable();
                $table->string('tujuan', 150)->nullable();
                $table->string('penanggung_jawab', 150)->nullable();
                $table->string('status', 20)->default('diajukan');    // diajukan|disetujui|ditolak|selesai
                $table->unsignedBigInteger('diajukan_oleh')->nullable();
                $table->unsignedBigInteger('disetujui_oleh')->nullable();
                $table->text('catatan_penolakan')->nullable();
                $table->date('kembali_pada')->nullable();
                $table->text('catatan_kembali')->nullable();
                $table->timestamps();

                $table->index(['status', 'mulai', 'sampai'], 'asrama_izins_status_tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asrama_izins');
        Schema::dropIfExists('asrama_absensi');

        Schema::table('asrama_members', function (Blueprint $table) {
            if (Schema::hasColumn('asrama_members', 'dicatat_oleh')) {
                $table->dropColumn('dicatat_oleh');
            }
            if (Schema::hasColumn('asrama_members', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }
};
