<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">
                        {{ $jenis === 'adab' ? '🕌' : '🛏️' }} Penilaian {{ $labelJenis }}
                    </h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Kuesioner skala 1–5 per siswa · {{ $jumlahKriteria }} pertanyaan
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('penilaian.rekap', ['periode' => $periodeAktif->id ?? 0]) }}"
                       class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📊 Rekap Nilai</a>
                    @can('kelola-master-penilaian')
                        <a href="{{ route('penilaian.master') }}"
                           class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">⚙️ Master Penilaian</a>
                    @endcan
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            @if($jumlahKriteria === 0)
                <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 p-3.5 mb-3 rounded shadow-sm text-[13px] leading-relaxed">
                    ⚠️ Belum ada pertanyaan aktif untuk <strong>{{ $labelJenis }}</strong>. Minta admin menambahkannya di
                    <a href="{{ route('penilaian.master') }}" class="font-bold underline">Master Penilaian</a> sebelum mulai menilai.
                </div>
            @endif

            {{-- ===== MULAI PENILAIAN ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                <h4 class="text-[14px] font-bold text-slate-800 mb-1">Mulai / lanjutkan penilaian saya</h4>
                <p class="text-[12.5px] text-slate-500 mb-3">
                    Satu lembar penilaian per periode. Penilaian {{ $labelJenis }} diisi oleh
                    <strong>musyrif/musyrifah</strong> — tiap musyrif punya lembarnya sendiri, dan bila satu siswa
                    dinilai lebih dari satu musyrif, nilainya dirata-ratakan di rekap.
                    @if($jenis === 'keasramaan') Lembar keasramaan bisa diisi cepat per kamar. @endif
                </p>

                @if($periodeList->isEmpty())
                    <div class="text-[13px] text-slate-500">Belum ada periode penilaian. Tambahkan dulu di Master Penilaian.</div>
                @else
                    <form action="{{ route('penilaian.sesi.buat', $jenis) }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                        @csrf
                        <div class="col-span-2 sm:col-span-8">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode</label>
                            <select name="periode_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                @foreach($periodeList as $p)
                                    <option value="{{ $p->id }}" @selected($periodeAktif && $p->id === $periodeAktif->id)>{{ $p->nama }}@if($p->aktif) — aktif @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-4 flex items-end">
                            <button type="submit" class="w-full h-10 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">
                                📝 Buka lembar penilaian
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ===== LEMBAR SAYA ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-3">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Lembar penilaian saya</h4>
                </div>
                @forelse($sesiSaya as $s)
                    @php
                        $terisi = count($s->hasilPerSiswa());
                        $total = \App\Models\Siswa::where('status', 'Aktif')->count();
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 last:border-0">
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $s->periode->nama ?? '-' }}</div>
                            <div class="text-[11.5px] text-slate-400">
                                {{ \App\Models\PenilaianSesi::PERAN[$s->penilai_peran] ?? '-' }} · {{ $terisi }}/{{ $total }} siswa terisi
                                @if($s->difinalkan_pada) · difinalkan {{ $s->difinalkan_pada->format('d/m/Y H:i') }}@endif
                            </div>
                        </div>
                        <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold {{ $s->status === 'final' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ $s->status === 'final' ? 'FINAL' : 'DRAFT' }}
                        </span>
                        <a href="{{ route('penilaian.sesi', $s->id) }}"
                           class="inline-flex items-center justify-center h-8 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12px] font-semibold transition whitespace-nowrap">
                            {{ $s->status === 'final' ? 'Lihat' : 'Lanjut isi' }}
                        </a>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Belum ada lembar penilaian. Pilih periode di atas untuk mulai.</div>
                @endforelse
            </div>

            {{-- ===== LEMBAR PENILAI LAIN ===== --}}
            @if($sesiLain->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Sudah menilai {{ $labelJenis }}</h4>
                    </div>
                    @foreach($sesiLain as $s)
                        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-slate-100 last:border-0">
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-semibold text-slate-700 truncate">{{ $s->penilai->name ?? '-' }}</div>
                                <div class="text-[11.5px] text-slate-400 truncate">
                                    {{ $s->periode->nama ?? '-' }} · {{ \App\Models\PenilaianSesi::PERAN[$s->penilai_peran] ?? '-' }}
                                </div>
                            </div>
                            <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold {{ $s->status === 'final' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $s->status === 'final' ? 'FINAL' : 'DRAFT' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
