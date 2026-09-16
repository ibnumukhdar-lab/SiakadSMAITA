<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tahun ajaran (satu yang AKTIF pada satu waktu) + wali kelas pada daftar kelas.
 *
 * Catatan desain: kolom `siswas.tahun_ajaran` dan `siswas.kelas` tetap berupa teks,
 * jadi data 130 siswa dan modul yang sudah jalan (Asrama, Student Root) tidak tersentuh.
 * Tahun ajaran aktif dipakai sebagai nilai bawaan saat menyimpan siswa, saat kenaikan
 * kelas, dan sebagai penanda "tahun berjalan" di aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tahun_ajaran')) {
            Schema::create('tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 20)->unique();   // contoh: 2026/2027
                $table->date('awal')->nullable();
                $table->date('akhir')->nullable();
                $table->boolean('aktif')->default(false);
                $table->string('keterangan', 150)->nullable();
                $table->timestamps();
            });
        }

        // Tahun ajaran yang sedang dipakai aplikasi (dari data siswa yang sudah ada).
        if (DB::table('tahun_ajaran')->count() === 0) {
            $terpakai = DB::table('siswas')
                ->whereNotNull('tahun_ajaran')
                ->where('tahun_ajaran', '!=', '')
                ->orderByDesc('id')
                ->value('tahun_ajaran');

            $nama = $terpakai ?: date('Y') . '/' . (date('Y') + 1);

            DB::table('tahun_ajaran')->insert([
                'nama' => $nama,
                'awal' => null,
                'akhir' => null,
                'aktif' => true,
                'keterangan' => 'Dibuat otomatis saat pemasangan modul tahun ajaran.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('kelas', function (Blueprint $table) {
            if (! Schema::hasColumn('kelas', 'wali_kelas_id')) {
                $table->unsignedBigInteger('wali_kelas_id')->nullable()->index()->after('tingkat');
            }
            if (! Schema::hasColumn('kelas', 'wali_kelas_nama')) {
                $table->string('wali_kelas_nama', 100)->nullable()->after('wali_kelas_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'wali_kelas_id')) {
                $table->dropColumn('wali_kelas_id');
            }
            if (Schema::hasColumn('kelas', 'wali_kelas_nama')) {
                $table->dropColumn('wali_kelas_nama');
            }
        });

        Schema::dropIfExists('tahun_ajaran');
    }
};
