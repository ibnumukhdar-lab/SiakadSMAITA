<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📊 Rekap Penilaian Karakter</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Nilai adab &amp; keasramaan per siswa (dinilai musyrif/musyrifah) · predikat A ≥ {{ $ambang['a'] }}, B ≥ {{ $ambang['b'] }}, C ≥ {{ $ambang['c'] }}, D &lt; {{ $ambang['c'] }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('penilaian.rekap.ekspor', ['periode' => $periodeId, 'kelas' => $filter['kelas'], 'q' => $filter['q']]) }}"
                       class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">⬇️ Ekspor CSV</a>
                    @can('nilai-adab')
                        <a href="{{ route('penilaian.isi', 'adab') }}" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">🕌 Isi Adab</a>
                    @endcan
                    @can('nilai-keasramaan')
                        <a href="{{ route('penilaian.isi', 'keasramaan') }}" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">🛏️ Isi Keasramaan</a>
                    @endcan
                </div>
            </div>

            {{-- ===== FILTER ===== --}}
            <form action="{{ route('penilaian.rekap') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3 grid grid-cols-2 sm:grid-cols-12 gap-2">
                <div class="col-span-2 sm:col-span-5">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode</label>
                    <select name="periode" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        @forelse($periodeList as $p)
                            <option value="{{ $p->id }}" @selected($periodeId === $p->id)>{{ $p->nama }}@if($p->aktif) — aktif @endif</option>
                        @empty
                            <option value="0">Belum ada periode</option>
                        @endforelse
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kelas</label>
                    <select name="kelas" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        <option value="">Semua</option>
                        @foreach($daftarKelas as $namaKelas)
                            <option value="{{ $namaKelas }}" @selected($filter['kelas'] === $namaKelas)>{{ $namaKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari nama / NISN</label>
                    <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Ketik untuk mencari"
                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                </div>
                <div class="col-span-2 sm:col-span-1 flex items-end">
                    <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Filter</button>
                </div>
            </form>

            {{-- ===== RINGKASAN ===== --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Siswa aktif</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['siswa'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Sudah dinilai adab</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['adab_terisi'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Sudah dinilai keasramaan</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['asrama_terisi'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sebaran predikat</div>
                    <div class="flex flex-wrap gap-1">
                        @foreach(['A', 'B', 'C', 'D'] as $p)
                            <span class="inline-flex items-center gap-1 rounded-lg border px-1.5 py-0.5 text-[11px] font-bold {{ \App\Models\PenilaianPengaturan::warnaPredikat($p) }}">
                                {{ $p }} <span class="font-semibold opacity-70">{{ $ringkasan['predikat'][$p] ?? 0 }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ===== SUDAH MENGISI ===== --}}
            @if($sesiPeriode->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Lembar penilaian periode ini</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($sesiPeriode as $jenis => $kumpulan)
                            @foreach($kumpulan as $s)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600">
                                    {{ $jenis === 'adab' ? '🕌' : '🛏️' }} {{ $s->penilai->name ?? '-' }}
                                    <span class="font-bold {{ $s->status === 'final' ? 'text-green-600' : 'text-amber-600' }}">{{ $s->status === 'final' ? 'final' : 'draft' }}</span>
                                </span>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif

            @if($baris->isEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                    Tidak ada siswa yang cocok dengan filter ini.
                </div>
            @else
                {{-- ===== TABEL (desktop) ===== --}}
                <div class="hidden sm:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/80 text-[10.5px] font-black uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-2.5">Siswa</th>
                                <th class="px-3 py-2.5">Kelas</th>
                                <th class="px-3 py-2.5 text-center">Adab</th>
                                <th class="px-3 py-2.5 text-center">Keasramaan</th>
                                <th class="px-3 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($baris as $r)
                                <tr class="border-t border-slate-100 hover:bg-slate-50/60 transition">
                                    <td class="px-4 py-2.5">
                                        <div class="text-[13px] font-semibold text-slate-800">{{ $r['nama'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $r['nisn'] ?: '-' }}</div>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="text-[12.5px] text-slate-600">{{ $r['kelas'] ?: '-' }}</div>
                                        @if($r['kamar'])<div class="text-[11px] text-slate-400">{{ $r['kamar'] }}</div>@endif
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        @include('penilaian.partials._nilai-akhir', ['nilai' => $r['adab']])
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        @include('penilaian.partials._nilai-akhir', ['nilai' => $r['keasramaan']])
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'siswa' => $r['id']]) }}"
                                               target="_blank" title="Cetak rapot Adab & Keasramaan"
                                               class="inline-flex items-center justify-center h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900 transition">🖨️</a>
                                            <a href="{{ route('penilaian.siswa', $r['id']) }}"
                                               class="inline-flex items-center justify-center h-7 px-2.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-[11.5px] font-semibold hover:bg-slate-50 transition">Rincian</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ===== KARTU (HP) ===== --}}
                <div class="sm:hidden space-y-2">
                    @foreach($baris as $r)
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-[13.5px] font-bold text-slate-900 truncate">{{ $r['nama'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $r['kelas'] ?: 'Kelas -' }}@if($r['kamar']) · {{ $r['kamar'] }}@endif · {{ $r['nisn'] ?: 'NISN -' }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'siswa' => $r['id']]) }}" target="_blank" class="text-[11.5px] font-semibold text-slate-500 hover:underline">🖨️</a>
                                    <a href="{{ route('penilaian.siswa', $r['id']) }}" class="text-[11.5px] font-semibold text-blue-900 hover:underline">Rincian →</a>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-2.5">
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-2">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">🕌 Adab</div>
                                    @include('penilaian.partials._nilai-akhir', ['nilai' => $r['adab'], 'ringkas' => true])
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-2">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">🛏️ Keasramaan</div>
                                    @include('penilaian.partials._nilai-akhir', ['nilai' => $r['keasramaan'], 'ringkas' => true])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
