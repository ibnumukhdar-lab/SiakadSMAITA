<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BEE Smart - Mode Presentasi Kelas</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Arab (Amiri) & Font Latin -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f8fafc; overflow: hidden; }
        .font-arabic { font-family: 'Amiri', serif; }
        
        /* Transisi Slide */
        .slide { display: none; opacity: 0; transition: opacity 0.3s ease-in-out; }
        .slide.active { display: flex; opacity: 1; }

        /* Efek Tombol Audio */
        .btn-audio { transition: all 0.2s; }
        .btn-audio:hover { transform: scale(1.1); filter: brightness(1.2); }
        .btn-audio:active { transform: scale(0.9); }
        
        /* Animasi saat suara diputar */
        .playing { animation: pulse-ring 1s infinite; box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col relative">

    @if(!$activeWeek || $activeWeek->vocabs->count() == 0)
        <div class="flex-1 flex flex-col items-center justify-center">
            <div class="text-6xl mb-4">📭</div>
            <h1 class="text-3xl font-bold text-slate-500">Belum ada Modul BEE Smart yang Aktif.</h1>
            <p class="text-slate-400 mt-2">Silakan aktifkan modul minggu ini melalui dashboard admin.</p>
            <a href="{{ route('bee.index') }}" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-md transition">Kembali ke Dashboard</a>
        </div>
    @else

        <!-- ================= HEADER PRESENTASI ================= -->
        <div class="w-full bg-white shadow-sm flex justify-between items-center px-8 py-4 z-10 shrink-0 border-b-4 border-amber-400">
            <div class="flex items-center gap-4">
                <span class="text-4xl">🐝</span>
                <div>
                    <h1 class="text-2xl font-black text-blue-900 leading-none mb-1">BEE SMART</h1>
                    
                    <!-- MENU DROPDOWN PILIH MODUL -->
                    @if(isset($activeWeeks) && $activeWeeks->count() > 1)
                        <form action="{{ route('bee.classroom') }}" method="GET" class="inline-block">
                            <select name="modul_id" onchange="this.form.submit()" class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-300 rounded px-2 py-1 outline-none cursor-pointer hover:bg-amber-100 transition uppercase tracking-widest shadow-sm">
                                <option value="semua" {{ (isset($activeWeek->id) && $activeWeek->id === 'semua') ? 'selected' : '' }}>🌟 TAMPILKAN SEMUA MODUL</option>
                                @foreach($activeWeeks as $week)
                                    <option value="{{ $week->id }}" {{ (isset($activeWeek->id) && $activeWeek->id == $week->id) ? 'selected' : '' }}>
                                        {{ $week->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">{{ $activeWeek->judul }}</p>
                    @endif
                </div>
            </div>
            
            <div class="flex gap-4">
                <a href="{{ route('bee.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-5 rounded-full flex items-center gap-2 transition border border-slate-200 shadow-sm">
                    Kembali
                </a>
                <!-- Tombol Putar Otomatis (Membaca urut 1 slide ini) -->
                <button onclick="playAllCurrentSlide()" class="bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-bold py-2 px-6 rounded-full flex items-center gap-2 transition border border-indigo-200 shadow-sm">
                    <span>🔊</span> Baca Semua
                </button>
                <button onclick="toggleFullScreen()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-5 rounded-full flex items-center gap-2 transition shadow-sm" title="Layar Penuh (F11)">
                    ⛶ Fullscreen
                </button>
            </div>
        </div>

        <!-- ================= AREA SLIDE KOSAKATA ================= -->
        <div class="flex-1 relative bg-slate-50 overflow-hidden w-full flex items-center justify-center">
            
            @foreach($activeWeek->vocabs as $index => $vocab)
                <div class="slide flex-col items-center justify-center w-full h-full p-8 {{ $index == 0 ? 'active' : '' }}" id="slide-{{ $index }}">
                    
                    <!-- Kata Indonesia -->
                    <div class="text-center mb-10">
                        <span class="text-sm font-bold text-amber-600 bg-amber-100 px-4 py-1 rounded-full uppercase tracking-widest border border-amber-200">Kosakata #{{ $index + 1 }}</span>
                        <h2 class="text-5xl md:text-6xl font-black text-slate-800 mt-4 capitalize">"{{ $vocab->kosakata_id }}"</h2>
                    </div>

                    <!-- Layout 2 Kolom (Inggris & Arab) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-6xl">
                        
                        <!-- Kolom Inggris -->
                        <div class="bg-white rounded-3xl p-10 shadow-xl border-t-8 border-emerald-400 relative">
                            <div class="absolute top-6 right-6 text-4xl opacity-50">🇬🇧</div>
                            <h3 class="text-slate-400 font-bold uppercase tracking-widest mb-6">English</h3>
                            
                            <!-- Kosakata -->
                            <div class="flex items-center gap-4 mb-6">
                                <div class="text-5xl font-black text-emerald-600">{{ $vocab->vocab_en }}</div>
                                @if($vocab->audio_vocab_en)
                                    <button onclick="playAudio('audio-en-word-{{ $index }}', this)" class="btn-audio w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shadow border border-emerald-200">🔊</button>
                                    <audio id="audio-en-word-{{ $index }}" src="{{ url('berkas/' . $vocab->audio_vocab_en) }}"></audio>
                                @endif
                            </div>
                            
                            <!-- Kalimat -->
                            <div class="flex items-start gap-4">
                                <div class="text-2xl text-emerald-800 italic font-semibold leading-relaxed">"{{ $vocab->sentence_en }}"</div>
                                @if($vocab->audio_sentence_en)
                                    <button onclick="playAudio('audio-en-sent-{{ $index }}', this)" class="btn-audio w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shadow shrink-0 mt-1 border border-emerald-200">🔊</button>
                                    <audio id="audio-en-sent-{{ $index }}" src="{{ url('berkas/' . $vocab->audio_sentence_en) }}"></audio>
                                @endif
                            </div>
                        </div>

                        <!-- Kolom Arab -->
                        <div class="bg-white rounded-3xl p-10 shadow-xl border-t-8 border-blue-400 relative text-right" dir="rtl">
                            <div class="absolute top-6 left-6 text-4xl opacity-50" dir="ltr">🇸🇦</div>
                            <h3 class="text-slate-400 font-bold uppercase tracking-widest mb-6" dir="ltr">العربية (Arabic)</h3>
                            
                            <!-- Kosakata -->
                            <div class="flex items-center justify-start gap-4 mb-6">
                                <div class="text-6xl font-bold text-blue-600 font-arabic leading-tight">{{ $vocab->mufrodat_ar }}</div>
                                @if($vocab->audio_mufrodat_ar)
                                    <button onclick="playAudio('audio-ar-word-{{ $index }}', this)" class="btn-audio w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl shadow shrink-0 border border-blue-200" dir="ltr">🔊</button>
                                    <audio id="audio-ar-word-{{ $index }}" src="{{ url('berkas/' . $vocab->audio_mufrodat_ar) }}"></audio>
                                @endif
                            </div>
                            
                            <!-- Kalimat -->
                            <div class="flex items-start justify-start gap-4">
                                <div class="text-3xl text-blue-800 font-bold font-arabic leading-relaxed">{{ $vocab->jumlah_ar }}</div>
                                @if($vocab->audio_jumlah_ar)
                                    <button onclick="playAudio('audio-ar-sent-{{ $index }}', this)" class="btn-audio w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow shrink-0 mt-1 border border-blue-200" dir="ltr">🔊</button>
                                    <audio id="audio-ar-sent-{{ $index }}" src="{{ url('berkas/' . $vocab->audio_jumlah_ar) }}"></audio>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

        </div>

        <!-- ================= FOOTER NAVIGASI GURU ================= -->
        <div class="w-full bg-slate-800 text-white p-4 flex justify-between items-center z-10 shrink-0">
            <button onclick="prevSlide()" class="hover:bg-slate-700 px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition">
                <span>◀</span> Kata Sebelumnya
            </button>
            
            <div class="text-center">
                <p class="font-bold text-lg"><span id="current-index" class="text-amber-400">1</span> / {{ $activeWeek->vocabs->count() }}</p>
                <p class="text-xs text-slate-400">Gunakan panah ⬅ ➡ di keyboard</p>
            </div>
            
            <button onclick="nextSlide()" class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition shadow-lg border border-blue-500">
                Kata Selanjutnya <span>▶</span>
            </button>
        </div>

        <!-- ================= SCRIPT LOGIKA KELAS ================= -->
        <script>
            let currentSlide = 0;
            const totalSlides = {{ $activeWeek->vocabs->count() }};
            let isPlayingSequence = false;

            // FUNGSI NAVIGASI SLIDE
            function showSlide(index) {
                // Sembunyikan semua slide
                document.querySelectorAll('.slide').forEach(el => el.classList.remove('active'));
                
                // Hentikan semua audio yang sedang berjalan
                document.querySelectorAll('audio').forEach(audio => {
                    audio.pause();
                    audio.currentTime = 0;
                });
                document.querySelectorAll('.btn-audio').forEach(btn => btn.classList.remove('playing'));
                isPlayingSequence = false;

                // Batasan Index (Looping)
                if (index >= totalSlides) currentSlide = 0; 
                else if (index < 0) currentSlide = totalSlides - 1; 
                else currentSlide = index;

                // Tampilkan slide yang dituju
                document.getElementById('slide-' + currentSlide).classList.add('active');
                document.getElementById('current-index').innerText = currentSlide + 1;
            }

            function nextSlide() { showSlide(currentSlide + 1); }
            function prevSlide() { showSlide(currentSlide - 1); }

            // NAVIGASI KEYBOARD (Panah Kiri / Kanan)
            document.addEventListener('keydown', function(event) {
                // Mencegah trigger saat guru mungkin sedang mengetik sesuatu di tempat lain
                if(event.target.tagName.toLowerCase() === 'input' || event.target.tagName.toLowerCase() === 'textarea' || event.target.tagName.toLowerCase() === 'select') return;
                
                if (event.key === "ArrowRight") { nextSlide(); }
                else if (event.key === "ArrowLeft") { prevSlide(); }
            });

            // FUNGSI MEMUTAR AUDIO MANUAL
            function playAudio(audioId, btnElement) {
                let audio = document.getElementById(audioId);
                if (!audio) return;

                // Jika sedang di-play, maka pause
                if (!audio.paused) {
                    audio.pause();
                    audio.currentTime = 0;
                    btnElement.classList.remove('playing');
                    return;
                }

                // Matikan audio lain yang sedang berbunyi agar tidak tabrakan
                document.querySelectorAll('audio').forEach(a => { a.pause(); a.currentTime = 0; });
                document.querySelectorAll('.btn-audio').forEach(b => b.classList.remove('playing'));

                // Putar audio yang dipilih dan tambahkan animasi gelombang ke tombol
                btnElement.classList.add('playing');
                audio.play();

                // Hapus animasi jika audio selesai
                audio.onended = function() {
                    btnElement.classList.remove('playing');
                };
            }

            // FUNGSI BACA SEMUA (Urut dalam 1 slide)
            async function playAllCurrentSlide() {
                if(isPlayingSequence) return; // Mencegah double klik
                isPlayingSequence = true;

                let slide = document.getElementById('slide-' + currentSlide);
                
                // Ambil daftar id audio dan tombol secara berurutan
                let sequence = [
                    { id: `audio-en-word-${currentSlide}` },
                    { id: `audio-en-sent-${currentSlide}` },
                    { id: `audio-ar-word-${currentSlide}` },
                    { id: `audio-ar-sent-${currentSlide}` }
                ];

                for(let item of sequence) {
                    if(!isPlayingSequence) break; // Jika guru pindah slide di tengah jalan, hentikan

                    let audio = document.getElementById(item.id);
                    if(audio) {
                        let btn = audio.previousElementSibling; // Tombol persis sebelum tag <audio>
                        btn.classList.add('playing');
                        
                        await new Promise(resolve => {
                            audio.onended = () => {
                                btn.classList.remove('playing');
                                setTimeout(resolve, 1000); // Jeda 1 detik antar suara
                            };
                            audio.onerror = () => { btn.classList.remove('playing'); resolve(); };
                            audio.play().catch(e => { btn.classList.remove('playing'); resolve(); });
                        });
                    }
                }
                isPlayingSequence = false;
            }

            // FUNGSI FULLSCREEN
            function toggleFullScreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    if (document.exitFullscreen) { document.exitFullscreen(); }
                }
            }
        </script>
    @endif
</body>
</html>