<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📝 Inspeksi Asrama: <span style="color: #2563eb;">{{ \Carbon\Carbon::parse($draft_putra->tanggal)->translatedFormat('d F Y') }}</span>
        </h2>
    </x-slot>

    <style>
        .k-wrapper { max-width: 800px; margin: 0 auto; padding: 20px 15px; font-family: 'Segoe UI', sans-serif; }
        .k-alert-success { background: #dcfce7; border-left: 5px solid #22c55e; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .k-alert-error { background: #fee2e2; border-left: 5px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        
        .section-box { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .section-title { font-size: 20px; font-weight: 900; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px dashed #e2e8f0; display: flex; align-items: center; gap: 8px; }
        
        .k-card { background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; border-left: 5px solid #cbd5e1; }
        .k-card-title { font-size: 17px; font-weight: 900; color: #1e293b; margin: 0; text-transform: uppercase; }
        
        .badge-done { background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 50px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; }
        .btn-rate { background: #4f46e5; color: white; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(79,70,229,0.3); text-align: center; }
        
        .progress-bg { background: #e2e8f0; height: 10px; border-radius: 50px; overflow: hidden; margin-bottom: 15px; }
        
        .k-final-box { border-radius: 12px; padding: 20px; color: white; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-align: center; }

        /* Animasi Tab */
        @keyframes fadeUpTab { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeUpTab 0.4s ease-out forwards; }
    </style>

    <div class="py-6">
        <div class="k-wrapper">
            
            @if(session('success')) <div class="k-alert-success">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div class="k-alert-error">❌ {{ session('error') }}</div> @endif
            @if($errors->any()) <div class="k-alert-error">Pastikan Anda telah mengisi form dengan benar!</div> @endif

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
                <div class="section-box" style="border-top: 5px solid #3b82f6;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 class="section-title" style="color: #1d4ed8; margin: 0; border: none; padding: 0;">👦 KAMAR PUTRA (IKHWAN)</h3>
                        
                        @if($kamar_putra->count() > 0 && $draft_putra->status != 'final' && count($dinilai_putra) > 0)
                            <form action="{{ route('asrama.penilaian.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset seluruh penilaian Divisi Putra hari ini? Data yang sudah diinput akan dihapus.');">
                                @csrf
                                <input type="hidden" name="kategori" value="putra">
                                <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: bold; cursor: pointer;">
                                    🔄 Reset Penilaian
                                </button>
                            </form>
                        @endif
                    </div>
                    <div style="border-bottom: 2px dashed #e2e8f0; margin-bottom: 15px;"></div>
                    
                    @if($kamar_putra->count() > 0)
                        @if($draft_putra->status == 'final')
                            <div style="background: #dcfce7; padding: 15px; border-radius: 10px; color: #166534; font-weight: bold; text-align: center;">
                                ✅ Inspeksi Divisi Putra Hari Ini Telah Selesai.
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 5px;">
                                <span>Progress Penilaian</span>
                                <span>{{ count($dinilai_putra) }} / {{ $kamar_putra->count() }} Kamar</span>
                            </div>
                            <div class="progress-bg">
                                <div style="background: #3b82f6; height: 100%; width: {{ ($kamar_putra->count() > 0) ? (count($dinilai_putra) / $kamar_putra->count()) * 100 : 0 }}%; transition: 0.5s;"></div>
                            </div>

                            @if($selesai_putra)
                                <div class="k-final-box" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                                    <h4 style="font-weight: 900; margin-bottom: 10px; font-size: 16px;">🪄 SELURUH KAMAR PUTRA TELAH DINILAI</h4>
                                    <p style="font-size: 12px; margin-bottom: 15px; color: #bfdbfe;">Silakan lanjut ke tahap penentuan gelar kamar terbersih dan terkotor.</p>
                                    <a href="{{ route('asrama.penilaian.konfirmasi', ['kategori' => 'putra']) }}" class="btn-rate" style="display: block; width: 100%; background: #fbbf24; color: #78350f; font-size: 14px; padding: 12px; text-decoration: none; text-transform: uppercase;">
                                        Lanjut Penentuan Juara Putra ➡️
                                    </a>
                                </div>
                            @endif

                            @foreach ($kamar_putra as $kamar)
                                @php $sudah = in_array($kamar->id, $dinilai_putra); @endphp
                                <div x-data="{ openModal: false }">
                                    <div class="k-card" style="{{ $sudah ? 'border-color: #22c55e;' : 'border-color: #3b82f6;' }}">
                                        <div>
                                            <h4 class="k-card-title">{{ $kamar->nama_kamar }}</h4>
                                            <p style="font-size: 11px; color: #64748b; font-weight: bold; margin: 0;">Musyrif: {{ $kamar->musyrif->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            @if($sudah) 
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="badge-done">✅ Dinilai</span>
                                                    <button @click="openModal = true" class="btn-rate" style="background: #f59e0b; box-shadow: 0 2px 4px rgba(245,158,11,0.3);">✏️ Koreksi</button>
                                                </div>
                                            @else 
                                                <button @click="openModal = true" class="btn-rate">Beri Nilai</button>
                                            @endif
                                        </div>
                                    </div>
                                    @include('asrama.penilaian._form_modal') 
                                </div>
                            @endforeach
                        @endif
                    @else
                        <p style="font-size: 13px; color: #94a3b8; font-style: italic;">Belum ada data kamar Putra.</p>
                    @endif
                </div>
            </div>

            <!-- TAB PUTRI -->
            <div id="tab-putri" class="tab-content">
                <div class="section-box" style="border-top: 5px solid #ec4899;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 class="section-title" style="color: #be185d; margin: 0; border: none; padding: 0;">👧 KAMAR PUTRI (AKHWAT)</h3>
                        
                        @if($kamar_putri->count() > 0 && $draft_putri->status != 'final' && count($dinilai_putri) > 0)
                            <form action="{{ route('asrama.penilaian.reset') }}" method="POST" onsubmit="return confirm('Yakin ingin mereset seluruh penilaian Divisi Putri hari ini? Data yang sudah diinput akan dihapus.');">
                                @csrf
                                <input type="hidden" name="kategori" value="putri">
                                <button type="submit" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: bold; cursor: pointer;">
                                    🔄 Reset Penilaian
                                </button>
                            </form>
                        @endif
                    </div>
                    <div style="border-bottom: 2px dashed #e2e8f0; margin-bottom: 15px;"></div>
                    
                    @if($kamar_putri->count() > 0)
                        @if($draft_putri->status == 'final')
                            <div style="background: #dcfce7; padding: 15px; border-radius: 10px; color: #166534; font-weight: bold; text-align: center;">
                                ✅ Inspeksi Divisi Putri Hari Ini Telah Selesai.
                            </div>
                        @else
                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: bold; color: #64748b; margin-bottom: 5px;">
                                <span>Progress Penilaian</span>
                                <span>{{ count($dinilai_putri) }} / {{ $kamar_putri->count() }} Kamar</span>
                            </div>
                            <div class="progress-bg">
                                <div style="background: #ec4899; height: 100%; width: {{ ($kamar_putri->count() > 0) ? (count($dinilai_putri) / $kamar_putri->count()) * 100 : 0 }}%; transition: 0.5s;"></div>
                            </div>

                            @if($selesai_putri)
                                <div class="k-final-box" style="background: linear-gradient(135deg, #db2777 0%, #be185d 100%);">
                                    <h4 style="font-weight: 900; margin-bottom: 10px; font-size: 16px;">🪄 SELURUH KAMAR PUTRI TELAH DINILAI</h4>
                                    <p style="font-size: 12px; margin-bottom: 15px; color: #fbcfe8;">Silakan lanjut ke tahap penentuan gelar kamar terbersih dan terkotor.</p>
                                    <a href="{{ route('asrama.penilaian.konfirmasi', ['kategori' => 'putri']) }}" class="btn-rate" style="display: block; width: 100%; background: #fbbf24; color: #78350f; font-size: 14px; padding: 12px; text-decoration: none; text-transform: uppercase;">
                                        Lanjut Penentuan Juara Putri ➡️
                                    </a>
                                </div>
                            @endif

                            @foreach ($kamar_putri as $kamar)
                                @php $sudah = in_array($kamar->id, $dinilai_putri); @endphp
                                <div x-data="{ openModal: false }">
                                    <div class="k-card" style="{{ $sudah ? 'border-color: #22c55e;' : 'border-color: #ec4899;' }}">
                                        <div>
                                            <h4 class="k-card-title">{{ $kamar->nama_kamar }}</h4>
                                            <p style="font-size: 11px; color: #64748b; font-weight: bold; margin: 0;">Musyrif: {{ $kamar->musyrif->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            @if($sudah) 
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <span class="badge-done">✅ Dinilai</span>
                                                    <button @click="openModal = true" class="btn-rate" style="background: #f59e0b; box-shadow: 0 2px 4px rgba(245,158,11,0.3);">✏️ Koreksi</button>
                                                </div>
                                            @else 
                                                <button @click="openModal = true" class="btn-rate" style="background: #ec4899;">Beri Nilai</button>
                                            @endif
                                        </div>
                                    </div>
                                    @include('asrama.penilaian._form_modal') 
                                </div>
                            @endforeach
                        @endif
                    @else
                        <p style="font-size: 13px; color: #94a3b8; font-style: italic;">Belum ada data kamar Putri.</p>
                    @endif
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