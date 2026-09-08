<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Display - {{ $setting->judul_utama ?? 'Aggregator' }}</title>
    
    <!-- Memanggil Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Arab (Amiri) untuk BEE Smart -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Animasi dan Layout Layar Penuh */
        body {
            margin: 0; padding: 0; overflow: hidden; 
            background-color: #f1f5f9; background-size: cover;
            background-position: center; background-repeat: no-repeat;
            @if($setting->background_image)
                /* DIUBAH KE BERKAS */
                background-image: url('{{ url('berkas/' . $setting->background_image) }}');
            @endif
        }
        
        .bg-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(255, 255, 255, 0.85); z-index: -1;
        }

        /* Kelas untuk Font Arab */
        .font-arabic { font-family: 'Amiri', serif; }

        /* --- PENGATURAN TRANSISI SLIDE UTAMA --- */
        .slide {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            opacity: 0; visibility: hidden;
            transition: opacity 0.4s ease-in-out, visibility 0.4s;
            pointer-events: none; display: flex; flex-direction: column;
        }
        .slide.active, .slide.exiting {
            opacity: 1; visibility: visible; pointer-events: auto;
        }

        /* =========================================================
           EFEK ANIMASI KHUSUS (KEYFRAMES MASUK & KELUAR)
           ========================================================= */
        @keyframes bounceUp {
            0% { opacity: 0; transform: translateY(60px) scale(0.9); }
            60% { opacity: 1; transform: translateY(-5px) scale(1.02); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes bounceDown {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(60px) scale(0.9); }
        }

        @keyframes fadeInBox {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOutBox {
            0% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(30px); }
        }

        @keyframes slideInRight {
            0% { opacity: 0; transform: translateX(-40px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideOutLeft {
            0% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(-40px); }
        }

        /* --- MENERAPKAN ANIMASI --- */
        .animate-bounce-up { opacity: 0; }
        .slide.active .animate-bounce-up { animation: bounceUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }

        .animate-box { opacity: 0; }
        .slide.active .animate-box { animation: fadeInBox 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }

        .animate-slide-right { opacity: 0; }
        .slide.active .animate-slide-right { animation: slideInRight 0.6s cubic-bezier(0.25, 1, 0.5, 1) forwards; }

        .slide.exiting .animate-bounce-up { animation: bounceDown 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards !important; animation-delay: 0s !important; }
        .slide.exiting .animate-box { animation: fadeOutBox 0.5s cubic-bezier(0.25, 1, 0.5, 1) forwards !important; animation-delay: 0s !important; }
        .slide.exiting .animate-slide-right { animation: slideOutLeft 0.4s cubic-bezier(0.25, 1, 0.5, 1) forwards !important; animation-delay: 0s !important; }

        /* --- STYLING ELEMEN KARTU --- */
        .aggregator-card {
            background: #ffffff; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 20px 12px; display: flex; flex-direction: column; align-items: center;
            text-align: center; border-top-width: 8px; border-top-style: solid;
            width: 100%; height: 100%;
        }
        .avatar-fallback {
            width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 28px; font-weight: 900; color: #ffffff;
            border: 4px solid; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        /* --- MENGEMBALIKAN DESAIN LEGA (SPACIOUS) YANG ASLI --- */
        .leaderboard-box {
            background-color: #1e3a8a; border-radius: 20px;
            padding: 45px 24px 24px 24px; /* Padding besar kembali */
            box-shadow: 0 15px 30px rgba(30, 58, 138, 0.2);
            position: relative; display: flex; flex-direction: column; gap: 16px; 
            width: 100%; height: 100%; 
        }
        .leaderboard-title {
            background-color: white; color: #f97316; 
            font-size: 20px; font-weight: 900; text-align: center; /* Font besar kembali */
            padding: 8px 24px; border-radius: 50px; position: absolute;
            top: -24px; left: 50%; transform: translateX(-50%);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-transform: uppercase; white-space: nowrap;
            z-index: 10;
        }
        .rank-card {
            background: white; border-radius: 12px; padding: 16px 20px; /* Jarak lega kembali */
            display: flex; align-items: center; gap: 12px; width: 100%;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); flex: 1; 
        }
        
        /* --- STYLING TOMBOL FULLSCREEN & START --- */
        #fs-btn {
            position: fixed; bottom: 20px; right: 20px; z-index: 50;
            background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(5px);
            border: 1px solid rgba(0,0,0,0.1); color: #475569;
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; transition: all 0.2s ease;
        }
        #fs-btn:hover { background: rgba(255, 255, 255, 0.9); color: #0f172a; transform: scale(1.1); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        #start-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.95); z-index: 9999;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            cursor: pointer;
        }
        .pulse-btn { animation: pulse-ring 2s infinite; }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
</head>

<body class="text-gray-800 font-sans h-screen w-screen relative flex flex-col">

    <!-- ========================================================================= -->
    <!-- LOGIKA PENARIKAN DATA ASRAMA                                              -->
    <!-- ========================================================================= -->
    @php
        $bulanIni = \Carbon\Carbon::now()->month;
        $tahunIni = \Carbon\Carbon::now()->year;

        // 1. DATA KLASEMEN BULANAN (DIUBAH AGAR MEMBIDIK TABEL DETAIL SKOR)
        $semuaKamar = \App\Models\AsramaKamar::where('status', 'aktif')->get();
        if($semuaKamar->count() == 0) {
            $semuaKamar = \App\Models\AsramaKamar::all(); 
        }

        $inspeksiFinalBulanIni = \App\Models\AsramaPenilaian::where('status', 'final')
                                    ->whereMonth('tanggal', $bulanIni)
                                    ->whereYear('tanggal', $tahunIni)
                                    ->pluck('id');

        $penilaianDetail = \App\Models\AsramaPenilaianKamar::whereIn('penilaian_id', $inspeksiFinalBulanIni)->get();

        $users = \Illuminate\Support\Facades\DB::table('users')->pluck('name', 'id');

        $rankingKamarRaw = $semuaKamar->map(function ($kamar) use ($penilaianDetail, $users) {
            $sidakKamarIni = $penilaianDetail->where('kamar_id', $kamar->id);
            $jumlahSidak = $sidakKamarIni->count();
            
            $rataRata = $jumlahSidak > 0 ? $sidakKamarIni->avg('total_skor') : 0;

            $musyrifId = $kamar->musyrif_id ?? $kamar->pembina_id ?? $kamar->guru_id ?? null;
            $namaMusyrif = $musyrifId ? ($users[$musyrifId] ?? 'Tidak Ditemukan') : 'Belum Diatur';
            $kategori = strtolower($kamar->kategori ?? $kamar->tipe_kamar ?? '');

            return (object) [
                'id' => $kamar->id,
                'nama_kamar' => $kamar->nama_kamar ?? $kamar->nama ?? 'Kamar ' . $kamar->id,
                'nama_musyrif' => $namaMusyrif,
                'jumlah_sidak' => $jumlahSidak,
                'rata_rata_skor' => (float) $rataRata,
                'kategori' => $kategori
            ];
        });

        $rankingKamar = $rankingKamarRaw->sortByDesc('rata_rata_skor')->values();

        // Top 3 Bulanan Putra
        $putraAktif = $rankingKamar->filter(fn($k) => (str_contains($k->kategori, 'putra') || str_contains($k->kategori, 'ikhwan')) && $k->jumlah_sidak > 0)->values();
        $putraTerbersih = $putraAktif->take(3);

        // Top 3 Bulanan Putri
        $putriAktif = $rankingKamar->filter(fn($k) => (str_contains($k->kategori, 'putri') || str_contains($k->kategori, 'akhwat')) && $k->jumlah_sidak > 0)->values();
        $putriTerbersih = $putriAktif->take(3);

        // 2. DATA HIGHLIGHT SIDAK TERAKHIR (DENGAN FOTO)
        // Putra
        $latestPutra = \App\Models\AsramaPenilaian::with(['kamarTerbersih.musyrif', 'kamarTerkotor.musyrif'])
            ->where('kategori', 'putra')->where('status', 'final')->orderBy('tanggal', 'desc')->first();

        $runnerUpPutra = null;
        if($latestPutra) {
            $runnerUpPutra = \App\Models\AsramaPenilaianKamar::with('kamar')
                ->where('penilaian_id', $latestPutra->id)
                ->where('kamar_id', '!=', $latestPutra->kamar_terbersih_id)
                ->orderBy('total_skor', 'desc')->first();
        }

        // Putri
        $latestPutri = \App\Models\AsramaPenilaian::with(['kamarTerbersih.musyrif', 'kamarTerkotor.musyrif'])
            ->where('kategori', 'putri')->where('status', 'final')->orderBy('tanggal', 'desc')->first();

        $runnerUpPutri = null;
        if($latestPutri) {
            $runnerUpPutri = \App\Models\AsramaPenilaianKamar::with('kamar')
                ->where('penilaian_id', $latestPutri->id)
                ->where('kamar_id', '!=', $latestPutri->kamar_terbersih_id)
                ->orderBy('total_skor', 'desc')->first();
        }
    @endphp

    <!-- Layar Start -->
    <div id="start-overlay" onclick="startDisplay()">
        @if($setting->logo)
            <!-- DIUBAH KE BERKAS -->
            <img src="{{ url('berkas/' . $setting->logo) }}" class="h-32 mb-8 drop-shadow-xl" alt="Logo">
        @endif
        <h1 class="text-4xl text-white font-black mb-10 tracking-widest uppercase">SISTEM DISPLAY TERPADU</h1>
        <button class="pulse-btn bg-blue-600 text-white font-bold text-2xl py-4 px-10 rounded-full shadow-[0_0_20px_rgba(37,99,235,0.5)]">
            ▶ Ketuk Layar Untuk Memulai
        </button>
        <p class="text-slate-400 mt-6 font-semibold">Tindakan ini diperlukan untuk mengaktifkan pemutaran Audio BEE Smart.</p>
    </div>

    <div class="bg-overlay"></div>

    <!-- ================= HEADER LOGO & JUDUL (DIBERI JARAK AGAR TIDAK NABRAK) ================= -->
    <div id="header-area" class="w-full text-center pt-6 pb-2 z-10 shrink-0 h-[15vh] flex flex-col justify-center relative">
        @if($setting->logo)
            <!-- DIUBAH KE BERKAS -->
            <img src="{{ url('berkas/' . $setting->logo) }}" alt="Logo" class="h-12 md:h-16 mx-auto mb-2 drop-shadow-md">
        @endif
        <h1 class="text-3xl md:text-5xl font-black text-blue-900 tracking-wider uppercase drop-shadow-sm" style="text-shadow: 2px 2px 0px white;">
            {{ $setting->judul_utama ?? 'STUDENT ROOT AGREGATOR' }}
        </h1>
    </div>

    <!-- ================= KONTINER SLIDE UTAMA (TINGGI DISET 85% LAYAR) ================= -->
    <div id="slide-container" class="relative flex-1 w-full" style="height: 85vh;">
        
        <!-- ========================================================= -->
        <!-- SLIDE 1: AGREGATOR KESELURUHAN (GRID 6 KOLOM)             -->
        <!-- ========================================================= -->
        <div id="slide-1" class="slide px-8 lg:px-16 pt-8 pb-10">
            <div style="zoom: 90%; height: 100%;" class="w-full">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 xl:gap-8 w-full max-w-[98%] mx-auto h-full content-center">
                    @forelse($group_points->take(12) as $grup)
                        @php
                            $warna = $grup->warna_grup ?? '#1e3a8a';
                            $mentorName = $grup->nama_mentor ?? 'Tanpa Mentor';
                        @endphp
                        
                        <div class="aggregator-card animate-bounce-up" style="border-top-color: {{ $warna }}; animation-delay: {{ $loop->index * 0.05 }}s;">
                            <div class="relative mb-5 w-full flex justify-center">
                                @if($grup->avatar_mentor)
                                    <!-- DIUBAH KE BERKAS -->
                                    <img src="{{ url('berkas/' . $grup->avatar_mentor) }}" class="w-16 h-16 rounded-full object-cover border-4 shadow-md" style="border-color: {{ $warna }};">
                                @else
                                    <div class="avatar-fallback" style="background-color: {{ $warna }}; border-color: {{ $warna }};">
                                        {{ substr($mentorName, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 bg-white text-[10px] font-bold px-3 py-1 rounded-full shadow border border-gray-200 text-gray-700 whitespace-nowrap max-w-[95%] overflow-hidden text-ellipsis">
                                    {{ $mentorName }}
                                </div>
                            </div>
                            
                            <h4 class="text-base xl:text-lg font-black uppercase mb-3 w-full truncate" style="color: {{ $warna }};">{{ $grup->nama_grup }}</h4>
                            
                            <div class="bg-gray-50 border border-gray-100 rounded-xl w-full py-3 shadow-inner mt-auto">
                                <p class="text-3xl xl:text-4xl font-black text-gray-800 leading-none mb-1">{{ number_format($grup->total_poin, 0, ',', '.') }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Total Points</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center text-gray-500 font-bold text-2xl">Belum ada grup yang aktif.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SLIDE 2: TOP 5 & HISTORI (DENGAN ZOOM PERSENTASE)         -->
        <!-- ========================================================= -->
        <div id="slide-2" class="slide px-8 lg:px-16 pt-8 pb-10">
            <!-- PENGATURAN ZOOM (PERSENTASE) DI SINI -->
            <div style="zoom: 82%; height: 100%;" class="w-full">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 xl:gap-12 w-full max-w-[98%] xl:max-w-[95%] mx-auto h-full items-stretch">
                    
                    <!-- KOLOM 1: TOP 5 GROUPS -->
                    <div class="leaderboard-box animate-box" style="animation-delay: 0.1s;">
                        <div class="leaderboard-title">Top 5 Groups</div>
                        @foreach($group_points->take(5) as $grup)
                            <div class="rank-card animate-slide-right" style="animation-delay: {{ 0.3 + ($loop->index * 0.15) }}s;">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-white text-lg shadow-sm shrink-0" style="background-color: {{ $grup->warna_grup ?? '#1e3a8a' }}">
                                    {{ $loop->iteration }}
                                </div>
                                <div class="flex-1 min-w-0 px-2">
                                    <h4 class="font-black text-blue-900 text-[15px] uppercase leading-tight truncate">{{ $grup->nama_grup }}</h4>
                                    <p class="text-xs font-bold text-gray-500 truncate">{{ $grup->nama_mentor ?? 'Tanpa Mentor' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-blue-800 text-xl">{{ number_format($grup->total_poin, 0, ',', '.') }}</p>
                                    <p class="text-[10px] font-bold text-gray-400">Points</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- KOLOM 2: TOP 5 CONTRIBUTORS -->
                    <div class="leaderboard-box animate-box" style="animation-delay: 0.4s;">
                        <div class="leaderboard-title">Top 5 Contributors</div>
                        @foreach($top_contributors as $siswa)
                            <div class="rank-card animate-slide-right" style="animation-delay: {{ 0.6 + ($loop->index * 0.15) }}s;">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-white text-lg shadow-sm shrink-0 bg-orange-500">
                                    {{ substr($siswa->nama_lengkap, 0, 1) }}
                                </div>
                                <div class="flex-1 min-w-0 px-2">
                                    <h4 class="font-black text-blue-900 text-[15px] capitalize leading-tight truncate">{{ $siswa->nama_lengkap }}</h4>
                                    <p class="text-xs font-bold text-gray-500 truncate">{{ $siswa->nama_grup ?? 'Belum ada grup' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-green-600 text-xl">+{{ number_format($siswa->total_poin, 0, ',', '.') }}</p>
                                    <p class="text-[10px] font-bold text-gray-400">Points</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- KOLOM 3: RECENT POINTS -->
                    <div class="leaderboard-box animate-box" style="animation-delay: 0.7s;">
                        <div class="leaderboard-title">Recent Points</div>
                        @foreach($recent_points as $rp)
                            <div class="rank-card flex-col items-stretch animate-slide-right" style="animation-delay: {{ 0.9 + ($loop->index * 0.15) }}s; gap: 4px; padding: 12px 16px;">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-black text-blue-900 text-sm capitalize truncate mr-2">{{ $rp->nama_lengkap }}</h4>
                                    <span class="text-[10px] font-bold text-gray-400 text-right shrink-0 leading-tight">
                                        {{ \Carbon\Carbon::parse($rp->created_at)->format('d M H:i') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-end mt-1">
                                    <p class="text-xs font-extrabold {{ $rp->poin > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        Poin {{ $rp->poin > 0 ? '+' : '' }}{{ $rp->poin }}
                                    </p>
                                </div>
                                @php $catatan = $rp->catatan ?? $rp->keterangan ?? ''; @endphp
                                @if($catatan)
                                    <p class="text-[10px] text-gray-500 italic mt-1 pt-1 truncate border-t border-gray-100" title="{{ $catatan }}">
                                        * {{ $catatan }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- SLIDE 3: RAPOR ASRAMA PUTRA (IKHWAN)                      -->
        <!-- ========================================================= -->
        @if($latestPutra || $putraTerbersih->isNotEmpty())
        <div id="slide-asrama-putra" class="slide px-8 lg:px-16 pt-8 pb-10">
            <div style="zoom: 85%; height: 100%;" class="w-full">
                
                <div class="flex flex-col items-center justify-center mb-8 animate-bounce-up shrink-0">
                    <div class="bg-blue-100 text-blue-800 px-8 py-2 rounded-full font-black tracking-widest uppercase text-base shadow-sm border border-blue-200">
                        👨‍👦 HASIL INSPEKSI ASRAMA PUTRA
                    </div>
                    @if($latestPutra)
                        <p class="text-sm font-bold text-gray-500 mt-2 bg-white/50 px-4 py-1 rounded-full">Sidak: {{ \Carbon\Carbon::parse($latestPutra->tanggal)->locale('id')->translatedFormat('l, d F Y') }}</p>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 w-full max-w-[98%] mx-auto h-[75%] content-start items-stretch">
                    <!-- KOLOM 1: JUARA 1 -->
                    <div class="bg-white rounded-[2rem] border-4 border-emerald-400 p-6 flex flex-col relative shadow-2xl animate-slide-right" style="animation-delay: 0.2s;">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-emerald-500 text-white px-8 py-2 rounded-full font-black text-sm tracking-widest shadow-lg whitespace-nowrap">
                            🏆 TERBERSIH HARI INI
                        </div>
                        @if($latestPutra && $latestPutra->foto_terbersih)
                            <!-- DIUBAH KE BERKAS -->
                            <img src="{{ url('berkas/'.$latestPutra->foto_terbersih) }}" class="w-full h-48 md:h-56 object-cover rounded-2xl mb-4 shadow-inner">
                        @else
                            <div class="w-full h-48 md:h-56 bg-emerald-50 rounded-2xl mb-4 flex flex-col items-center justify-center text-emerald-400 font-bold border-2 border-dashed border-emerald-200">
                                <span class="text-4xl mb-2">📸</span> Tanpa Foto Bukti
                            </div>
                        @endif
                        <div class="text-center flex-1 flex flex-col justify-center">
                            <h3 class="text-4xl font-black text-emerald-800 uppercase mb-1">{{ $latestPutra && $latestPutra->kamarTerbersih ? $latestPutra->kamarTerbersih->nama_kamar : 'TIDAK ADA' }}</h3>
                            <p class="text-sm font-bold text-gray-500 mb-4">Musyrif: {{ $latestPutra && $latestPutra->kamarTerbersih ? ($latestPutra->kamarTerbersih->musyrif->name ?? '-') : '-' }}</p>
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-black mt-auto uppercase tracking-wide">
                                ✨ REWARD: SELURUH ANGGOTA +1 POIN
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM 2: CALON JUARA & KLASEMEN -->
                    <div class="flex flex-col gap-6 animate-box" style="animation-delay: 0.4s;">
                        @if($runnerUpPutra)
                        <div class="bg-white rounded-[1.5rem] border border-gray-200 p-5 shadow-lg relative overflow-hidden flex-shrink-0">
                            <div class="absolute right-0 top-0 bottom-0 w-3 bg-amber-400"></div>
                            <p class="text-[11px] font-black text-amber-500 uppercase tracking-widest mb-1">🥈 Calon Juara Berikutnya</p>
                            <h4 class="text-2xl font-black text-gray-800 uppercase mb-1">{{ $runnerUpPutra->kamar->nama_kamar }}</h4>
                            <p class="text-xs font-bold text-gray-500">Skor: <span class="text-amber-600 font-black">{{ $runnerUpPutra->total_skor }}</span> — Terus pertahankan kerapian!</p>
                        </div>
                        @endif
                        <div class="bg-gradient-to-b from-blue-800 to-indigo-900 rounded-[1.5rem] p-6 shadow-xl flex-1 flex flex-col justify-center">
                            <h4 class="text-white font-black text-center mb-5 uppercase tracking-widest text-sm border-b border-blue-700 pb-3">📊 Klasemen Rata-Rata Bulan Ini</h4>
                            <div class="space-y-4 flex-1 flex flex-col justify-center">
                                @forelse($putraTerbersih->take(3) as $k)
                                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 flex items-center gap-4 border border-white/5">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-300 to-yellow-500 text-yellow-900 font-black flex items-center justify-center text-lg shrink-0 shadow-md">{{ $loop->iteration }}</div>
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-white font-bold text-lg truncate">{{ $k->nama_kamar }}</h5>
                                        </div>
                                        <div class="text-white font-black bg-black/30 px-3 py-1.5 rounded-lg text-sm shrink-0 shadow-inner">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                    </div>
                                @empty
                                    <div class="text-center text-blue-300 font-bold text-sm">Belum ada data asrama bulan ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM 3: TERKOTOR -->
                    <div class="bg-white rounded-[2rem] border-4 border-red-500 p-6 flex flex-col relative shadow-2xl animate-slide-right" style="animation-delay: 0.6s;">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-8 py-2 rounded-full font-black text-sm tracking-widest shadow-lg whitespace-nowrap">
                            ⚠️ PERHATIAN EKSTRA
                        </div>
                        @if($latestPutra && $latestPutra->foto_terkotor)
                            <!-- DIUBAH KE BERKAS -->
                            <img src="{{ url('berkas/'.$latestPutra->foto_terkotor) }}" class="w-full h-48 md:h-56 object-cover rounded-2xl mb-4 shadow-inner">
                        @else
                            <div class="w-full h-48 md:h-56 bg-red-50 rounded-2xl mb-4 flex flex-col items-center justify-center text-red-400 font-bold border-2 border-dashed border-red-200">
                                <span class="text-4xl mb-2">📸</span> Tanpa Foto Bukti
                            </div>
                        @endif
                        <div class="text-center flex-1 flex flex-col justify-center">
                            <h3 class="text-4xl font-black text-red-800 uppercase mb-1">{{ $latestPutra && $latestPutra->kamarTerkotor ? $latestPutra->kamarTerkotor->nama_kamar : 'TIDAK ADA' }}</h3>
                            <p class="text-sm font-bold text-gray-500 mb-4">Musyrif: {{ $latestPutra && $latestPutra->kamarTerkotor ? ($latestPutra->kamarTerkotor->musyrif->name ?? '-') : '-' }}</p>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-black mt-auto uppercase tracking-wide">
                                🚨 SANKSI: SELURUH ANGGOTA -1 POIN
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ========================================================= -->
        <!-- SLIDE 4: RAPOR ASRAMA PUTRI (AKHWAT)                      -->
        <!-- ========================================================= -->
        @if($latestPutri || $putriTerbersih->isNotEmpty())
        <div id="slide-asrama-putri" class="slide px-8 lg:px-16 pt-8 pb-10">
            <div style="zoom: 85%; height: 100%;" class="w-full">
                
                <div class="flex flex-col items-center justify-center mb-8 animate-bounce-up shrink-0">
                    <div class="bg-pink-100 text-pink-800 px-8 py-2 rounded-full font-black tracking-widest uppercase text-base shadow-sm border border-pink-200">
                        👩‍👧 HASIL INSPEKSI ASRAMA PUTRI
                    </div>
                    @if($latestPutri)
                        <p class="text-sm font-bold text-gray-500 mt-2 bg-white/50 px-4 py-1 rounded-full">Sidak: {{ \Carbon\Carbon::parse($latestPutri->tanggal)->locale('id')->translatedFormat('l, d F Y') }}</p>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 w-full max-w-[98%] mx-auto h-[75%] content-start items-stretch">
                    <!-- KOLOM 1: JUARA 1 -->
                    <div class="bg-white rounded-[2rem] border-4 border-emerald-400 p-6 flex flex-col relative shadow-2xl animate-slide-right" style="animation-delay: 0.2s;">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-emerald-500 text-white px-8 py-2 rounded-full font-black text-sm tracking-widest shadow-lg whitespace-nowrap">
                            🏆 TERBERSIH HARI INI
                        </div>
                        @if($latestPutri && $latestPutri->foto_terbersih)
                            <!-- DIUBAH KE BERKAS -->
                            <img src="{{ url('berkas/'.$latestPutri->foto_terbersih) }}" class="w-full h-48 md:h-56 object-cover rounded-2xl mb-4 shadow-inner">
                        @else
                            <div class="w-full h-48 md:h-56 bg-emerald-50 rounded-2xl mb-4 flex flex-col items-center justify-center text-emerald-400 font-bold border-2 border-dashed border-emerald-200">
                                <span class="text-4xl mb-2">📸</span> Tanpa Foto Bukti
                            </div>
                        @endif
                        <div class="text-center flex-1 flex flex-col justify-center">
                            <h3 class="text-4xl font-black text-emerald-800 uppercase mb-1">{{ $latestPutri && $latestPutri->kamarTerbersih ? $latestPutri->kamarTerbersih->nama_kamar : 'TIDAK ADA' }}</h3>
                            <p class="text-sm font-bold text-gray-500 mb-4">Musyrifah: {{ $latestPutri && $latestPutri->kamarTerbersih ? ($latestPutri->kamarTerbersih->musyrif->name ?? '-') : '-' }}</p>
                            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-xs font-black mt-auto uppercase tracking-wide">
                                ✨ REWARD: SELURUH ANGGOTA +1 POIN
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM 2: CALON JUARA & KLASEMEN -->
                    <div class="flex flex-col gap-6 animate-box" style="animation-delay: 0.4s;">
                        @if($runnerUpPutri)
                        <div class="bg-white rounded-[1.5rem] border border-gray-200 p-5 shadow-lg relative overflow-hidden flex-shrink-0">
                            <div class="absolute right-0 top-0 bottom-0 w-3 bg-amber-400"></div>
                            <p class="text-[11px] font-black text-amber-500 uppercase tracking-widest mb-1">🥈 Calon Juara Berikutnya</p>
                            <h4 class="text-2xl font-black text-gray-800 uppercase mb-1">{{ $runnerUpPutri->kamar->nama_kamar }}</h4>
                            <p class="text-xs font-bold text-gray-500">Skor: <span class="text-amber-600 font-black">{{ $runnerUpPutri->total_skor }}</span> — Terus pertahankan kerapian!</p>
                        </div>
                        @endif
                        <div class="bg-gradient-to-b from-pink-700 to-rose-900 rounded-[1.5rem] p-6 shadow-xl flex-1 flex flex-col justify-center">
                            <h4 class="text-white font-black text-center mb-5 uppercase tracking-widest text-sm border-b border-pink-500 pb-3">📊 Klasemen Rata-Rata Bulan Ini</h4>
                            <div class="space-y-4 flex-1 flex flex-col justify-center">
                                @forelse($putriTerbersih->take(3) as $k)
                                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-3 flex items-center gap-4 border border-white/5">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-300 to-yellow-500 text-yellow-900 font-black flex items-center justify-center text-lg shrink-0 shadow-md">{{ $loop->iteration }}</div>
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-white font-bold text-lg truncate">{{ $k->nama_kamar }}</h5>
                                        </div>
                                        <div class="text-white font-black bg-black/30 px-3 py-1.5 rounded-lg text-sm shrink-0 shadow-inner">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                    </div>
                                @empty
                                    <div class="text-center text-pink-300 font-bold text-sm">Belum ada data asrama bulan ini.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM 3: TERKOTOR -->
                    <div class="bg-white rounded-[2rem] border-4 border-red-500 p-6 flex flex-col relative shadow-2xl animate-slide-right" style="animation-delay: 0.6s;">
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-8 py-2 rounded-full font-black text-sm tracking-widest shadow-lg whitespace-nowrap">
                            ⚠️ PERHATIAN EKSTRA
                        </div>
                        @if($latestPutri && $latestPutri->foto_terkotor)
                            <!-- DIUBAH KE BERKAS -->
                            <img src="{{ url('berkas/'.$latestPutri->foto_terkotor) }}" class="w-full h-48 md:h-56 object-cover rounded-2xl mb-4 shadow-inner">
                        @else
                            <div class="w-full h-48 md:h-56 bg-red-50 rounded-2xl mb-4 flex flex-col items-center justify-center text-red-400 font-bold border-2 border-dashed border-red-200">
                                <span class="text-4xl mb-2">📸</span> Tanpa Foto Bukti
                            </div>
                        @endif
                        <div class="text-center flex-1 flex flex-col justify-center">
                            <h3 class="text-4xl font-black text-red-800 uppercase mb-1">{{ $latestPutri && $latestPutri->kamarTerkotor ? $latestPutri->kamarTerkotor->nama_kamar : 'TIDAK ADA' }}</h3>
                            <p class="text-sm font-bold text-gray-500 mb-4">Musyrifah: {{ $latestPutri && $latestPutri->kamarTerkotor ? ($latestPutri->kamarTerkotor->musyrif->name ?? '-') : '-' }}</p>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs font-black mt-auto uppercase tracking-wide">
                                🚨 SANKSI: SELURUH ANGGOTA -1 POIN
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ======================================================= -->
        <!-- SLIDE 5..N: MODUL BEE SMART (KOSAKATA)                  -->
        <!-- ======================================================= -->
        @if(isset($activeBeeWeek) && $activeBeeWeek->vocabs->count() > 0)
            @foreach($activeBeeWeek->vocabs as $vocab)
                <div class="slide slide-bee px-8 md:px-16 pb-12 pt-6 flex flex-col items-center justify-center">
                    <div style="zoom: 90%; width: 100%;" class="flex flex-col items-center justify-center">
                        <div class="text-2xl text-amber-500 font-black tracking-widest mb-10 uppercase border-4 border-dashed border-amber-400 py-3 px-10 rounded-full bg-white/50 backdrop-blur-sm shadow-sm animate-bounce-up">
                            🐝 BEE Smart: {{ $activeBeeWeek->judul }}
                        </div>

                        <div class="text-6xl font-black text-gray-800 mb-12 animate-box" style="animation-delay: 0.2s;">
                            "{{ $vocab->kosakata_id }}"
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 w-full max-w-[90%] xl:max-w-[80%] mx-auto items-stretch">
                            <!-- Box English -->
                            <div class="bg-white/90 backdrop-blur-md rounded-[40px] p-12 shadow-2xl border-t-[12px] border-emerald-400 text-center animate-slide-right flex flex-col justify-center" style="animation-delay: 0.4s;">
                                <div class="text-6xl mb-6">🇬🇧</div>
                                <h4 class="text-slate-400 font-bold uppercase tracking-widest mb-8 text-xl">English</h4>
                                <div class="text-6xl font-black text-emerald-500 mb-6">{{ $vocab->vocab_en }}</div>
                                <div class="text-3xl text-emerald-700 italic font-semibold leading-relaxed">"{{ $vocab->sentence_en }}"</div>
                                
                                <!-- DIUBAH KE BERKAS -->
                                @if($vocab->audio_vocab_en) <audio class="audio-bee" src="{{ url('berkas/'.$vocab->audio_vocab_en) }}"></audio> @endif
                                @if($vocab->audio_sentence_en) <audio class="audio-bee" src="{{ url('berkas/'.$vocab->audio_sentence_en) }}"></audio> @endif
                            </div>

                            <!-- Box Arabic -->
                            <div class="bg-white/90 backdrop-blur-md rounded-[40px] p-12 shadow-2xl border-t-[12px] border-blue-400 text-center animate-slide-right flex flex-col justify-center" style="animation-delay: 0.6s;" dir="rtl">
                                <div class="text-6xl mb-6">🇸🇦</div>
                                <h4 class="text-slate-400 font-bold uppercase tracking-widest mb-8 text-xl">العربية</h4>
                                <div class="text-[5.5rem] font-bold text-blue-500 mb-4 font-arabic leading-tight">{{ $vocab->mufrodat_ar }}</div>
                                <div class="text-4xl text-blue-700 font-arabic leading-relaxed font-bold">{{ $vocab->jumlah_ar }}</div>
                                
                                <!-- DIUBAH KE BERKAS -->
                                @if($vocab->audio_mufrodat_ar) <audio class="audio-bee" src="{{ url('berkas/'.$vocab->audio_mufrodat_ar) }}"></audio> @endif
                                @if($vocab->audio_jumlah_ar) <audio class="audio-bee" src="{{ url('berkas/'.$vocab->audio_jumlah_ar) }}"></audio> @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

    </div>

    <!-- ================= TOMBOL FULLSCREEN ================= -->
    <button id="fs-btn" onclick="toggleFullScreen()" title="Aktifkan Layar Penuh">
        <svg id="fs-icon-enter" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
        </svg>
        <svg id="fs-icon-exit" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 14h4v4m0-4l-5 5m11-1h4v4m0-4l-5 5M4 10h4V6m0 4l-5-5m11 5h4V6m0 4l-5-5"></path>
        </svg>
    </button>

    <!-- SCRIPT ANIMASI, AUDIO PLAYER, & AJAX RELOAD -->
    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch((err) => {});
            } else {
                if (document.exitFullscreen) { document.exitFullscreen(); }
            }
        }

        document.addEventListener('fullscreenchange', (event) => {
            if (document.fullscreenElement) {
                document.getElementById('fs-icon-enter').classList.add('hidden');
                document.getElementById('fs-icon-exit').classList.remove('hidden');
            } else {
                document.getElementById('fs-icon-enter').classList.remove('hidden');
                document.getElementById('fs-icon-exit').classList.add('hidden');
            }
        });

        const slideDuration = {{ ($setting->durasi_slide ?? 60) * 1000 }}; 
        const exitAnimationDuration = 600; 
        let slides = [];
        let totalSlides = 0;
        let currentSlide = 0;
        let isAudioUnlocked = false;

        function startDisplay() {
            document.getElementById('start-overlay').style.display = 'none';
            isAudioUnlocked = true;
            slides = document.querySelectorAll('.slide');
            totalSlides = slides.length;
            if(totalSlides > 0) { processCurrentSlide(); }
        }

        function playAudioSequence(slideElement) {
            return new Promise(async (resolve) => {
                let audios = slideElement.querySelectorAll('.audio-bee');
                
                if(audios.length === 0) {
                    setTimeout(resolve, 5000); 
                    return;
                }
                
                for (let i = 0; i < audios.length; i++) {
                    let audio = audios[i];
                    await new Promise(r => {
                        audio.onended = () => { setTimeout(r, 800); }; 
                        audio.onerror = r; 
                        audio.play().catch(e => { console.log('Autoplay diblokir', e); r(); });
                    });
                }
                
                setTimeout(resolve, 1500); 
            });
        }

        async function processCurrentSlide() {
            slides = document.querySelectorAll('.slide');
            totalSlides = slides.length;
            if (currentSlide >= totalSlides) currentSlide = 0;

            let activeSlide = slides[currentSlide];
            activeSlide.classList.add('active');

            if (activeSlide.classList.contains('slide-bee') && isAudioUnlocked) {
                await playAudioSequence(activeSlide);
            } else {
                await new Promise(r => setTimeout(r, slideDuration));
            }

            activeSlide.classList.remove('active');
            activeSlide.classList.add('exiting');

            setTimeout(() => {
                activeSlide.classList.remove('exiting');
                currentSlide++;

                if (currentSlide >= totalSlides) {
                    doAjaxReload();
                } else {
                    processCurrentSlide();
                }
            }, exitAnimationDuration);
        }

        function doAjaxReload() {
            const fetchUrl = new URL(window.location.href);
            fetchUrl.searchParams.set('_t', new Date().getTime()); 

            fetch(fetchUrl.toString())
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");
                    document.getElementById('slide-container').innerHTML = doc.getElementById('slide-container').innerHTML;
                    currentSlide = 0;
                    setTimeout(() => { processCurrentSlide(); }, 50); 
                })
                .catch(err => {
                    window.location.reload();
                });
        }
    </script>
</body>
</html>