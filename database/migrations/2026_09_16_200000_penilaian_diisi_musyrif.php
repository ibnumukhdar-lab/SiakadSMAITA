<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Penilaian Adab & Keasramaan diisi oleh MUSYRIF/MUSYRIFAH saja.
 *
 * Izin mengisi (`nilai-adab`, `nilai-keasramaan`) dicabut dari peran Kepala Diniyah.
 * Kepala Diniyah tetap bisa MELIHAT lewat izin `buka-menu-penilaian` (rekap & rincian),
 * jadi kalau nanti beliau perlu ikut menilai lagi, cukup tambahkan izinnya di Kelola Akun.
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where('name', 'Kepala Diniyah')->first();

        if ($role) {
            foreach (['nilai-adab', 'nilai-keasramaan'] as $namaIzin) {
                $permission = Permission::where('name', $namaIzin)->first();
                if ($permission && $role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);
                }
            }
        }

        // Pastikan musyrif tetap punya izin mengisi.
        $musyrif = Role::where('name', 'Musyrif')->first();
        if ($musyrif) {
            foreach (['buka-menu-penilaian', 'nilai-adab', 'nilai-keasramaan'] as $namaIzin) {
                $permission = Permission::firstOrCreate(['name' => $namaIzin]);
                if (! $musyrif->hasPermissionTo($permission)) {
                    $musyrif->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::where('name', 'Kepala Diniyah')->first();
        if ($role) {
            foreach (['buka-menu-penilaian', 'nilai-adab', 'nilai-keasramaan'] as $namaIzin) {
                $permission = Permission::where('name', $namaIzin)->first();
                if ($permission && ! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
