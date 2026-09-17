<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🧾 Penyusunan Portofolio</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Project yang sudah menuntaskan seluruh tahapannya — lengkapi narasi &amp; foto, lalu cetak portofolionya
                    </p>
                </div>
                <a href="{{ route('project-sr.index') }}"
                   class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📁 Daftar Project</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Portofolio memuat rekap 5 tahap (status, tanggal, catatan, nilai tiap siswa), narasi yang kamu tulis,
                dan foto/dokumentasi kegiatan. Opsi <strong>Cetak Portofolio</strong> muncul setelah minimal satu foto/dokumentasi diunggah.
            </div>

            @forelse($daftar as $baris)
                @php
                    $p = $baris['project'];
                    $lengkapNarasi = $baris['narasi'] >= 4;
                    $siapCetak = $baris['dokumen'] > 0;
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-[14.5px] font-bold text-slate-900 truncate">{{ $p->nama }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                {{ $p->grup->nama_grup ?? '-' }}@if($p->mentor) · mentor {{ $p->mentor->name }}@endif
                                @if($p->tanggal_selesai) · selesai {{ $p->tanggal_selesai->format('d/m/Y') }}@endif
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold
                            {{ $siapCetak ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $siapCetak ? 'SIAP CETAK' : 'BELUM ADA FOTO' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 mt-2.5 text-[11.5px] text-slate-500">
                        <span>Tahap: <strong class="text-slate-700">{{ $p->progresTahap() }}</strong> selesai</span>
                        <span>Narasi: <strong class="text-slate-700">{{ $baris['narasi'] }}/6</strong> bagian</span>
                        <span>Foto/dokumen: <strong class="text-slate-700">{{ $baris['dokumen'] }}</strong></span>
                        @if($baris['rata'] !== null)
                            <span class="inline-flex items-center gap-1.5">
                                Nilai project: <strong class="text-slate-800">{{ $baris['rata'] }}%</strong>
                                <span class="inline-flex items-center justify-center h-5 w-5 rounded-md border text-[10.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat(\App\Models\PenilaianPengaturan::predikat($baris['rata'])) }}">
                                    {{ \App\Models\PenilaianPengaturan::predikat($baris['rata']) }}
                                </span>
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <a href="{{ route('project-sr.portofolio', $p->id) }}"
                           class="inline-flex items-center justify-center h-8 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12px] font-semibold transition">
                            {{ $baris['dokumen'] > 0 || $baris['narasi'] > 0 ? 'Lanjutkan penyusunan' : 'Susun portofolio' }}
                        </a>
                        @if($siapCetak)
                            <a href="{{ route('project-sr.portofolio.cetak', $p->id) }}" target="_blank"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-slate-50 transition">🖨️ Cetak portofolio</a>
                        @endif
                        <a href="{{ route('project-sr.show', $p->id) }}"
                           class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 text-[12px] font-semibold hover:bg-slate-100 transition">Detail project</a>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                    Belum ada project yang tuntas seluruh tahapannya.
                    Portofolio otomatis masuk ke halaman ini setelah kelima tahap (yang dipakai) berstatus <strong>Selesai</strong>.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
