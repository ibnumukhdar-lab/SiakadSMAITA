<x-app-layout>
    <div class="py-8 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📊 Dashboard Karakter &amp; Rekap Poin</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Rekap poin Student Root seluruh grup &amp; siswa</p>
                </div>
                @can('buka-menu-kelola-akun')
                <button onclick="openSyncModal()" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sinkronkan Data
                </button>
                @endcan
            </div>

            <!-- CUSTOM CSS UNTUK KARTU AGREGATOR (TANPA TAILWIND) -->
            <style>
                .aggregator-container {
                    margin-bottom: 3rem;
                }
                .aggregator-title {
                    font-size: 24px;
                    font-weight: 900;
                    text-transform: uppercase;
                    text-align: center;
                    letter-spacing: 2px;
                    color: #1e3a8a;
                    margin-bottom: 2rem;
                    text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
                }
                .aggregator-grid {
                    display: grid;
                    /* Memaksa 5 kolom sejajar di layar besar */
                    grid-template-columns: repeat(5, 1fr);
                    gap: 20px;
                }
                /* Responsif untuk HP dan Tablet */
                @media (max-width: 1024px) {
                    .aggregator-grid { grid-template-columns: repeat(3, 1fr); }
                }
                @media (max-width: 768px) {
                    .aggregator-grid { grid-template-columns: repeat(2, 1fr); }
                }
                
                .aggregator-card {
                    background: #ffffff;
                    border-radius: 16px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                    padding: 24px 15px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                    border: 1px solid #f1f5f9;
                    border-top-width: 6px;
                    border-top-style: solid;
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                }
                .aggregator-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                }
                
                .aggregator-avatar-wrapper {
                    position: relative;
                    margin-bottom: 20px;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                }
                .aggregator-avatar {
                    width: 75px;
                    height: 75px;
                    border-radius: 50%;
                    object-fit: cover;
                    border-width: 3px;
                    border-style: solid;
                    background-color: #f8fafc;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .aggregator-avatar-fallback {
                    width: 75px;
                    height: 75px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: 900;
                    color: #ffffff;
                    border-width: 3px;
                    border-style: solid;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .aggregator-mentor-name {
                    position: absolute;
                    bottom: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #ffffff;
                    color: #334155;
                    font-size: 11px;
                    font-weight: 800;
                    padding: 3px 10px;
                    border-radius: 20px;
                    border: 1px solid #e2e8f0;
                    white-space: nowrap;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    z-index: 2;
                    max-width: 95%;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                .aggregator-group-name {
                    font-size: 15px;
                    font-weight: 900;
                    text-transform: uppercase;
                    margin: 0 0 15px 0;
                    line-height: 1.3;
                    width: 100%;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                .aggregator-points-box {
                    background-color: #f8fafc;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    width: 100%;
                    padding: 12px 0;
                    margin-top: auto;
                }
                .aggregator-points-value {
                    font-size: 28px;
                    font-weight: 900;
                    color: #0f172a;
                    margin: 0 0 2px 0;
                    line-height: 1;
                }
                .aggregator-points-label {
                    font-size: 10px;
                    font-weight: 800;
                    text-transform: uppercase;
                    color: #64748b;
                    letter-spacing: 1px;
                    margin: 0;
                }
            </style>

            <!-- STUDENT ROOT AGREGATOR SECTION -->
            <div class="aggregator-container">
                <h2 class="aggregator-title">STUDENT ROOT AGREGATOR</h2>
                
                <div class="aggregator-grid">
                    @forelse($group_points as $grup)
                    
                    @php
                        // Ambil variabel dengan aman
                        $warna = $grup->warna_grup ?? '#1e3a8a';
                        $mentorName = $grup->mentor->name ?? $grup->nama_mentor ?? 'Tanpa Mentor';
                        $mentorAvatar = $grup->mentor->avatar ?? $grup->avatar_mentor ?? null;
                    @endphp

                    <div class="aggregator-card" style="border-top-color: {{ $warna }};">
                        
                        <!-- Area Foto & Nama Mentor -->
                        <div class="aggregator-avatar-wrapper">
                            @if($mentorAvatar)
                                <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                                <img src="{{ url('berkas/' . $mentorAvatar) }}" alt="Foto Mentor" class="aggregator-avatar" style="border-color: {{ $warna }};">
                            @else
                                <div class="aggregator-avatar-fallback" style="background-color: {{ $warna }}; border-color: {{ $warna }};">
                                    {{ substr($mentorName, 0, 1) }}
                                </div>
                            @endif
                            
                            <div class="aggregator-mentor-name" title="{{ $mentorName }}">{{ $mentorName }}</div>
                        </div>
                        
                        <!-- Nama Grup -->
                        <h4 class="aggregator-group-name" style="color: {{ $warna }};" title="{{ $grup->nama_grup ?? 'Grup' }}">
                            {{ $grup->nama_grup ?? 'Nama Grup' }}
                        </h4>
                        
                        <!-- Box Poin -->
                        <div class="aggregator-points-box">
                            <p class="aggregator-points-value">{{ number_format($grup->total_poin ?? 0, 0, ',', '.') }}</p>
                            <p class="aggregator-points-label">Total Points</p>
                        </div>

                    </div>
                    @empty
                    <div style="grid-column: 1 / -1; padding: 40px; text-align: center; background: #fff; border-radius: 15px; border: 1px dashed #ccc;">
                        <p style="color: #888; font-weight: bold; margin: 0;">Belum ada grup yang aktif atau terbentuk.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- LEADERBOARD SECTION (Top 5) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 mt-4 pt-8 border-t border-gray-200">
                
                <!-- Top 5 Prestasi (Poin Positif) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="bg-green-50/70 px-5 py-4 border-b border-slate-100">
                        <h3 class="text-[15px] font-bold text-green-800">🏆 Top 5 Prestasi Tertinggi</h3>
                        <p class="text-xs text-green-600 mt-0.5">Siswa dengan akumulasi poin positif terbanyak</p>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-gray-100">
                            @forelse($top_prestasi as $index => $siswa)
                                <li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50/70 transition">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-sm">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">{{ $siswa->nama_lengkap }}</p>
                                            <p class="text-xs text-gray-500">Kelas {{ $siswa->kelas }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">+{{ $siswa->total_poin }}</span>
                                </li>
                            @empty
                                <li class="px-6 py-6 text-center text-sm text-gray-500 italic">Belum ada data poin prestasi yang tercatat.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Top 5 Pelanggaran (Poin Negatif) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="bg-red-50/70 px-5 py-4 border-b border-slate-100">
                        <h3 class="text-[15px] font-bold text-red-800">⚠️ Top 5 Perlu Perhatian Khusus</h3>
                        <p class="text-xs text-red-600 mt-0.5">Siswa dengan akumulasi poin negatif terbanyak</p>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-gray-100">
                            @forelse($top_pelanggaran as $index => $siswa)
                                <li class="px-6 py-4 flex justify-between items-center hover:bg-slate-50/70 transition">
                                    <div class="flex items-center gap-4">
                                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-bold text-sm">
                                            #{{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-800 text-sm">{{ $siswa->nama_lengkap }}</p>
                                            <p class="text-xs text-gray-500">Kelas {{ $siswa->kelas }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">{{ $siswa->total_poin }}</span>
                                </li>
                            @empty
                                <li class="px-6 py-6 text-center text-sm text-gray-500 italic">Alhamdulillah, belum ada data pelanggaran berat.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>

            <!-- TABEL REKAP KESELURUHAN SISWA -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-5 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-[15px] font-bold text-slate-800">📋 Rekapitulasi Seluruh Siswa</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Peringkat berdasarkan akumulasi total poin tertinggi.</p>
                    </div>
                    
                    <!-- Fitur Pencarian -->
                    <form action="{{ route('sr.dashboard') }}" method="GET" class="w-full md:w-1/3">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kelas..." class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3.5 pr-10 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <button type="submit" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-blue-600">
                                🔍
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[860px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">No. Peringkat</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">NISN</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Lengkap</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Total Akumulasi Poin</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekap_siswa as $index => $siswa)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <!-- Menghitung urutan peringkat berdasarkan pagination -->
                                <td class="px-4 py-3 text-sm font-bold text-slate-500">{{ ($rekap_siswa->currentPage() - 1) * $rekap_siswa->perPage() + $loop->iteration }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $siswa->nisn }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $siswa->nama_lengkap }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $siswa->kelas }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($siswa->total_poin > 0)
                                        <span class="inline-block min-w-[3rem] text-xs font-bold px-3 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">+{{ $siswa->total_poin }}</span>
                                    @elseif($siswa->total_poin < 0)
                                        <span class="inline-block min-w-[3rem] text-xs font-bold px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">{{ $siswa->total_poin }}</span>
                                    @else
                                        <span class="inline-block min-w-[3rem] text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('sr.poin.history', $siswa->id) }}" title="Lihat Histori Poin" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👁️</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500 italic">Data siswa tidak ditemukan atau belum ada siswa aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Menampilkan Link Paginasi -->
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $rekap_siswa->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- ==============================================
         MODAL POP-UP SINKRONISASI (SAPU JAGAT)
         ============================================== -->
    <div id="syncModal" class="fixed inset-0 z-50 hidden flex items-center justify-center transition-opacity duration-300 opacity-0">
        <!-- Latar belakang gelap -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm"></div>
        
        <!-- Kotak Modal -->
        <div id="syncModalBox" class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 z-10 overflow-hidden transform scale-95 transition-transform duration-300">
            
            <!-- STATE 1: SEDANG LOADING -->
            <div id="syncLoading" class="p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-gray-100 border-t-blue-600 mb-6"></div>
                <h3 class="text-xl font-black text-gray-800 mb-2">Mensinkronkan Data...</h3>
                <p class="text-sm text-gray-500">Mencari data yatim, memindahkan histori poin, dan menghitung ulang seluruh kalkulasi. Mohon tunggu sebentar.</p>
            </div>

            <!-- STATE 2: SUKSES -->
            <div id="syncSuccess" class="hidden p-8 text-center">
                <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-black text-gray-800 mb-4">Proses Selesai!</h3>
                
                <!-- Kotak Rincian (Dihasilkan otomatis dari HTML rute backend) -->
                <div id="syncMessage" class="bg-blue-50 p-4 rounded-lg text-sm text-left border border-blue-100 mb-6">
                    <!-- List data akan disuntikkan ke sini -->
                </div>
                
                <button onclick="closeSyncModalAndRefresh()" class="inline-flex items-center justify-center gap-1.5 h-10 w-full px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    Tutup & Muat Ulang Halaman
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT JAVASCRIPT UNTUK MODAL SINKRONISASI -->
    <script>
        function openSyncModal() {
            const modal = document.getElementById('syncModal');
            const box = document.getElementById('syncModalBox');
            const loading = document.getElementById('syncLoading');
            const success = document.getElementById('syncSuccess');

            // Reset tampilan ke Loading
            loading.classList.remove('hidden');
            success.classList.add('hidden');

            // Munculkan Modal
            modal.classList.remove('hidden');
            void modal.offsetWidth; // Memicu reflow browser agar animasi jalan
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');

            // Jalankan Fetch ke Route Backend
            fetch("{{ url('/sinkron-database') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                // Trik Pintar: Mengekstrak tag <ul> dari respons HTML route Anda
                let match = html.match(/<ul[^>]*>([\s\S]*?)<\/ul>/);
                let listHtml = match ? match[1] : '<li>✅ Sinkronisasi berhasil dijalankan dan data telah bersih.</li>';
                
                // Masukkan ke dalam kotak rincian
                document.getElementById('syncMessage').innerHTML = `<ul class="text-gray-700 space-y-2 list-disc pl-4">${listHtml}</ul>`;
                
                // Tambahkan sedikit delay 1 detik agar UX terasa lebih natural
                setTimeout(() => {
                    loading.classList.add('hidden');
                    success.classList.remove('hidden');
                }, 1000);
            })
            .catch(error => {
                document.getElementById('syncMessage').innerHTML = `<p class="text-red-500 font-bold">Terjadi kesalahan teknis saat menghubungi server.</p>`;
                loading.classList.add('hidden');
                success.classList.remove('hidden');
            });
        }

        function closeSyncModalAndRefresh() {
            // Animasi tutup pop-up
            const modal = document.getElementById('syncModal');
            const box = document.getElementById('syncModalBox');
            
            modal.classList.add('opacity-0');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            
            // Reload halaman setelah animasi tutup selesai
            setTimeout(() => {
                window.location.reload();
            }, 300);
        }
    </script>
</x-app-layout>