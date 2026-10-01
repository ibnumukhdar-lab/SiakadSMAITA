<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kuis & Belajar - {{ $week->judul }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- LIBRARY CONFETTI UNTUK SELEBRASI -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #f1f5f9; }
        .font-arabic { font-family: 'Amiri', serif; }
        
        /* Navigasi Tab Bawah */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-around; padding: 10px 5px; z-index: 50; box-shadow: 0 -4px 10px rgba(0,0,0,0.05); }
        .nav-item { flex: 1; text-align: center; color: #94a3b8; font-weight: 800; font-size: 11px; padding: 8px 0; border-radius: 12px; transition: 0.3s; }
        .nav-item.active { color: #1e40af; background: #e0f2fe; }
        .nav-item span { display: block; font-size: 20px; margin-bottom: 2px; }
        
        .tab-content { display: none; padding-bottom: 80px; }
        .tab-content.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Flipcard CSS */
        .flip-card { background-color: transparent; width: 100%; height: 350px; perspective: 1000px; margin-bottom: 20px; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; cursor: pointer; }
        .flip-card.flipped .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding: 20px; }
        .flip-card-front { background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; justify-content: center; align-items: center; }
        .flip-card-back { background-color: white; color: #1f2937; transform: rotateY(180deg); border: 2px solid #e2e8f0; overflow-y: auto; justify-content: flex-start; text-align: left; }

        /* Efek Tombol Audio Global */
        .btn-play { transition: 0.2s; }
        .btn-play:active { transform: scale(0.9); }
        .btn-play.playing { animation: pulseRing 1s infinite; background-color: #fef08a !important; color: #854d0e !important; }
        @keyframes pulseRing { 0% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(234, 179, 8, 0); } 100% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); } }

        /* Kuis Matching CSS */
        .match-btn { width: 100%; padding: 12px 10px; background: white; border: 2px solid #e2e8f0; border-radius: 12px; font-weight: 800; color: #475569; margin-bottom: 10px; transition: 0.2s; cursor: pointer; text-align: center; font-size: 14px; min-height: 50px; display: flex; align-items: center; justify-content: center; }
        .match-btn.selected { border-color: #3b82f6; background: #eff6ff; color: #1d4ed8; transform: scale(0.95); }
        .match-btn.matched { border-color: #22c55e; background: #dcfce7; color: #15803d; pointer-events: none; opacity: 0.5; }
        .match-btn.wrong { border-color: #ef4444; background: #fee2e2; color: #b91c1c; animation: shake 0.4s; }

        /* Kuis PG CSS */
        .pg-btn { width: 100%; padding: 15px; text-align: left; background: white; border: 2px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; font-weight: bold; color: #475569; transition: 0.2s; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        .pg-btn.correct { border-color: #22c55e; background: #dcfce7; color: #15803d; }
        .pg-btn.wrong { border-color: #ef4444; background: #fee2e2; color: #b91c1c; }

        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    </style>
</head>
<body>

    <!-- Header Sticky -->
    <div class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 py-3 flex items-center shadow-sm">
        <a href="{{ route('bee.buku-saku') }}" class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center font-bold text-slate-600">⬅</a>
        <div class="text-center flex-1">
            <h1 class="font-black text-slate-800 text-base truncate">{{ $week->judul }}</h1>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- TAB 1: BELAJAR (FLIPCARD) -->
    <!-- ============================================== -->
    <div id="tab-belajar" class="tab-content active px-5 pt-6">
        <div class="bg-blue-100 text-blue-800 p-4 rounded-xl text-sm font-bold mb-6 flex items-center gap-3 shadow-sm border border-blue-200">
            <span class="text-2xl">💡</span> Ketuk kartu untuk membalik. Tekan ikon 🔊 untuk memutar audio!
        </div>

        @foreach($week->vocabs as $index => $vocab)
            <div class="flip-card">
                <div class="flip-card-inner" onclick="flipCard(this)">
                    <!-- Sisi Depan (Indonesia) -->
                    <div class="flip-card-front">
                        <div class="text-xs font-bold text-blue-200 uppercase tracking-widest mb-2">Kosakata #{{ $index + 1 }}</div>
                        <h2 class="text-4xl font-black capitalize leading-tight">"{{ $vocab->kosakata_id }}"</h2>
                        <div class="mt-8 px-4 py-2 bg-blue-800/50 rounded-full text-blue-200 text-xs font-bold border border-blue-400/30">Ketuk untuk membalik ↻</div>
                    </div>
                    
                    <!-- Sisi Belakang (Inggris & Arab + Kalimat) -->
                    <div class="flip-card-back" onclick="event.stopPropagation()">
                        <button onclick="flipCard(this.closest('.flip-card-back'))" class="absolute top-3 right-3 text-xs bg-slate-100 px-3 py-1 rounded-full font-bold text-slate-500">Tutup ✖</button>

                        <div class="w-full mb-4 border-b border-slate-200 pb-4 pt-4">
                            <div class="text-xs font-black text-emerald-500 uppercase mb-2">🇬🇧 English</div>
                            <div class="flex items-center gap-3 mb-2">
                                <div class="text-3xl font-black text-slate-800">{{ $vocab->vocab_en }}</div>
                                @if($vocab->audio_vocab_en)
                                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                                    <button onclick="playAudioRaw('{{ url('berkas/'.$vocab->audio_vocab_en) }}', this)" class="btn-play w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center border border-emerald-200">🔊</button>
                                @endif
                            </div>
                            <div class="flex items-start gap-2">
                                <div class="text-sm font-bold text-slate-500 italic flex-1">"{{ $vocab->sentence_en }}"</div>
                                @if($vocab->audio_sentence_en)
                                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                                    <button onclick="playAudioRaw('{{ url('berkas/'.$vocab->audio_sentence_en) }}', this)" class="btn-play w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 border border-slate-200 text-xs">🔊</button>
                                @endif
                            </div>
                        </div>

                        <div class="w-full text-right" dir="rtl">
                            <div class="text-xs font-black text-blue-500 uppercase mb-2" dir="ltr">🇸🇦 العربية</div>
                            <div class="flex items-center justify-start gap-3 mb-2">
                                <div class="text-4xl font-bold text-blue-800 font-arabic">{{ $vocab->mufrodat_ar }}</div>
                                @if($vocab->audio_mufrodat_ar)
                                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                                    <button onclick="playAudioRaw('{{ url('berkas/'.$vocab->audio_mufrodat_ar) }}', this)" class="btn-play w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center border border-blue-200" dir="ltr">🔊</button>
                                @endif
                            </div>
                            <div class="flex items-start justify-start gap-2">
                                <div class="text-lg font-bold text-slate-600 font-arabic flex-1 leading-tight">{{ $vocab->jumlah_ar }}</div>
                                @if($vocab->audio_jumlah_ar)
                                    <!-- JALUR DIUBAH KE /BERKAS/ -->
                                    <button onclick="playAudioRaw('{{ url('berkas/'.$vocab->audio_jumlah_ar) }}', this)" class="btn-play w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0 border border-slate-200 text-xs" dir="ltr">🔊</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- ============================================== -->
    <!-- TAB 2: LATIHAN (SEKSI 1 & SEKSI 2) -->
    <!-- ============================================== -->
    <div id="tab-latihan" class="tab-content px-5 pt-6 relative">
        
        <!-- SEKSI 1: PILIHAN GANDA (20 SOAL) -->
        <div id="seksi-1" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 mb-6">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-3">
                <h3 class="font-black text-lg text-slate-800">Seksi 1: Pilihan Ganda</h3>
                <span class="bg-blue-100 text-blue-700 font-black text-xs px-3 py-1 rounded-full" id="pg-counter">1 / 20</span>
            </div>
            
            <div id="pg-question-area" class="min-h-[250px]">
                <!-- Konten Soal Di-inject JS -->
            </div>
        </div>

        <!-- LAYAR TRANSISI ANTAR SEKSI -->
        <div id="seksi-transisi" class="hidden bg-emerald-500 text-white p-8 rounded-3xl shadow-lg mb-6 text-center">
            <div class="text-6xl mb-4">🌟</div>
            <h3 class="font-black text-2xl mb-2">Luar Biasa!</h3>
            <p class="font-bold text-emerald-100 mb-6">Kamu berhasil menyelesaikan 20 Soal Pilihan Ganda. Mari lanjut ke Seksi Terakhir!</p>
            <button onclick="startSeksi2()" class="w-full bg-white text-emerald-600 font-black py-4 rounded-xl shadow-md active:scale-95 transition">MULAI MENCOCOKKAN KATA</button>
        </div>

        <!-- SEKSI 2: MATCH MAKING -->
        <div id="seksi-2" class="hidden bg-white p-6 rounded-3xl shadow-sm border border-slate-200 mb-6">
            <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-3">
                <h3 class="font-black text-lg text-slate-800">Seksi 2: Mencocokkan</h3>
                <span class="bg-amber-100 text-amber-700 font-black text-xs px-3 py-1 rounded-full" id="match-level-indicator">Level 1: Inggris</span>
            </div>
            
            <p class="text-xs font-bold text-slate-500 mb-4 text-center">Ketuk kata Asing, dengarkan audionya, lalu ketuk arti bahasa Indonesianya!</p>

            <div class="flex gap-4">
                <div class="flex-1 flex flex-col gap-2" id="match-col-foreign"></div>
                <div class="flex-1 flex flex-col gap-2" id="match-col-indo"></div>
            </div>
        </div>

    </div>

    <!-- ============================================== -->
    <!-- TAB 3: KLAIM POIN -->
    <!-- ============================================== -->
    <div id="tab-klaim" class="tab-content px-5 pt-6">
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-200 text-center">
            
            <!-- Lock Screen -->
            <div id="lock-screen">
                <div class="text-6xl mb-4">🔒</div>
                <h2 class="text-xl font-black text-slate-800 mb-2">Poin Terkunci</h2>
                <p class="text-sm font-bold text-slate-500">Selesaikan Seksi 1 (PG) dan Seksi 2 (Mencocokkan) untuk membuka form Klaim Poin!</p>
                <button onclick="switchTab('latihan')" class="mt-6 bg-blue-100 text-blue-700 font-bold px-6 py-3 rounded-xl w-full">Ke Menu Latihan</button>
            </div>

            <!-- Claim Form -->
            <div id="claim-screen" class="hidden">
                <div class="text-6xl mb-4">🏆</div>
                <h2 class="text-2xl font-black text-emerald-600 mb-2">Misi Selesai!</h2>
                <p class="text-sm font-bold text-slate-500 mb-6">Kamu hebat! Masukkan NIS / NISN kamu di bawah ini untuk mengambil <strong class="text-amber-500">+1 Poin Karakter</strong> Student Root.</p>
                
                <input type="number" id="nisn-input" placeholder="Ketik NIS / NISN..." class="w-full text-center text-xl font-black p-4 bg-slate-100 border-2 border-slate-200 rounded-xl mb-4 focus:outline-none focus:border-amber-400">
                
                <button onclick="submitClaim()" id="btn-submit-claim" class="w-full bg-amber-400 text-white font-black text-lg py-4 rounded-xl shadow-lg transition active:scale-95">
                    KLAIM POIN SEKARANG
                </button>
                <div id="claim-msg" class="mt-4 font-bold text-sm"></div>
            </div>

        </div>
    </div>

    <audio id="global-audio-player"></audio>

    <div class="bottom-nav">
        <div class="nav-item active" id="nav-belajar" onclick="switchTab('belajar')"><span>📚</span> Belajar</div>
        <div class="nav-item" id="nav-latihan" onclick="switchTab('latihan')"><span>🎮</span> Latihan</div>
        <div class="nav-item" id="nav-klaim" onclick="switchTab('klaim')"><span>🎁</span> Klaim Poin</div>
    </div>

    <!-- ============================================== -->
    <!-- LOGIKA JAVASCRIPT GAME ENGINE -->
    <!-- ============================================== -->
    <script>
        const rawVocabs = @json($week->vocabs);
        const vocabs = Array.isArray(rawVocabs) ? rawVocabs : Object.values(rawVocabs);
        
        const globalAudio = document.getElementById('global-audio-player');
        
        // JALUR DIUBAH KE /BERKAS/ AGAR ENGINE JAVASCRIPT BISA MEMUTAR AUDIO KUIS
        const storageUrl = "{{ url('berkas/') }}/";

        // --- WEB AUDIO API (SUARA SFX OTOMATIS) ---
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        const audioCtx = new AudioContext();

        function playDingSound() {
            if(audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            osc.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // Nada A5
            osc.frequency.exponentialRampToValueAtTime(1108.73, audioCtx.currentTime + 0.1); 
            
            gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.5, audioCtx.currentTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
            
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.5);
        }

        function playBuzzerSound() {
            if(audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            osc.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(150, audioCtx.currentTime); 
            osc.frequency.exponentialRampToValueAtTime(100, audioCtx.currentTime + 0.3);
            
            gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.05);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            
            osc.start(audioCtx.currentTime);
            osc.stop(audioCtx.currentTime + 0.3);
        }

        // --- SELEBRASI JENG JENG (VICTORY CHORD) ---
        function playVictorySound() {
            if(audioCtx.state === 'suspended') audioCtx.resume();
            const now = audioCtx.currentTime;

            // "Jeng" 1 (G Major chord) - Pendek
            [392.00, 493.88, 587.33].forEach(freq => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'triangle';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, now);
                gain.gain.linearRampToValueAtTime(0.15, now + 0.05);
                gain.gain.linearRampToValueAtTime(0, now + 0.2);
                osc.start(now);
                osc.stop(now + 0.2);
            });

            // "Jeng!" 2 (C Major chord) - Panjang & Megah
            [523.25, 659.25, 783.99].forEach(freq => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'triangle';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, now + 0.2);
                gain.gain.linearRampToValueAtTime(0.2, now + 0.25);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 1.5);
                osc.start(now + 0.2);
                osc.stop(now + 1.5);
            });
        }

        // Global State
        let pgQuestions = [];
        let currentPgIndex = 0;
        let isPgDone = false;
        
        let matchLevel = 1;
        let matchPairsLeft = 0;
        let selectedForeign = null;
        let selectedIndo = null;
        let isMatchDone = false;

        document.addEventListener('DOMContentLoaded', () => {
            if(vocabs.length > 0) {
                generatePgBank(); 
                renderPgQuestion();
            }
        });

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            document.getElementById('tab-' + tabId).classList.add('active');
            document.getElementById('nav-' + tabId).classList.add('active');
        }

        function flipCard(innerEl) {
            innerEl.closest('.flip-card').classList.toggle('flipped');
            globalAudio.pause(); 
            document.querySelectorAll('.btn-play').forEach(b => b.classList.remove('playing'));
        }

        function playAudioRaw(url, btnElement = null) {
            globalAudio.src = url;
            if(btnElement) {
                document.querySelectorAll('.btn-play').forEach(b => b.classList.remove('playing'));
                btnElement.classList.add('playing');
                globalAudio.onended = () => btnElement.classList.remove('playing');
                globalAudio.onerror = () => btnElement.classList.remove('playing');
            }
            globalAudio.play().catch(e => {
                if(btnElement) btnElement.classList.remove('playing');
            });
        }

        // --- ENGINE SEKSI 1: PILIHAN GANDA LOGIKA KETAT (KESETARAAN LEVEL) ---
        function generatePgBank() {
            pgQuestions = [];
            for(let i=0; i<20; i++) {
                let v = vocabs[Math.floor(Math.random() * vocabs.length)];
                let lang = Math.random() > 0.5 ? 'en' : 'ar';
                
                let varList = [1];
                // Var 2 (Kalimat): Wajib punya audio kalimat & teks kalimat
                if (lang === 'en' && v.audio_sentence_en && v.sentence_en) varList.push(2);
                if (lang === 'ar' && v.audio_jumlah_ar && v.jumlah_ar) varList.push(2);
                
                // Var 3 (Kosakata Audio): Wajib punya audio kosakata
                if (lang === 'en' && v.audio_vocab_en) varList.push(3);
                if (lang === 'ar' && v.audio_mufrodat_ar) varList.push(3);
                
                let variation = varList[Math.floor(Math.random() * varList.length)];
                pgQuestions.push({ vocab: v, lang: lang, variation: variation });
            }
        }

        function renderPgQuestion() {
            if(currentPgIndex >= 20) {
                isPgDone = true;
                confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } }); 
                document.getElementById('seksi-1').classList.add('hidden');
                document.getElementById('seksi-transisi').classList.remove('hidden');
                return;
            }

            document.getElementById('pg-counter').innerText = `${currentPgIndex + 1} / 20`;
            let q = pgQuestions[currentPgIndex];
            let html = ``;

            let options = [q.vocab];
            let distractors = vocabs.filter(x => x.id != q.vocab.id);
            
            // Filter Pengecoh sesuai level soal
            if (q.variation === 2) {
                distractors = distractors.filter(x => q.lang === 'en' ? x.sentence_en : x.jumlah_ar);
            } else if (q.variation === 3) {
                distractors = distractors.filter(x => q.lang === 'en' ? x.audio_vocab_en : x.audio_mufrodat_ar);
            }

            distractors = distractors.sort(() => 0.5 - Math.random()).slice(0, 3);
            options = [...options, ...distractors].sort(() => 0.5 - Math.random()); 

            if(q.variation === 1) {
                // VAR 1: Teks Kosakata Asing -> Teks Arti Indo
                let foreignWord = q.lang === 'en' ? q.vocab.vocab_en : q.vocab.mufrodat_ar;
                let fontClass = q.lang === 'ar' ? 'font-arabic text-5xl leading-tight' : 'text-3xl';
                
                html += `<p class="text-xs font-bold text-slate-500 mb-4 text-center">Apa arti kata di bawah ini?</p>`;
                html += `<div class="text-center font-black text-blue-600 mb-8 ${fontClass}">${foreignWord}</div>`;
                html += `<div class="flex flex-col gap-3">`;
                options.forEach(opt => {
                    html += `<button onclick="checkPgAnswer(${opt.id === q.vocab.id}, this)" class="pg-btn capitalize">${opt.kosakata_id}</button>`;
                });
                html += `</div>`;
            } 
            else if (q.variation === 2) {
                // VAR 2: Audio Kalimat -> Teks Kalimat (Level Kalimat Setara)
                let audioUrl = q.lang === 'en' ? storageUrl + q.vocab.audio_sentence_en : storageUrl + q.vocab.audio_jumlah_ar;
                html += `<p class="text-xs font-bold text-slate-500 mb-6 text-center">Dengarkan kalimat berikut.<br>Pilih teks kalimat yang sesuai dengan audio!</p>`;
                html += `<div class="flex justify-center mb-8">
                            <button onclick="playAudioRaw('${audioUrl}', this)" class="btn-play w-20 h-20 bg-amber-100 text-amber-600 border-4 border-amber-300 rounded-full text-3xl shadow-md flex items-center justify-center">🔊</button>
                         </div>`;
                html += `<div class="flex flex-col gap-3">`;
                
                let fontClassOpt = q.lang === 'ar' ? 'font-arabic text-xl text-right' : 'text-sm italic';
                let dirOpt = q.lang === 'ar' ? 'dir="rtl"' : '';

                options.forEach(opt => {
                    let optText = q.lang === 'en' ? opt.sentence_en : opt.jumlah_ar;
                    html += `<button onclick="checkPgAnswer(${opt.id === q.vocab.id}, this)" class="pg-btn ${fontClassOpt}" ${dirOpt}>${optText}</button>`;
                });
                html += `</div>`;
            }
            else if (q.variation === 3) {
                // VAR 3: Teks Kosakata Indo -> Audio Kosakata Asing (Level Kata Setara)
                html += `<p class="text-xs font-bold text-slate-500 mb-4 text-center">Dengarkan dan pilih pelafalan yang tepat untuk kata:</p>`;
                html += `<div class="text-center font-black text-3xl text-slate-800 mb-8 capitalize">"${q.vocab.kosakata_id}"</div>`;
                
                html += `<div class="grid grid-cols-2 gap-3">`;
                options.forEach((opt, idx) => {
                    let aud = q.lang === 'en' ? opt.audio_vocab_en : opt.audio_mufrodat_ar;
                    let audPath = aud ? storageUrl + aud : ''; 
                    
                    html += `<div class="flex items-center bg-slate-50 border-2 border-slate-200 rounded-xl p-2 gap-2">
                                <button onclick="playAudioRaw('${audPath}', this)" class="btn-play w-12 h-12 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-lg">🔊</button>
                                <button onclick="checkPgAnswer(${opt.id === q.vocab.id}, this)" class="flex-1 font-black text-slate-500 text-sm text-left py-2">Opsi ${['A','B','C','D'][idx]}</button>
                             </div>`;
                });
                html += `</div>`;
            }

            document.getElementById('pg-question-area').innerHTML = html;
        }

        function checkPgAnswer(isCorrect, btnElement) {
            globalAudio.pause(); 
            let visualBtn = btnElement.tagName === 'BUTTON' && btnElement.classList.contains('pg-btn') ? btnElement : btnElement.parentElement;
            
            if(isCorrect) {
                playDingSound(); 
                visualBtn.classList.add('correct', 'border-emerald-500', 'bg-emerald-100', 'text-emerald-700');
                
                let rect = visualBtn.getBoundingClientRect();
                let xPos = (rect.left + rect.width / 2) / window.innerWidth;
                let yPos = (rect.top + rect.height / 2) / window.innerHeight;
                confetti({ particleCount: 30, spread: 50, origin: { x: xPos, y: yPos }, colors: ['#10b981', '#34d399', '#fef08a'] });

                document.getElementById('pg-question-area').style.pointerEvents = 'none';
                
                setTimeout(() => {
                    currentPgIndex++;
                    document.getElementById('pg-question-area').style.pointerEvents = 'auto';
                    renderPgQuestion();
                }, 1000);
            } else {
                playBuzzerSound(); 
                visualBtn.classList.add('wrong', 'border-red-500', 'bg-red-100', 'text-red-700');
                visualBtn.style.animation = "shake 0.4s";
                setTimeout(() => {
                    visualBtn.classList.remove('wrong', 'border-red-500', 'bg-red-100', 'text-red-700');
                    visualBtn.style.animation = "none";
                }, 600);
            }
        }

        // --- ENGINE SEKSI 2: MATCH MAKING ---
        function startSeksi2() {
            document.getElementById('seksi-transisi').classList.add('hidden');
            document.getElementById('seksi-2').classList.remove('hidden');
            matchLevel = 1; 
            generateMatchLevel();
        }

        function generateMatchLevel() {
            let pool = [...vocabs].sort(() => 0.5 - Math.random()).slice(0, 5);
            matchPairsLeft = pool.length;
            
            let arrForeign = [...pool].sort(() => 0.5 - Math.random());
            let arrIndo = [...pool].sort(() => 0.5 - Math.random());
            
            let htmlForeign = ''; let htmlIndo = '';
            
            document.getElementById('match-level-indicator').innerText = matchLevel === 1 ? 'Level 1: Inggris' : 'Level 2: Arab';

            arrForeign.forEach(v => {
                let text = matchLevel === 1 ? v.vocab_en : v.mufrodat_ar;
                let audioUrl = matchLevel === 1 ? v.audio_vocab_en : v.audio_mufrodat_ar; 
                let fontClass = matchLevel === 2 ? 'font-arabic text-2xl' : 'text-base';
                
                htmlForeign += `<button onclick="selectMatch('foreign', '${v.id}', '${storageUrl + audioUrl}', this)" class="match-btn ${fontClass}" id="btn-f-${v.id}">${text}</button>`;
            });

            arrIndo.forEach(v => {
                htmlIndo += `<button onclick="selectMatch('indo', '${v.id}', null, this)" class="match-btn capitalize" id="btn-i-${v.id}">${v.kosakata_id}</button>`;
            });

            document.getElementById('match-col-foreign').innerHTML = htmlForeign;
            document.getElementById('match-col-indo').innerHTML = htmlIndo;
        }

        function selectMatch(type, id, audioUrl, btn) {
            if(btn.classList.contains('matched')) return;

            if(type === 'foreign' && audioUrl && audioUrl !== storageUrl+'null' && audioUrl !== storageUrl) {
                playAudioRaw(audioUrl, null);
            }

            document.querySelectorAll(`[id^='btn-${type.charAt(0)}-']`).forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');

            if(type === 'foreign') selectedForeign = { id: id, el: btn };
            if(type === 'indo') selectedIndo = { id: id, el: btn };

            if(selectedForeign && selectedIndo) {
                if(selectedForeign.id === selectedIndo.id) {
                    playDingSound(); 
                    
                    let rect = selectedIndo.el.getBoundingClientRect();
                    confetti({ particleCount: 20, spread: 40, origin: { x: (rect.left + rect.width / 2) / window.innerWidth, y: (rect.top + rect.height / 2) / window.innerHeight }, colors: ['#10b981'] });

                    selectedForeign.el.classList.replace('selected', 'matched');
                    selectedIndo.el.classList.replace('selected', 'matched');
                    selectedForeign = null; selectedIndo = null;
                    matchPairsLeft--;

                    if(matchPairsLeft <= 0) {
                        setTimeout(() => {
                            if(matchLevel === 1) {
                                matchLevel = 2; 
                                generateMatchLevel();
                            } else {
                                isMatchDone = true;
                                unlockClaim();
                            }
                        }, 1000);
                    }
                } else {
                    playBuzzerSound(); 
                    selectedForeign.el.classList.replace('selected', 'wrong');
                    selectedIndo.el.classList.replace('selected', 'wrong');
                    let tF = selectedForeign.el; let tI = selectedIndo.el;
                    selectedForeign = null; selectedIndo = null;
                    setTimeout(() => {
                        tF.classList.remove('wrong'); tI.classList.remove('wrong');
                    }, 500);
                }
            }
        }

        // --- KUNCI KLAIM & SELEBRASI JENG JENG ---
        function unlockClaim() {
            playVictorySound();
            confetti({ particleCount: 250, spread: 150, origin: { y: 0.5 }, zIndex: 9999, colors: ['#f59e0b', '#fbbf24', '#10b981', '#3b82f6'] }); 

            document.getElementById('seksi-2').innerHTML = `<div class="text-center p-8"><div class="text-6xl mb-4">🏆</div><h3 class="font-black text-emerald-600 text-2xl">Latihan Selesai!</h3><p class="font-bold text-slate-500 mt-2">Buka tab Klaim Poin sekarang.</p></div>`;
            
            document.getElementById('lock-screen').classList.add('hidden');
            document.getElementById('claim-screen').classList.remove('hidden');
            
            let navKlaim = document.getElementById('nav-klaim');
            navKlaim.innerHTML = `<span>🎁</span> KLAIM (!)`;
            navKlaim.style.color = '#eab308';
            navKlaim.classList.add('animate-bounce');
        }

        // --- SUBMIT AJAX ---
        function submitClaim() {
            let nisn = document.getElementById('nisn-input').value;
            let msgBox = document.getElementById('claim-msg');
            let btn = document.getElementById('btn-submit-claim');
            
            if(!nisn) { msgBox.innerHTML = '<span class="text-red-500">NISN tidak boleh kosong!</span>'; return; }

            btn.innerHTML = 'Memproses...'; btn.disabled = true;

            fetch(`{{ route('bee.buku-saku.claim', $week->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ nisn: nisn })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    playDingSound(); 
                    confetti({ particleCount: 200, spread: 120, origin: { y: 0.4 }, zIndex: 9999 }); 
                    msgBox.innerHTML = `<div class="bg-emerald-100 border-2 border-emerald-300 text-emerald-700 p-4 rounded-xl text-lg mt-4">🎉 ${data.message}</div>`;
                    document.getElementById('nisn-input').style.display = 'none';
                    btn.style.display = 'none';
                } else {
                    playBuzzerSound(); 
                    msgBox.innerHTML = `<span class="text-red-500">⚠️ ${data.message}</span>`;
                    btn.innerHTML = 'KLAIM POIN SEKARANG'; btn.disabled = false;
                }
            })
            .catch(err => {
                msgBox.innerHTML = '<span class="text-red-500">Terjadi kesalahan jaringan. Coba lagi.</span>';
                btn.innerHTML = 'KLAIM POIN SEKARANG'; btn.disabled = false;
            });
        }
    </script>
</body>
</html>