<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan rapor per santri (18 Sep 2026, permintaan Fahri).
 *
 * Sebelum ini bagian "Catatan Musyrif / Pembina" (rapor Adab & Keasramaan) dan
 * "Catatan Mentor" (rapor Student Root) hanya berupa garis kosong untuk ditulis tangan.
 * Sekarang bisa diisi lewat aplikasi dan ikut tercetak pada periode yang sesuai.
 *
 * Kunci periode:
 *  - jenis = adab         → penilaian_periode_id (satu periode penilaian, mis. "Semester 1 2026/2027")
 *  - jenis = student_root → tahun_ajaran + semester (s1 = Juli–Des, s2 = Jan–Jun), sama dengan rapor SR
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('catatan_rapot')) {
            return;
        }

        Schema::create('catatan_rapot', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['adab', 'student_root']);
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('penilaian_periode_id')->nullable();
            $table->string('tahun_ajaran', 12)->nullable();
            $table->string('semester', 4)->nullable();
            $table->text('isi');
            $table->unsignedBigInteger('penulis_id')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'jenis'], 'catatan_rapot_siswa_jenis_idx');
            $table->index('penilaian_periode_id', 'catatan_rapot_periode_idx');
            $table->index(['tahun_ajaran', 'semester'], 'catatan_rapot_semester_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_rapot');
    }
};
