<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🖨️ Rapot Penilaian Adab &amp; Keasramaan</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Cetak rapot per anak atau sekaligus satu kamar (satu halaman per santri)</p>
                </div>
                <a href="{{ route('penilaian.rekap', ['periode' => $periodeId]) }}"
                   class="self-start sm:self-auto inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">📊 Rekap Nilai</a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('error') }}</div>
            @endif

            <form action="{{ route('penilaian.rapot') }}" method="GET" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-4 grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                <div class="sm:col-span-4">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode</label>
                    <select name="periode" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" @selected($periodeId === $p->id)>{{ $p->nama }}@if($p->aktif) — aktif @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kamar</label>
                    <select name="kamar" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        <option value="0">Semua kamar</option>
                        @foreach($kamarList as $k)
                            <option value="{{ $k->id }}" @selected($kamarId === $k->id)>{{ $k->nama_kamar }} ({{ ucfirst($k->kategori) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="nama atau NISN"
                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                </div>
                <div class="sm:col-span-1 flex items-end">
                    <button type="submit" class="w-full h-10 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>
                </div>
            </form>

            @if($kamarId > 0 && $baris->isNotEmpty())
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-3.5 mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div class="text-[13px] text-blue-900">
                        Cetak sekaligus <strong>{{ $baris->count() }} santri</strong> kamar {{ $kamarList->firstWhere('id', $kamarId)->nama_kamar ?? '' }} —
                        satu halaman per santri, siap dibagikan.
                    </div>
                    <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'kamar' => $kamarId]) }}"
                       class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition whitespace-nowrap">
                        🖨️ Cetak Rapot Kamar Ini
                    </a>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Daftar santri</h4>
                    <span class="text-[11.5px] text-slate-400">{{ $baris->count() }} santri · periode {{ $periode->nama ?? '-' }}</span>
                </div>

                @forelse($baris as $b)
                    <div class="flex flex-wrap items-center gap-3 px-3 sm:px-4 py-3 border-b border-slate-100 last:border-0">
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $b['nama'] }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                {{ $b['kelas'] ?: 'Kelas -' }}@if($b['kamar']) · {{ $b['kamar'] }}@endif · NISN {{ $b['nisn'] ?: '-' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-[11.5px] font-bold {{ $b['adab']['rata'] !== null ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                Adab {{ $b['adab']['rata'] !== null ? $b['adab']['rata'] . ' (' . $b['adab']['predikat'] . ')' : '-' }}
                                <span class="text-slate-400 font-normal">{{ count($b['adab']['penilai']) }}/{{ $b['wajib'] }}</span>
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-[11.5px] font-bold {{ $b['keasramaan']['rata'] !== null ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                Asrama {{ $b['keasramaan']['rata'] !== null ? $b['keasramaan']['rata'] . ' (' . $b['keasramaan']['predikat'] . ')' : '-' }}
                                <span class="text-slate-400 font-normal">{{ count($b['keasramaan']['penilai']) }}/{{ $b['wajib'] }}</span>
                            </span>
                        </div>

                        <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'siswa' => $b['id']]) }}"
                           class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900 transition whitespace-nowrap">
                            🖨️ Cetak
                        </a>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada santri yang cocok dengan pilihan ini.</div>
                @endforelse
            </div>

            <p class="text-[11.5px] text-slate-400 mt-3">
                Nilai akhir dihitung sama dengan halaman Rekap: rata-rata persentase dari setiap musyrif/musyrifah yang menilai pada periode terpilih.
            </p>
        </div>
    </div>
</x-app-layout>
