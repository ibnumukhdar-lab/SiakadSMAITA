<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TEMPAT LAHIR & TANGGAL LAHIR DIPISAH (30 Sep 2026)
 * =====================================================================
 * Permintaan Kepala Sekolah: kolom `siswas.ttl` berisi teks bebas
 * ("Pematang Siantar, 8 Maret 2011", "Kotawaringin Timur 2009-11-23 00:00:00",
 * bahkan kosong). Akibatnya kata sandi portal orang tua harus menebak-nebak
 * dari teks, dan 24 siswa tidak bisa masuk.
 *
 * Sekarang:
 *   - tempat_lahir  : teks, diisi lewat kolom biasa
 *   - tanggal_lahir : tipe DATE (pemilih kalender), sumber tunggal kata sandi
 *
 * Kolom lama `ttl` SENGAJA dibiarkan utuh: (a) masih dipakai cetak lembar induk
 * & ekspor, (b) jalan pulang bila isian baru salah. Isian baru otomatis
 * merapikan `ttl` agar tampilan lama tetap terbaca.
 *
 * Aman diulang; tidak menghapus apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (! Schema::hasColumn('siswas', 'tempat_lahir')) {
                $table->string('tempat_lahir', 100)->nullable()->after('ttl');
            }
            if (! Schema::hasColumn('siswas', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'tanggal_lahir')) {
                $table->dropColumn('tanggal_lahir');
            }
            if (Schema::hasColumn('siswas', 'tempat_lahir')) {
                $table->dropColumn('tempat_lahir');
            }
        });
    }
};
