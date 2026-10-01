<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buku Saku Digital - BEE Smart</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Latin (Nunito) -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="pb-10 min-h-screen flex flex-col">

    <!-- ================= HEADER MOBILE ================= -->
    <div class="bg-gradient-to-b from-amber-400 to-amber-500 text-white rounded-b-[40px] px-6 pt-12 pb-16 shadow-lg relative overflow-hidden shrink-0">
        <!-- Ikon Hiasan Background -->
        <div class="absolute top-0 right-0 opacity-20 text-9xl transform translate-x-4 -translate-y-4">🐝</div>
        
        <div class="relative z-10">
            <h1 class="text-3xl font-black mb-1 drop-shadow-md">Buku Saku Digital</h1>
            <p class="text-amber-100 font-bold text-sm tracking-widest uppercase">BEE Smart Learning</p>
        </div>
    </div>

    <!-- ================= KONTEN UTAMA ================= -->
    <div class="px-5 -mt-8 relative z-20 flex-1">
        
        <!-- Kartu Sambutan -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
            <p class="text-slate-600 font-bold text-sm leading-relaxed">
                👋 Halo! Pilih modul di bawah ini untuk mulai menghafal kosakata, melatih pelafalan, dan mengumpulkan Poin Karakter hari ini.
            </p>
        </div>

        <!-- Daftar Modul Tersedia -->
        <div class="flex flex-col gap-4">
            @forelse($weeks as $week)
                <a href="{{ route('bee.buku-saku.show', $week->id) }}" class="block bg-white rounded-2xl p-5 shadow-sm border-2 border-transparent hover:border-amber-400 transition transform hover:-translate-y-1 active:scale-95">
                    
                    <div class="flex justify-between items-center mb-2">
                        <!-- Label Status -->
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $week->status == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $week->status == 'aktif' ? 'Tayang Minggu Ini' : 'Arsip' }}
                        </span>
                        
                        <!-- Jumlah Kosakata -->
                        <span class="text-xs font-bold text-slate-400">{{ $week->vocabs_count }} Kata</span>
                    </div>
                    
                    <h2 class="text-xl font-black text-slate-800">{{ $week->judul }}</h2>
                    
                    <div class="mt-4 flex items-center text-amber-500 font-bold text-sm">
                        Mulai Belajar <span class="ml-2">➔</span>
                    </div>
                </a>
            @empty
                <!-- Jika belum ada modul yang diterbitkan -->
                <div class="text-center p-10 text-slate-400 font-bold bg-white rounded-2xl border border-slate-100 shadow-sm mt-4">
                    <div class="text-5xl mb-3">📭</div>
                    Belum ada modul yang diterbitkan oleh Guru.
                </div>
            @endforelse
        </div>
        
    </div>

    <!-- Footer Simple -->
    <div class="w-full text-center py-6 mt-auto">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sistem Display Terpadu &copy; {{ date('Y') }}</p>
    </div>

</body>
</html>