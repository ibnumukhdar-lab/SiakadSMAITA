@php
    $pengaturan = \App\Models\Pengaturan::first();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pengaturan->nama_sekolah ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <style>
                body { font-family: 'Instrument Sans', sans-serif; }
            </style>
        @endif
    </head>
    <body class="bg-slate-50 dark:bg-[#0a0a0a] text-[#1b1b18] flex p-4 sm:p-6 lg:p-8 items-center justify-center min-h-screen flex-col">
        
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750">
            
            <main class="w-full max-w-[640px] bg-white dark:bg-[#161615] shadow-2xl shadow-slate-200/80 dark:shadow-none rounded-[24px] border border-slate-100 dark:border-slate-800 p-3 sm:p-4">
                
                <div class="relative bg-slate-100 dark:bg-slate-900 rounded-[16px] overflow-hidden w-full" style="aspect-ratio: 3 / 1;">
                    @if($pengaturan && $pengaturan->sampul_path)
                        <!-- Jika sampul diupload dari database pengaturan, arahkan ke jalur /berkas/ -->
                        <img src="{{ url('berkas/' . $pengaturan->sampul_path) }}" alt="Sampul Depan" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.95;">
                    @else
                        <!-- Fallback ke gambar default jika belum diatur -->
                        <img src="{{ asset('img/sampul.jpg') }}?v=3" alt="Sampul Depan" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.95;">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                </div>
                <br>
                <br>

                <div class="px-6 pb-8 pt-0 flex flex-col justify-center items-center text-center relative">
                    
                    @if($pengaturan && $pengaturan->logo_path)
                        <div class="-mt-12 mb-4 relative z-10 bg-white dark:bg-[#161615] p-2.5 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800">
                            <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                            <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="w-16 h-16 sm:w-20 sm:h-20 object-contain" alt="Logo Sekolah">
                        </div>
                    @endif

                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-[#EDEDEC] mb-2 mt-2">
                        {{ $pengaturan->nama_sekolah ?? 'Nama Sekolah' }}
                    </h1>
                    
                    <p class="text-[14px] leading-relaxed text-slate-500 dark:text-[#A1A09A] mb-8 max-w-[400px]">
                        {{ $pengaturan->motto ?? 'Motto atau slogan sekolah Anda berada di sini. Sistem Informasi Terpadu.' }}
                    </p>

                    <div class="w-full max-w-[340px] mt-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block w-full text-center px-6 py-4 bg-[#1b1b18] hover:bg-black dark:bg-[#EDEDEC] dark:hover:bg-white text-white dark:text-[#1b1b18] font-semibold rounded-xl shadow-lg shadow-black/10 transition-all text-base tracking-wide active:scale-[0.98]">
                                Masuk Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="block w-full text-center px-6 py-4 bg-[#1b1b18] hover:bg-black dark:bg-[#EDEDEC] dark:hover:bg-white text-white dark:text-[#1b1b18] font-semibold rounded-xl shadow-lg shadow-black/10 transition-all text-base tracking-wide active:scale-[0.98]">
                                Log In Admin
                            </a>
                        @endauth
                    </div>
                </div>

            </main>

        </div>
        
    </body>
</html>