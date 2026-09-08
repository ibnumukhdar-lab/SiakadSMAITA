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
    Schema::create('sr_group_members', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->uuid('group_id');
        $table->unsignedBigInteger('student_id'); // Relasi ke tabel siswas
        $table->date('tanggal_gabung');
        $table->date('tanggal_keluar')->nullable(); // Untuk histori keanggotaan
        $table->timestamps();

        $table->foreign('group_id')->references('id')->on('sr_groups')->onDelete('cascade');
        $table->foreign('student_id')->references('id')->on('siswas')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sr_group_members');
    }
};


