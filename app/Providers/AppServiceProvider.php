<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; // <-- WAJIB DITAMBAHKAN

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MENGAKTIFKAN KUNCI MASTER UNTUK SUPER ADMIN
        // Sistem akan otomatis memberi izin (true) pada SEMUA gembok jika jabatannya Super Admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}