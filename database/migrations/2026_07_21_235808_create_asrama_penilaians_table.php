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
        Schema::create('asrama_penilaians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('musyrif_id')->constrained('users')->cascadeOnDelete();
            
            // 4 Kandidat Pemenang & Pelanggar
            $table->foreignId('terbersih_putra_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->foreignId('terkotor_putra_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->foreignId('terbersih_putri_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            $table->foreignId('terkotor_putri_id')->nullable()->constrained('asrama_kamars')->nullOnDelete();
            
            // 4 Slot Bukti Foto
            $table->string('foto_terbersih_putra')->nullable();
            $table->string('foto_terkotor_putra')->nullable();
            $table->string('foto_terbersih_putri')->nullable();
            $table->string('foto_terkotor_putri')->nullable();
            
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_penilaians');
    }
};
