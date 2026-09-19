<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * SESI RAPOR ADAB & KEASRAMaan (19 Sep 2026) — penyederhanaan alur.
 *
 * Sebelumnya setiap musyrif membuat sendiri "lembar penilaian"-nya (dua kali: Adab dan
 * Keasramaan) dan harus menekan Finalkan per lembar. Sekarang sesi dibuka/ditutup
 * SEKALI oleh TU / Super Admin / Kepala Sekolah / Kepala Diniyah lewat izin
 * `kelola-sesi-rapor`; selama sesi terbuka musyrif/musyrifah mengisi, setelah ditutup
 * pengisian berhenti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian_periode', function (Blueprint $table) {
            $table->boolean('terbuka')->default(false)->after('aktif');
            $table->unsignedBigInteger('dibuka_oleh')->nullable()->after('terbuka');
            $table->timestamp('dibuka_pada')->nullable()->after('dibuka_oleh');
            $table->unsignedBigInteger('ditutup_oleh')->nullable()->after('dibuka_pada');
            $table->timestamp('ditutup_pada')->nullable()->after('ditutup_oleh');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['kelola-sesi-rapor'] as $nama) {
            Permission::firstOrCreate(['name' => $nama, 'guard_name' => 'web']);
        }

        $peta = [
            'Tata Usaha' => ['kelola-sesi-rapor'],
            'Kepala Sekolah' => ['kelola-sesi-rapor'],
            'Kepala Diniyah' => ['kelola-sesi-rapor'],
        ];

        foreach ($peta as $namaRole => $daftarIzin) {
            $role = Role::where('name', $namaRole)->first();
            if ($role) {
                $role->givePermissionTo($daftarIzin);
            }
        }

        // Periode yang sedang aktif dibiarkan TERBUKA supaya pengisian yang sudah berjalan
        // tidak mendadak berhenti karena deploy. Pengelola bisa menutup/membukanya di
        // halaman "Sesi & Progres".
        DB::table('penilaian_periode')->where('aktif', true)->update(['terbuka' => true]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('penilaian_periode', function (Blueprint $table) {
            $table->dropColumn(['terbuka', 'dibuka_oleh', 'dibuka_pada', 'ditutup_oleh', 'ditutup_pada']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'kelola-sesi-rapor')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
