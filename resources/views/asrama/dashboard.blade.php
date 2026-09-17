<x-app-layout>
    <!-- SUNTIKAN TAILWIND CDN & CUSTOM CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }
        .hover-lift { transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); }
        .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .bg-pattern { background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; }
        
        /* Animasi Masuk / Pindah Tab */
        @keyframes fadeUpTab { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeUpTab 0.4s ease-out forwards; }
        
        .animate-fade-up { animation: fadeUpTab 0.6s ease-out forwards; opacity: 0; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
    </style>

    <div class="py-8 bg-slate-50 bg-pattern min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛏️ Dashboard Inspeksi Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Pantau hasil sidak kebersihan kamar putra &amp; putri</p>
                </div>
            </div>

            <!-- ================= KONTROL NAVIGASI TAB ================= -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8 animate-fade-up">
                <!-- Tombol Putra -->
                <button id="btn-putra" onclick="switchTabAsrama('putra')" class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-lg bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-blue-500/30 hover:scale-105 transform border border-transparent">
                    👨‍👦 Asrama Putra
                </button>

                <!-- Tombol Putri -->
                <button id="btn-putri" onclick="switchTabAsrama('putri')" class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-rose-50 hover:text-rose-600 hover:scale-105 transform">
                    👩‍👧 Asrama Putri
                </button>
            </div>

            <!-- ================================================================ -->
            <!-- TAB 1: ZONA ASRAMA PUTRA (IKHWAN)                                -->
            <!-- ================================================================ -->
            <div id="tab-putra" class="tab-content active">
                
                <!-- STATISTIK SPESIFIK PUTRA -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-blue-100">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">🏢</div>
                        <div>
                            <p class="text-xs font-black text-blue-500 uppercase tracking-widest mb-1">Kamar Putra</p>
                            <p class="text-4xl font-black text-gray-800">{{ $totalKamarPutra }}</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-blue-500/30 text-white">🏢</div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-blue-100 delay-100">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">📝</div>
                        <div>
                            <p class="text-xs font-black text-blue-500 uppercase tracking-widest mb-1">Sidak Putra</p>
                            <p class="text-4xl font-black text-gray-800">{{ $totalInspeksiPutra }}<span class="text-xl text-gray-400 ml-1">x</span></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-blue-500/30 text-white">📝</div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-amber-100 delay-200">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">📅</div>
                        <div>
                            <p class="text-xs font-black text-amber-500 uppercase tracking-widest mb-1">Periode</p>
                            <p class="text-2xl font-black text-gray-800 uppercase leading-tight">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F') }}<br><span class="text-amber-500">{{ \Carbon\Carbon::now()->year }}</span></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-amber-500/30 text-white">📅</div>
                    </div>
                </div>

                <!-- Highlight Putra -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Terbersih Putra -->
                    <div class="rounded-[2.5rem] bg-gradient-to-br from-emerald-400 to-teal-600 p-1 shadow-2xl shadow-emerald-500/20 hover-lift">
                        <div class="bg-white/95 backdrop-blur-xl rounded-[2.3rem] h-full p-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-2xl">🏆</div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-800">Top 3 Terbersih (Putra)</h3>
                                    <p class="text-xs font-bold text-emerald-600">Rata-rata skor tertinggi</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-3">
                                @forelse($putraTerbersih as $index => $k)
                                    <div class="group flex items-center gap-4 p-3 rounded-2xl border-2 {{ $index == 0 ? 'bg-gradient-to-r from-amber-50 to-yellow-100 border-amber-200' : 'bg-gray-50 border-transparent hover:border-emerald-200 hover:bg-emerald-50' }} transition duration-300">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-white text-lg shadow-md {{ $index == 0 ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : ($index == 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' : 'bg-gradient-to-br from-orange-600 to-amber-800') }} group-hover:scale-110 transition">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-black text-gray-800 text-lg leading-none mb-1">{{ $k->nama_kamar }}</h4>
                                            <p class="text-[11px] font-bold text-gray-500 flex items-center gap-1">👤 {{ $k->nama_musyrif ?? '-' }}</p>
                                        </div>
                                        <div class="text-right bg-white px-3 py-1 rounded-xl shadow-sm border border-gray-100 group-hover:border-emerald-200">
                                            <div class="font-black text-xl text-emerald-600">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                        <p class="font-bold text-gray-400 text-sm">Belum ada data sidak putra bulan ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Terkotor Putra -->
                    <div class="rounded-[2.5rem] bg-gradient-to-br from-red-500 to-rose-700 p-1 shadow-2xl shadow-red-500/20 hover-lift">
                        <div class="bg-white/95 backdrop-blur-xl rounded-[2.3rem] h-full p-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-2xl">⚠️</div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-800">Perhatian Ekstra (Putra)</h3>
                                    <p class="text-xs font-bold text-red-600">Rata-rata di bawah {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}%</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-3">
                                @forelse($putraTerkotor as $index => $k)
                                    <div class="group flex items-center gap-4 p-3 rounded-2xl border-2 bg-red-50/50 border-red-100 hover:bg-red-100 transition duration-300">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-red-600 text-lg shadow-inner bg-white group-hover:scale-110 transition">
                                            🔻
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-black text-gray-800 text-lg leading-none mb-1">{{ $k->nama_kamar }}</h4>
                                            <p class="text-[11px] font-bold text-gray-500 flex items-center gap-1">👤 {{ $k->nama_musyrif ?? '-' }}</p>
                                        </div>
                                        <div class="text-right bg-white px-3 py-1 rounded-xl shadow-sm border border-red-100 group-hover:border-red-300">
                                            <div class="font-black text-xl text-red-600">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                        <p class="font-bold text-emerald-600 text-sm">✅ Semua kamar putra bernilai {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}% ke atas bulan ini — tidak ada yang masuk perhatian ekstra.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Putra Full -->
                <div class="glass-card rounded-[2rem] overflow-hidden flex flex-col hover-lift">
                    <div class="bg-gradient-to-r from-blue-800 to-indigo-900 p-5 flex items-center justify-between">
                        <h3 class="text-white font-black text-lg tracking-wide">📊 Klasemen Seluruh Kamar Putra</h3>
                    </div>
                    <div class="overflow-x-auto p-4 flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-center">Rank</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider">Kamar & Musyrif</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-center">Jumlah Sidak</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-right">Skor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($rankingPutra as $index => $kamar)
                                <tr class="hover:bg-blue-50/50 transition group">
                                    <td class="py-4 px-2 font-black text-gray-500 text-center w-16">
                                        @if($kamar->jumlah_sidak > 0)
                                            <span class="{{ $index < 3 ? 'text-blue-600 bg-blue-100 px-3 py-1 rounded-lg' : '' }}">#{{ $index + 1 }}</span>
                                        @else - @endif
                                    </td>
                                    <td class="py-4 px-2">
                                        <div class="font-black text-gray-800 text-base group-hover:text-blue-700 transition">{{ $kamar->nama_kamar }}</div>
                                        <div class="text-xs font-bold text-gray-400">👤 {{ $kamar->nama_musyrif ?? 'Belum Diatur' }}</div>
                                    </td>
                                    <td class="py-4 px-2 text-center">
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200">{{ $kamar->jumlah_sidak }}x</span>
                                    </td>
                                    <td class="py-4 px-2 text-right">
                                        <div class="font-black text-xl {{ $kamar->rata_rata_skor >= 80 ? 'text-emerald-600' : ($kamar->rata_rata_skor >= 60 ? 'text-amber-500' : 'text-red-500') }}">
                                            {{ $kamar->jumlah_sidak > 0 ? number_format($kamar->rata_rata_skor, 1) : 'Belum Dinilai' }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-8 font-bold text-gray-400">Tidak ada data kamar putra.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================================================================ -->
            <!-- TAB 2: ZONA ASRAMA PUTRI (AKHWAT)                                -->
            <!-- ================================================================ -->
            <div id="tab-putri" class="tab-content">
                
                <!-- STATISTIK SPESIFIK PUTRI -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-rose-100">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">🏢</div>
                        <div>
                            <p class="text-xs font-black text-rose-500 uppercase tracking-widest mb-1">Kamar Putri</p>
                            <p class="text-4xl font-black text-gray-800">{{ $totalKamarPutri }}</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-rose-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-rose-500/30 text-white">🏢</div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-rose-100 delay-100">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">📝</div>
                        <div>
                            <p class="text-xs font-black text-rose-500 uppercase tracking-widest mb-1">Sidak Putri</p>
                            <p class="text-4xl font-black text-gray-800">{{ $totalInspeksiPutri }}<span class="text-xl text-gray-400 ml-1">x</span></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-rose-400 to-rose-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-rose-500/30 text-white">📝</div>
                    </div>

                    <div class="glass-card rounded-3xl p-6 flex items-center justify-between hover-lift relative overflow-hidden border-amber-100 delay-200">
                        <div class="absolute -right-4 -top-4 text-8xl opacity-5">📅</div>
                        <div>
                            <p class="text-xs font-black text-amber-500 uppercase tracking-widest mb-1">Periode</p>
                            <p class="text-2xl font-black text-gray-800 uppercase leading-tight">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F') }}<br><span class="text-amber-500">{{ \Carbon\Carbon::now()->year }}</span></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg shadow-amber-500/30 text-white">📅</div>
                    </div>
                </div>

                <!-- Highlight Putri -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Terbersih Putri -->
                    <div class="rounded-[2.5rem] bg-gradient-to-br from-emerald-400 to-teal-600 p-1 shadow-2xl shadow-emerald-500/20 hover-lift">
                        <div class="bg-white/95 backdrop-blur-xl rounded-[2.3rem] h-full p-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-2xl">🏆</div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-800">Top 3 Terbersih (Putri)</h3>
                                    <p class="text-xs font-bold text-emerald-600">Rata-rata skor tertinggi</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-3">
                                @forelse($putriTerbersih as $index => $k)
                                    <div class="group flex items-center gap-4 p-3 rounded-2xl border-2 {{ $index == 0 ? 'bg-gradient-to-r from-amber-50 to-yellow-100 border-amber-200' : 'bg-gray-50 border-transparent hover:border-emerald-200 hover:bg-emerald-50' }} transition duration-300">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-white text-lg shadow-md {{ $index == 0 ? 'bg-gradient-to-br from-amber-400 to-yellow-500' : ($index == 1 ? 'bg-gradient-to-br from-gray-300 to-gray-500' : 'bg-gradient-to-br from-orange-600 to-amber-800') }} group-hover:scale-110 transition">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-black text-gray-800 text-lg leading-none mb-1">{{ $k->nama_kamar }}</h4>
                                            <p class="text-[11px] font-bold text-gray-500 flex items-center gap-1">👤 {{ $k->nama_musyrif ?? '-' }}</p>
                                        </div>
                                        <div class="text-right bg-white px-3 py-1 rounded-xl shadow-sm border border-gray-100 group-hover:border-emerald-200">
                                            <div class="font-black text-xl text-emerald-600">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                        <p class="font-bold text-gray-400 text-sm">Belum ada data sidak putri bulan ini.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Terkotor Putri -->
                    <div class="rounded-[2.5rem] bg-gradient-to-br from-red-500 to-rose-700 p-1 shadow-2xl shadow-red-500/20 hover-lift">
                        <div class="bg-white/95 backdrop-blur-xl rounded-[2.3rem] h-full p-8">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center text-2xl">⚠️</div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-800">Perhatian Ekstra (Putri)</h3>
                                    <p class="text-xs font-bold text-red-600">Rata-rata di bawah {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}%</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-3">
                                @forelse($putriTerkotor as $index => $k)
                                    <div class="group flex items-center gap-4 p-3 rounded-2xl border-2 bg-red-50/50 border-red-100 hover:bg-red-100 transition duration-300">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-red-600 text-lg shadow-inner bg-white group-hover:scale-110 transition">
                                            🔻
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-black text-gray-800 text-lg leading-none mb-1">{{ $k->nama_kamar }}</h4>
                                            <p class="text-[11px] font-bold text-gray-500 flex items-center gap-1">👤 {{ $k->nama_musyrif ?? '-' }}</p>
                                        </div>
                                        <div class="text-right bg-white px-3 py-1 rounded-xl shadow-sm border border-red-100 group-hover:border-red-300">
                                            <div class="font-black text-xl text-red-600">{{ number_format($k->rata_rata_skor, 1) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                        <p class="font-bold text-emerald-600 text-sm">✅ Semua kamar putri bernilai {{ \App\Http\Controllers\AsramaPenilaianController::BATAS_TERKOTOR_PERSEN }}% ke atas bulan ini — tidak ada yang masuk perhatian ekstra.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Putri Full -->
                <div class="glass-card rounded-[2rem] overflow-hidden flex flex-col hover-lift">
                    <div class="bg-gradient-to-r from-rose-600 to-pink-800 p-5 flex items-center justify-between">
                        <h3 class="text-white font-black text-lg tracking-wide">📊 Klasemen Seluruh Kamar Putri</h3>
                    </div>
                    <div class="overflow-x-auto p-4 flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-center">Rank</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider">Kamar & Musyrifah</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-center">Jumlah Sidak</th>
                                    <th class="py-3 px-2 text-gray-400 font-extrabold text-xs uppercase tracking-wider text-right">Skor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($rankingPutri as $index => $kamar)
                                <tr class="hover:bg-rose-50/50 transition group">
                                    <td class="py-4 px-2 font-black text-gray-500 text-center w-16">
                                        @if($kamar->jumlah_sidak > 0)
                                            <span class="{{ $index < 3 ? 'text-rose-600 bg-rose-100 px-3 py-1 rounded-lg' : '' }}">#{{ $index + 1 }}</span>
                                        @else - @endif
                                    </td>
                                    <td class="py-4 px-2">
                                        <div class="font-black text-gray-800 text-base group-hover:text-rose-600 transition">{{ $kamar->nama_kamar }}</div>
                                        <div class="text-xs font-bold text-gray-400">👤 {{ $kamar->nama_musyrif ?? 'Belum Diatur' }}</div>
                                    </td>
                                    <td class="py-4 px-2 text-center">
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200">{{ $kamar->jumlah_sidak }}x</span>
                                    </td>
                                    <td class="py-4 px-2 text-right">
                                        <div class="font-black text-xl {{ $kamar->rata_rata_skor >= 80 ? 'text-emerald-600' : ($kamar->rata_rata_skor >= 60 ? 'text-amber-500' : 'text-red-500') }}">
                                            {{ $kamar->jumlah_sidak > 0 ? number_format($kamar->rata_rata_skor, 1) : 'Belum Dinilai' }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-8 font-bold text-gray-400">Tidak ada data kamar putri.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- JAVASCRIPT UNTUK PINDAH TAB -->
    <script>
        function switchTabAsrama(zona) {
            const tabPutra = document.getElementById('tab-putra');
            const tabPutri = document.getElementById('tab-putri');
            const btnPutra = document.getElementById('btn-putra');
            const btnPutri = document.getElementById('btn-putri');

            tabPutra.classList.remove('active');
            tabPutri.classList.remove('active');

            if (zona === 'putra') {
                tabPutra.classList.add('active');
                btnPutra.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-lg bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-blue-500/30 hover:scale-105 transform border border-transparent";
                btnPutri.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-rose-50 hover:text-rose-600 hover:scale-105 transform";
            } 
            else if (zona === 'putri') {
                tabPutri.classList.add('active');
                btnPutri.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-lg bg-gradient-to-r from-rose-600 to-pink-700 text-white shadow-rose-500/30 hover:scale-105 transform border border-transparent";
                btnPutra.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-4 rounded-2xl font-black text-lg sm:text-xl transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:scale-105 transform";
            }
        }
    </script>
</x-app-layout>