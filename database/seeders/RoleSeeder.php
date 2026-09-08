<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Mencetak 4 Jabatan Utama Sekolah
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Kepala Sekolah']);
        Role::create(['name' => 'Tata Usaha']);
        Role::create(['name' => 'Guru']);

        // 2. Memberikan hak istimewa "Super Admin" ke akun Anda
        // Pastikan angka 1 di bawah ini adalah ID akun Anda di tabel users
        $superadmin = User::find(1); 
        if ($superadmin) {
            $superadmin->assignRole('Super Admin');
        }
    }
}