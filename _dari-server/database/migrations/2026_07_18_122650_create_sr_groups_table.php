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
    Schema::create('sr_groups', function (Blueprint $table) {
        $table->uuid('id')->primary(); // Sesuai spesifikasi UUID
        $table->string('nama_grup');
        $table->unsignedBigInteger('mentor_id'); // Relasi ke tabel users (Guru)
        $table->string('tahun_ajaran_mulai');
        $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        $table->timestamps();

        // Asumsi tabel guru/user Anda bernama 'users'
        $table->foreign('mentor_id')->references('id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sr_groups');
    }
};
