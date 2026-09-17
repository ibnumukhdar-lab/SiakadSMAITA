<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="mb-3">
                <a href="{{ route('penilaian.sesi', $sesi->id) }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Kembali ke daftar kamar</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">Kamar {{ $kamar->nama_kamar }}</h3>
                <p class="text-[13px] text-slate-500 mt-0.5">
                    {{ ucfirst($kamar->kategori) }} · musyrif: {{ $kamar->musyrif->name ?? '-' }} ·
                    {{ $sesi->label_jenis }} · {{ $sesi->periode->nama ?? '-' }}
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('error') }}</div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                Dinilai <strong>satu per satu</strong> — pilih nama anak, isi {{ $kriteria->count() }} pertanyaan, lalu kembali ke sini.
                Tidak ada penilaian serentak seluruh kamar.
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-3">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <div class="text-[13px] font-semibold text-slate-700">Progres kamar ini</div>
                    <div class="text-[12.5px] font-bold text-blue-900">{{ $sudah }}/{{ $anggota->count() }} anak dinilai</div>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-2 rounded-full bg-blue-700" style="width: {{ $anggota->count() > 0 ? round($sudah / $anggota->count() * 100) : 0 }}%"></div>
                </div>
            </div>

            @if($kriteria->isEmpty())
                <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 p-3.5 rounded shadow-sm text-[13px]">
                    Belum ada pertanyaan aktif untuk {{ $sesi->label_jenis }}.
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Penghuni kamar</h4>
                </div>

                @forelse($anggota as $a)
                    @php $h = $hasil[$a->id] ?? null; @endphp
                    <div class="flex items-center gap-3 px-3 sm:px-4 py-3 border-b border-slate-100 last:border-0">
                        <x-avatar-siswa :siswa="$a" />
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $a->nama_lengkap }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                Kelas {{ $a->kelas ?: '-' }}
                                @if($h) · {{ $h['total'] }}/{{ $h['jumlah'] * 5 }} skor · {{ $h['persentase'] }}% @else · belum dinilai @endif
                            </div>
                        </div>

                        @if($h)
                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($h['predikat']) }}">
                                {{ $h['predikat'] }}
                            </span>
                            @unless($h['lengkap'])
                                <span class="hidden sm:inline text-[10.5px] font-bold text-amber-600">belum lengkap</span>
                            @endunless
                        @endif

                        @if($sesi->status === 'draft' && $kriteria->isNotEmpty())
                            <a href="{{ route('penilaian.sesi.form', [$sesi->id, $a->id]) }}"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg {{ $h ? 'border border-slate-300 bg-white text-slate-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-[12px] font-semibold transition whitespace-nowrap">
                                {{ $h ? 'Perbaiki' : 'Nilai' }}
                            </a>
                        @elseif($h)
                            <a href="{{ route('penilaian.siswa', $a->id) }}"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 text-[12px] font-semibold hover:bg-slate-100 transition whitespace-nowrap">Lihat</a>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada siswa aktif di kamar ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
