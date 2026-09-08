<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Panggil Data Identitas Lembaga -->
        @php
            $pengaturan = \App\Models\Pengaturan::first();
        @endphp

        <!-- 1. Judul Dinamis Tab Browser -->
        <title>{{ $pengaturan->nama_sekolah ?? config('app.name', 'Laravel') }}</title>

        <!-- 2. Favicon (Ikon Kecil di Tab Browser) Dinamis -->
        @if($pengaturan && $pengaturan->logo_path)
            <link rel="icon" href="{{ asset('storage/' . $pengaturan->logo_path) }}">
        @else
            <!-- Ikon bawaan jika logo belum diupload -->
            <link rel="icon" href="https://laravel.com/img/favicon/favicon-32x32.png">
        @endif

        <!-- ================= PWA META TAGS ================= -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#1e3a8a">
        <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- ================= PWA SERVICE WORKER ================= -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('PWA ServiceWorker berhasil didaftarkan dengan scope: ', registration.scope);
                        })
                        .catch((err) => {
                            console.log('PWA ServiceWorker gagal didaftarkan: ', err);
                        });
                });
            }
        </script>
        <!-- ================= PWA CUSTOM INSTALL BANNER ================= -->
        <script>
            let deferredPrompt;

            window.addEventListener('beforeinstallprompt', (e) => {
                // Mencegah Chrome memunculkan pop-up default-nya sendiri
                e.preventDefault();
                // Simpan event-nya agar bisa dipicu nanti
                deferredPrompt = e;
                
                // Tampilkan banner buatan kita sendiri
                showInstallPromotion();
            });

            function showInstallPromotion() {
                // Pastikan banner belum ada agar tidak ganda
                if (document.getElementById('pwa-install-banner')) return;

                // Buat elemen banner
                const banner = document.createElement('div');
                banner.id = 'pwa-install-banner';
                banner.innerHTML = `
                    <div style="position: fixed; bottom: 25px; left: 50%; transform: translateX(-50%); background: #1e3a8a; color: white; padding: 15px 20px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 15px; z-index: 99999; width: 90%; max-width: 400px; justify-content: space-between; border: 1px solid #3b82f6;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: white; padding: 8px; border-radius: 10px;">
                                <img src="/logo.png" alt="Logo" style="width: 24px; height: 24px;">
                            </div>
                            <div style="line-height: 1.3;">
                                <p style="margin: 0; font-weight: 900; font-size: 14px; font-family: sans-serif;">Instal SIAKAD</p>
                                <p style="margin: 0; font-size: 11px; color: #bfdbfe; font-family: sans-serif;">Akses asrama lebih cepat!</p>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button id="pwa-install-btn" style="background: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 900; font-size: 12px; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">INSTAL</button>
                            <button id="pwa-close-btn" style="background: transparent; color: #93c5fd; border: none; font-size: 20px; cursor: pointer; padding: 0; font-weight: bold;">&times;</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(banner);

                // Jika tombol "INSTAL" ditekan
                document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                    // Sembunyikan banner kita
                    banner.style.display = 'none';
                    // Munculkan Pop-up Instalasi Bawaan Sistem (Android/Chrome)
                    deferredPrompt.prompt();
                    // Tunggu respon pengguna (apakah menekan "Install" atau "Cancel" di pop-up Android)
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('Respon pengguna:', outcome);
                    // Reset prompt
                    deferredPrompt = null;
                });

                // Jika tombol "X" ditekan
                document.getElementById('pwa-close-btn').addEventListener('click', () => {
                    banner.style.display = 'none';
                });
            }

            // Jika aplikasi sudah berhasil diinstal, hilangkan banner selama-lamanya
            window.addEventListener('appinstalled', () => {
                const banner = document.getElementById('pwa-install-banner');
                if(banner) banner.style.display = 'none';
                deferredPrompt = null;
                console.log('SIAKAD berhasil diinstal sebagai PWA!');
            });
        </script>
    </body>
</html>