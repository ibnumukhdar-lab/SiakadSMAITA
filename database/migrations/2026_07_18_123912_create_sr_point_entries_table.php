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
    Schema::create('sr_point_entries', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->unsignedBigInteger('student_id');
        $table->uuid('group_id')->nullable(); // Snapshot grup saat siswa dapat poin
        $table->uuid('criteria_id');
        $table->integer('poin'); // SNAPSHOT: Disalin dari kriteria agar tidak berubah retroaktif
        $table->text('catatan')->nullable();
        $table->unsignedBigInteger('input_by'); // Siapa yang memberi poin
        $table->date('tanggal_kejadian');
        $table->timestamps();
        $table->softDeletes(); // Sesuai instruksi audit trail: tidak dihapus permanen

        $table->foreign('student_id')->references('id')->on('siswas')->onDelete('cascade');
        $table->foreign('group_id')->references('id')->on('sr_groups')->onDelete('set null');
        $table->foreign('criteria_id')->references('id')->on('sr_point_criteria')->onDelete('cascade');
        $table->foreign('input_by')->references('id')->on('users')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sr_point_entries');
    }
};
