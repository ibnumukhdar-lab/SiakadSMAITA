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
        Schema::create('asrama_kamars', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kamar');
            $table->enum('kategori', ['putra', 'putri'])->default('putra'); // <-- TAMBAHAN BARU
            $table->foreignId('musyrif_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('kapasitas')->default(4);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asrama_kamars');
    }
};
