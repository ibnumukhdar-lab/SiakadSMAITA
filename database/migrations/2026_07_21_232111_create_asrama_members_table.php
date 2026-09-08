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
        Schema::create('asrama_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('asrama_kamars')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('siswas')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable(); // Jika berisi tanggal, berarti siswa sudah pindah/lulus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_members');
    }
};
