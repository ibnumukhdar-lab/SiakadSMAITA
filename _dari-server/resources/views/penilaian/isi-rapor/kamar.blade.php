<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="mb-3">
                <a href="{{ route('penilaian.isi.rapor') }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Kembali ke daftar kamar</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">Kamar {{ $kamar->nama_kamar }}</h3>
                <p class="text-[13px] text-slate-500 mt-0.5">
                    {{ ucfirst($kamar->kategori) }}
                    @if($binaanSaya)
                        · <span class="inline-flex items-center rounded-md bg-blue-50 text-blue-800 border border-blue-200 px-1.5 py-0.5 text-[10px] font-bold align-middle">binaan saya</span>
                    @elseif($kamar->musyrif)
                        · musyrif: {{ $kamar->musyrif->name }}
                    @endif
                    @if($periode) · {{ $periode->nama }} @endif
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('error') }}</div>
            @endif

            @unless($terbuka)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    Sesi isi rapor sedang <strong>tertutup</strong> — halaman ini hanya bisa dilihat. Hubungi TU / Kepala Diniyah bila perlu dibuka kembali.
                </div>
            @endunless

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="text-[13px] font-semibold text-slate-700">Progres kamar ini</div>
                    <div class="text-[12.5px] font-bold text-blue-900">{{ $lengkap }}/{{ $anggota->count() }} santri lengkap</div>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-2 rounded-full {{ $anggota->count() > 0 && $lengkap >= $anggota->count() ? 'bg-green-500' : 'bg-blue-700' }}"
                         style="width: {{ $anggota->count() > 0 ? (int) round($lengkap / $anggota->count() * 100) : 0 }}%"></div>
                </div>
                <p class="text-[11.5px] text-slate-400 mt-2">Dinilai <strong>satu per satu</strong>: pilih nama santri, isi bagian A (Adab) + B (Keasramaan), lalu simpan.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Penghuni kamar</h4>
                    <span class="text-[11.5px] text-slate-400">{{ $anggota->count() }} santri</span>
                </div>

                @forelse($anggota as $a)
                    @php
                        $st = $status[$a->id] ?? null;
                        $penuh = (bool) ($st['lengkap'] ?? false);
                        $sebagian = ! $penuh && (bool) ($st['terisi'] ?? false);
                    @endphp
                    <div class="flex items-center gap-3 px-3 sm:px-4 py-3 border-b border-slate-100 last:border-0">
                        <x-avatar-siswa :siswa="$a" />
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $a->nama_lengkap }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">Kelas {{ $a->kelas ?: '-' }}</div>
                        </div>

                        @if($penuh)
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-green-50 text-green-700 border border-green-200">lengkap</span>
                        @elseif($sebagian)
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">belum lengkap</span>
                        @else
                            <span class="hidden sm:inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-50 text-slate-500 border border-slate-200">belum dinilai</span>
                        @endif

                        @if($terbuka)
                            <a href="{{ route('penilaian.isi.form', $a->id) }}"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg {{ $penuh ? 'border border-slate-300 bg-white text-slate-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-[12px] font-semibold transition whitespace-nowrap">
                                {{ $penuh ? 'Perbaiki' : ($sebagian ? 'Lanjutkan' : 'Nilai') }}
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada siswa aktif di kamar ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
