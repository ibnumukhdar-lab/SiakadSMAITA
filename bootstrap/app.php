<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Rute berkas publik (/berkas/{path}) dimuat TANPA grup middleware "web".
            // Alasan (perbaikan 18 Sep 2026): permintaan gambar/audio/berkas sering
            // datang tanpa cookie (perangkat TV display, iframe lintas-situs, crawler).
            // Bila rute ini ikut grup "web", tiap permintaan seperti itu membuat SESI
            // BARU + Set-Cookie "laravel-session" baru; bila dikirim oleh peramban yang
            // mengizinkan cookie pihak-ketiga, cookie sesi pengguna yang sedang login
            // bisa tertimpa sesi kosong => "login berhasil tapi dilempar balik ke
            // halaman login" dan "419 Page Expired". Jangan pindahkan ke routes/web.php.
            require base_path('routes/berkas.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
