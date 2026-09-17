<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PORTOFOLIO PROJECT STUDENT ROOT.
 *
 * Setelah project menuntaskan seluruh tahap yang dipakai, mentor menyusun portofolio:
 * ringkasan narasi tiap tahap + unggahan foto/dokumentasi, lalu bisa dicetak.
 *
 * Tabel baru saja — `sr_projects`, `sr_project_nilai`, `sr_project_tahap_status` tidak diubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Narasi portofolio (satu per project)
        if (! Schema::hasTable('sr_project_portofolio')) {
            Schema::create('sr_project_portofolio', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->unique();
                $table->text('ringkasan')->nullable();     // gambaran umum project
                $table->text('latar_belakang')->nullable(); // latar belakang / observasi
                $table->text('tujuan')->nullable();
                $table->text('pelaksanaan')->nullable();    // perencanaan & perancangan & validasi
                $table->text('hasil')->nullable();          // hasil / karya
                $table->text('refleksi')->nullable();       // refleksi & tindak lanjut
                $table->string('tempat', 100)->nullable();  // tempat presentasi publik
                $table->date('tanggal_presentasi')->nullable();
                $table->unsignedBigInteger('disusun_oleh')->nullable();
                $table->timestamp('diselesaikan_pada')->nullable();
                $table->timestamps();
            });
        }

        // Foto / dokumentasi project (banyak per project)
        if (! Schema::hasTable('sr_project_dokumen')) {
            Schema::create('sr_project_dokumen', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('project_id')->index();
                $table->string('path', 255);                 // relatif storage/app/public
                $table->string('nama_asli', 255)->nullable();
                $table->string('keterangan', 255)->nullable();
                $table->unsignedInteger('urutan')->default(0);
                $table->unsignedBigInteger('diunggah_oleh')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_project_dokumen');
        Schema::dropIfExists('sr_project_portofolio');
    }
};
