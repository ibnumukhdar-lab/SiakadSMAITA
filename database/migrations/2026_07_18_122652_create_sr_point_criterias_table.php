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
    Schema::create('sr_point_criteria', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->enum('kategori', ['positif', 'negatif']);
        $table->string('nama_perilaku');
        $table->text('deskripsi')->nullable();
        $table->integer('poin'); // Contoh: 5 atau -2
        $table->enum('tingkat', ['ringan', 'sedang', 'berat'])->nullable();
        $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sr_point_criterias');
    }
};
