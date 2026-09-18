<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTE BERKAS PUBLIK (/berkas/{path})
|--------------------------------------------------------------------------
| PENTING (perbaikan 18 Sep 2026): rute ini dimuat di bootstrap/app.php
| lewat ->withRouting(..., then: ...) sehingga TIDAK memakai grup middleware
| "web" (tanpa StartSession / EncryptCookies / CSRF).
|
| Alasan: sebelumnya rute ini ikut grup "web", sehingga SETIAP permintaan
| gambar/audio/berkas (termasuk dari klien yang tidak mengirim cookie —
| perangkat TV display, iframe lintas-situs, crawler, pratinjau tautan)
| membuat SESI BARU dan mengirim Set-Cookie "laravel-session" baru.
| Akibat nyata: (1) ribuan berkas sesi menumpuk tiap hari (±1.400/6 jam) dan
| terus dibersihkan GC, (2) bila permintaan tanpa cookie itu dikirim oleh
| peramban yang mengizinkan cookie pihak-ketiga, cookie sesi pengguna yang
| SEDANG LOGIN tertimpa sesi kosong -> "berhasil login tapi dilempar balik ke
| halaman login" + "419 Page Expired" pada form yang sedang terbuka.
|
| Menyajikan berkas tidak butuh sesi, jadi rute ini sengaja bebas sesi.
| JANGAN memindahkannya ke routes/web.php (akan membawa middleware "web").
*/

Route::get('/berkas/{path}', function (string $path) {
    if ($path === '' || str_contains($path, '..')) {
        abort(404);
    }

    $base = realpath(storage_path('app/public'));
    $full = realpath($base . DIRECTORY_SEPARATOR . $path);

    if ($base === false || $full === false) {
        abort(404);
    }
    if ($full !== $base && ! str_starts_with($full, $base . DIRECTORY_SEPARATOR)) {
        abort(404);
    }
    if (! is_file($full)) {
        abort(404);
    }

    return response()->file($full);
})->where('path', '.*');
