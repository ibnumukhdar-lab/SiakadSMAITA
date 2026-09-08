<x-app-layout>
    <style>
        /* Animasi Tab (dipakai switchTab) */
        @keyframes fadeUpTab { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeUpTab 0.4s ease-out forwards; }
    </style>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📝 Inspeksi Asrama Hari Ini</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Sidak kebersihan kamar — {{ \Carbon\Carbon::parse($draft_putra->tanggal)->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            @if(session('success')) <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div> @endif
            @if($errors->any()) <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">Pastikan Anda telah mengisi form dengan benar!</div> @endif

            <!-- ================= KONTROL NAVIGASI TAB ================= -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-6">
                <button id="btn-putra" onclick="switchTab('putra')" class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-lg bg-blue-600 text-white shadow-blue-500/30 transform border border-transparent">
                    👨‍👦 Divisi Putra
                </button>
                <button id="btn-putri" onclick="switchTab('putri')" class="flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-rose-50 hover:text-rose-600 transform">
                    👩‍👧 Divisi Putri
                </button>
            </div>

            <!-- TAB PUTRA -->
            <div id="tab-putra" class="tab-content active">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-[15px] font-bold text-slate-800">👦 Kamar Putra (Ikhwan)</h4>

                        @if($kamar_putra->count() > 0 && $draft_putra->status != 'final' && count($dinilai_putra) > 0)
                            <form action="{{ route('asrama.penilaian.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset seluruh penilaian Divisi Putra hari ini? Data yang sudah diinput akan dihapus.');">
                                @csrf
                                <input type="hidden" name="kategori" value="putra">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">
                                    🔄 Reset Penilaian
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="p-5 sm:p-6">
                        @if($kamar_putra->count() > 0)
                            @if($draft_putra->status == 'final')
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold text-center px-4 py-3.5 rounded-xl">
                                    ✅ Inspeksi Divisi Putra Hari Ini Telah Selesai.
                                </div>
                            @else
                                <div class="flex items-center justify-between text-[13px] font-bold text-slate-600 mb-2">
                                    <span>Progress Penilaian</span>
                                    <span>{{ count($dinilai_putra) }} / {{ $kamar_putra->count() }} Kamar</span>
                                </div>
                                <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden mb-5">
                                    <div class="h-full bg-blue-600 rounded-full transition-all" style="width: {{ ($kamar_putra->count() > 0) ? (count($dinilai_putra) / $kamar_putra->count()) * 100 : 0 }}%;"></div>
                                </div>

                                @if($selesai_putra)
                                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-5 text-center">
                                        <h5 class="text-sm font-extrabold text-emerald-800 mb-1">🪄 Seluruh Kamar Putra Telah Dinilai</h5>
                                        <p class="text-xs font-semibold text-emerald-700 mb-4">Silakan lanjut ke tahap penentuan gelar kamar terbersih dan terkotor.</p>
                                        <a href="{{ route('asrama.penilaian.konfirmasi', ['kategori' => 'putra']) }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                            Lanjut Penentuan Juara Putra ➡️
                                        </a>
                                    </div>
                                @endif

                                <div class="space-y-3">
                                    @foreach ($kamar_putra as $kamar)
                                        @php $sudah = in_array($kamar->id, $dinilai_putra); @endphp
                                        <div x-data="{ openModal: false }">
                                            <div class="rounded-xl border p-4 flex flex-wrap items-center justify-between gap-3 transition {{ $sudah ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/60' }}">
                                                <div>
                                                    <h5 class="text-[15px] font-extrabold text-slate-800 uppercase leading-tight">{{ $kamar->nama_kamar }}</h5>
                                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Musyrif: {{ $kamar->musyrif->name ?? '-' }}</p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($sudah)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">✅ Dinilai</span>
                                                        <button @click="openModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 px-3 py-2 text-xs font-bold transition whitespace-nowrap">✏️ Koreksi</button>
                                                    @else
                                                        <button @click="openModal = true" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">Beri Nilai</button>
                                                    @endif
                                                </div>
                                            </div>
                                            @include('asrama.penilaian._form_modal')
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-slate-500 italic m-0">Belum ada data kamar Putra.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB PUTRI -->
            <div id="tab-putri" class="tab-content">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-[15px] font-bold text-slate-800">👧 Kamar Putri (Akhwat)</h4>

                        @if($kamar_putri->count() > 0 && $draft_putri->status != 'final' && count($dinilai_putri) > 0)
                            <form action="{{ route('asrama.penilaian.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset seluruh penilaian Divisi Putri hari ini? Data yang sudah diinput akan dihapus.');">
                                @csrf
                                <input type="hidden" name="kategori" value="putri">
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">
                                    🔄 Reset Penilaian
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="p-5 sm:p-6">
                        @if($kamar_putri->count() > 0)
                            @if($draft_putri->status == 'final')
                                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-bold text-center px-4 py-3.5 rounded-xl">
                                    ✅ Inspeksi Divisi Putri Hari Ini Telah Selesai.
                                </div>
                            @else
                                <div class="flex items-center justify-between text-[13px] font-bold text-slate-600 mb-2">
                                    <span>Progress Penilaian</span>
                                    <span>{{ count($dinilai_putri) }} / {{ $kamar_putri->count() }} Kamar</span>
                                </div>
                                <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden mb-5">
                                    <div class="h-full bg-pink-500 rounded-full transition-all" style="width: {{ ($kamar_putri->count() > 0) ? (count($dinilai_putri) / $kamar_putri->count()) * 100 : 0 }}%;"></div>
                                </div>

                                @if($selesai_putri)
                                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-5 text-center">
                                        <h5 class="text-sm font-extrabold text-emerald-800 mb-1">🪄 Seluruh Kamar Putri Telah Dinilai</h5>
                                        <p class="text-xs font-semibold text-emerald-700 mb-4">Silakan lanjut ke tahap penentuan gelar kamar terbersih dan terkotor.</p>
                                        <a href="{{ route('asrama.penilaian.konfirmasi', ['kategori' => 'putri']) }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                            Lanjut Penentuan Juara Putri ➡️
                                        </a>
                                    </div>
                                @endif

                                <div class="space-y-3">
                                    @foreach ($kamar_putri as $kamar)
                                        @php $sudah = in_array($kamar->id, $dinilai_putri); @endphp
                                        <div x-data="{ openModal: false }">
                                            <div class="rounded-xl border p-4 flex flex-wrap items-center justify-between gap-3 transition {{ $sudah ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/60' }}">
                                                <div>
                                                    <h5 class="text-[15px] font-extrabold text-slate-800 uppercase leading-tight">{{ $kamar->nama_kamar }}</h5>
                                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Musyrif: {{ $kamar->musyrif->name ?? '-' }}</p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($sudah)
                                                        <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">✅ Dinilai</span>
                                                        <button @click="openModal = true" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 px-3 py-2 text-xs font-bold transition whitespace-nowrap">✏️ Koreksi</button>
                                                    @else
                                                        <button @click="openModal = true" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">Beri Nilai</button>
                                                    @endif
                                                </div>
                                            </div>
                                            @include('asrama.penilaian._form_modal')
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <p class="text-sm text-slate-500 italic m-0">Belum ada data kamar Putri.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function switchTab(zona) {
            const tabPutra = document.getElementById('tab-putra');
            const tabPutri = document.getElementById('tab-putri');
            const btnPutra = document.getElementById('btn-putra');
            const btnPutri = document.getElementById('btn-putri');

            tabPutra.classList.remove('active');
            tabPutri.classList.remove('active');

            if (zona === 'putra') {
                tabPutra.classList.add('active');
                btnPutra.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-lg bg-blue-600 text-white shadow-blue-500/30 transform border border-transparent";
                btnPutri.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-rose-50 hover:text-rose-600 transform";
            }
            else if (zona === 'putri') {
                tabPutri.classList.add('active');
                btnPutri.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-lg bg-rose-500 text-white shadow-rose-500/30 transform border border-transparent";
                btnPutra.className = "flex-1 sm:flex-none flex items-center justify-center gap-3 px-8 py-3 rounded-2xl font-black text-lg transition-all duration-300 shadow-sm bg-white text-gray-500 border border-gray-200 hover:bg-blue-50 hover:text-blue-600 transform";
            }
        }
    </script>
</x-app-layout>
