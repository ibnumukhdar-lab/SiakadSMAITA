<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Hapus dulu tabel penilaian yang lama (urutannya harus dari anaknya dulu)
        Schema::dropIfExists('asrama_penilaian_kamars');
        Schema::dropIfExists('asrama_penilaians');

        // 2. Buat tabel penilaian versi baru (Laporan dipisah per Kategori)
        Schema::create('asrama_penilaians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('kategori', ['putra', 'putri']); // Pembeda Laporan
            $table->foreignId('musyrif_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Pemenangnya kembali jadi 2 saja (karena 1 laporan khusus putra, 1 laporan khusus putri)
            $table->foreignId('kamar_terbersih_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->foreignId('kamar_terkotor_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->string('foto_terbersih')->nullable();
            $table->string('foto_terkotor')->nullable();
            
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();
        });

        // 3. Buat ulang tabel rincian kamarnya
        Schema::create('asrama_penilaian_kamars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('asrama_penilaians')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('asrama_kamars')->cascadeOnDelete();
            $table->integer('skor_1')->default(0);
            $table->integer('skor_2')->default(0);
            $table->integer('skor_3')->default(0);
            $table->integer('skor_4')->default(0);
            $table->integer('skor_5')->default(0);
            $table->integer('total_skor')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asrama_penilaian_kamars');
        Schema::dropIfExists('asrama_penilaians');
    }
};