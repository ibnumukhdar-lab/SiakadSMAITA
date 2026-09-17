<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 17 Sep 2026 (permintaan Fahri):
 * 1) IZIN — aturan jam: jam boleh keluar + jam wajib kembali, catatan waktu kembali
 *    yang sebenarnya (kembali_at) dan besar keterlambatan dalam menit.
 * 2) INSPEKSI KEBERSIHAN — jadi 2 sesi per hari: pagi (05.00–09.00) & sore (17.00–18.00).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- 1. Izin: jam keluar / wajib kembali + keterlambatan ----------
        Schema::table('asrama_izins', function (Blueprint $table) {
            if (! Schema::hasColumn('asrama_izins', 'jam_keluar')) {
                $table->time('jam_keluar')->nullable()->after('sampai');
            }
            if (! Schema::hasColumn('asrama_izins', 'jam_wajib_kembali')) {
                $table->time('jam_wajib_kembali')->nullable()->after('jam_keluar');
            }
            if (! Schema::hasColumn('asrama_izins', 'kembali_at')) {
                $table->dateTime('kembali_at')->nullable()->after('catatan_penolakan');
            }
            if (! Schema::hasColumn('asrama_izins', 'terlambat_menit')) {
                $table->integer('terlambat_menit')->nullable()->after('kembali_at');
            }
        });

        // ---------- 2. Inspeksi kebersihan: penanda sesi (pagi/sore) ----------
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            if (! Schema::hasColumn('asrama_penilaians', 'sesi')) {
                $table->string('sesi', 10)->nullable()->after('kategori');
                $table->index(['tanggal', 'kategori', 'sesi'], 'asrama_penilaians_tanggal_kategori_sesi');
            }
        });

        // Data lama (inspeksi harian sebelum ada sesi) ditandai sebagai sesi pagi.
        DB::table('asrama_penilaians')->whereNull('sesi')->update(['sesi' => 'pagi']);
    }

    public function down(): void
    {
        Schema::table('asrama_penilaians', function (Blueprint $table) {
            if (Schema::hasColumn('asrama_penilaians', 'sesi')) {
                $table->dropIndex('asrama_penilaians_tanggal_kategori_sesi');
                $table->dropColumn('sesi');
            }
        });

        Schema::table('asrama_izins', function (Blueprint $table) {
            foreach (['terlambat_menit', 'kembali_at', 'jam_wajib_kembali', 'jam_keluar'] as $kolom) {
                if (Schema::hasColumn('asrama_izins', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};
