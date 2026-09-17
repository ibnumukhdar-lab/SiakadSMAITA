<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🖨️ Rapor Student Root</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        @if($bolehSemua)
                            Cetak rapor per santri — Anda bisa mencetak semua santri (pengawasan & arsip)
                        @else
                            Cetak rapor santri binaan grup Anda — {{ $grupSaya->pluck('nama_grup')->implode(', ') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    @if($grupTerpilih !== '')
                        <a href="{{ route('sr.rapot.cetak', ['grup' => $grupTerpilih, 'semester' => $preset]) }}"
                           class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition whitespace-nowrap">🖨️ Cetak grup ini ({{ $baris->count() }} santri)</a>
                    @endif
                    <a href="{{ route('sr.mygroup') }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">👥 Grup Binaan</a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('error') }}</div>
            @endif

            <form action="{{ route('sr.rapot') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-4 grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                <div class="sm:col-span-5">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode penilaian</label>
                    <select name="semester" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        @foreach($semesterList as $kunci => $label)
                            <option value="{{ $kunci }}" @selected($preset === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-5">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tahun ajaran</label>
                    <div class="w-full h-10 rounded-lg border border-slate-200 bg-slate-50 px-2.5 flex items-center text-[13px] font-semibold text-slate-600">
                        {{ $tahunAjaran }}
                        <span class="ml-1.5 font-normal text-slate-400">{{ \Carbon\Carbon::parse($dari)->locale('id')->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($sampai)->locale('id')->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
                <div class="sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full h-10 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Grup</label>
                    <select name="grup" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        <option value="">Semua grup yang boleh saya cetak</option>
                        @foreach($grupList as $g)
                            <option value="{{ $g->id }}" @selected($grupTerpilih === $g->id)>{{ $g->nama_grup }}@if($bolehSemua && $g->mentor) · {{ $g->mentor->name }}@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-6">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari santri</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="nama atau NISN"
                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                </div>
            </form>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-2">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Daftar santri</h4>
                    <span class="text-[11.5px] text-slate-400">
                        {{ $baris->count() }} santri tampil
                        @if($bolehSemua && $baris->count() !== $semuaSantri) · total {{ $semuaSantri }} santri aktif @endif
                        · {{ $semesterList[$preset] ?? '' }}
                    </span>
                </div>

                @forelse($baris as $b)
                    <div class="flex flex-wrap items-center gap-3 px-3 sm:px-4 py-3 border-b border-slate-100 last:border-0">
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $b['nama'] }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                Kelas {{ $b['kelas'] ?: '-' }}
                                @if($b['grup']) · Grup {{ $b['grup'] }}@endif
                                @if($b['kamar']) · Kamar {{ $b['kamar'] }}@endif
                                · NISN {{ $b['nisn'] ?: '-' }}
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-[11.5px] font-bold {{ $b['total'] > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($b['total'] < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-500 border-slate-200') }}">
                                {{ $b['total'] > 0 ? '+' : '' }}{{ $b['total'] }} poin · {{ $b['pos'] }} positif / {{ $b['neg'] }} negatif
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-lg border px-2 py-1 text-[11.5px] font-bold {{ $b['project_rata'] !== null ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                Project {{ $b['project_rata'] !== null ? $b['project_rata'] : '—' }}
                            </span>
                        </div>

                        <a href="{{ route('sr.rapot.cetak', ['siswa' => $b['id'], 'semester' => $preset]) }}"
                           target="_blank"
                           class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900 transition whitespace-nowrap">
                            🖨️ Cetak
                        </a>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada santri yang bisa dicetak pada pilihan ini.</div>
                @endforelse
            </div>


            <p class="text-[11.5px] text-slate-400 mt-3 leading-relaxed">
                Setiap mentor Student Root mencetak rapor santri binaan grupnya sendiri. Super Admin, Kepala Diniyah, Kepala Sekolah, dan Tata Usaha
                bisa mencetak semua santri untuk pengawasan dan arsip. Nilai karakter = akumulasi poin apa adanya; nilai project = rata-rata nilai akhir project
                (tahapan berbobot) pada periode yang dipilih.
            </p>
        </div>
    </div>
</x-app-layout>
