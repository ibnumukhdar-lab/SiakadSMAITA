<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===================== KEPALA HALAMAN ===================== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📊 Statistik Student Root</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        {{ $labelPeriode }} · {{ number_format($jumlah_santri, 0, ',', '.') }} santri
                        @if(!$bolehSemua) · hanya grup binaan Anda: {{ $grupSaya->pluck('nama_grup')->implode(', ') }} @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('sr.statistik.ekspor', request()->query()) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">⬇️ Ekspor CSV</a>
                    <a href="{{ route('sr.statistik.cetak', request()->query()) }}" target="_blank"
                       class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">🖨️ Cetak laporan</a>
                </div>
            </div>

            {{-- ===================== SARINGAN ===================== --}}
            <form method="GET" class="bg-white border border-slate-200 rounded-xl p-3 sm:p-4 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <label class="block">
                        <span class="text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Periode</span>
                        <select name="semester" class="mt-1 w-full h-9 rounded-lg border-slate-300 text-[13px]">
                            @foreach($semesterList as $kunci => $teks)
                                <option value="{{ $kunci }}" @selected($preset === $kunci)>{{ $teks }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Kelas</span>
                        <select name="kelas" class="mt-1 w-full h-9 rounded-lg border-slate-300 text-[13px]">
                            <option value="">Semua kelas</option>
                            @foreach(\App\Http\Controllers\SrStatistikController::KELAS as $k)
                                <option value="{{ $k }}" @selected($kelas === $k)>{{ $k }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Grup binaan</span>
                        <select name="grup" class="mt-1 w-full h-9 rounded-lg border-slate-300 text-[13px]">
                            <option value="">Semua grup</option>
                            @foreach($grupList as $g)
                                <option value="{{ $g->id }}" @selected($grup === $g->id)>{{ $g->nama_grup }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="flex items-end">
                        <button type="submit" class="h-9 w-full rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">Tampilkan</button>
                    </div>
                </div>
            </form>

            {{-- ===================== RINGKASAN ===================== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total entri poin</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ number_format($ringkasan['entri'], 0, ',', '.') }}</div>
                    <div class="text-[11.5px] text-slate-500">{{ $ringkasan['santri_terlibat'] }} santri terlibat</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Total poin</div>
                    <div class="text-xl font-bold mt-0.5 {{ $ringkasan['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">
                        {{ $ringkasan['poin'] > 0 ? '+' : '' }}{{ number_format($ringkasan['poin'], 0, ',', '.') }}
                    </div>
                    <div class="text-[11.5px] text-slate-500">{{ $ringkasan['santri_positif'] }} positif · {{ $ringkasan['santri_nol'] }} nol · {{ $ringkasan['santri_negatif'] }} negatif</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Rata-rata per santri</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $ringkasan['rata_rata'] > 0 ? '+' : '' }}{{ number_format($ringkasan['rata_rata'], 1, ',', '.') }}</div>
                    <div class="text-[11.5px] text-slate-500">dari {{ number_format($jumlah_santri, 0, ',', '.') }} santri</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Poin tertinggi</div>
                    @php $juara = $ringkasan['teratas'][0] ?? null; @endphp
                    <div class="text-xl font-bold text-slate-900 mt-0.5">{{ $juara ? ($juara['poin'] > 0 ? '+' : '') . $juara['poin'] : '—' }}</div>
                    <div class="text-[11.5px] text-slate-500">{{ $juara ? $juara['nama'] . ' (' . $juara['kelas'] . ')' : 'belum ada data' }}</div>
                </div>
            </div>

            {{-- ===================== TREN BULANAN ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2">Perbandingan antar bulan
                <span class="font-normal text-[12px] text-slate-500">— naik / turun dibanding bulan sebelumnya</span>
            </h4>

            @if(count($bulanan) === 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 text-[13px] mb-4">
                    Belum ada entri poin pada periode ini. Coba ganti periode ke <strong>Semua data</strong>.
                </div>
            @else
                <div class="bg-white border border-slate-200 rounded-xl p-4 mb-3">
                    <div class="flex items-end gap-4 sm:gap-8 overflow-x-auto pb-1" style="min-height:200px">
                        @foreach($bulanan as $b)
                            <div class="flex flex-col items-center gap-1.5" style="min-width:72px">
                                <div class="text-[12px] font-semibold {{ $b['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">
                                    {{ $b['poin'] > 0 ? '+' : '' }}{{ number_format($b['poin'], 0, ',', '.') }}
                                </div>
                                <div class="flex items-end gap-1" style="height:130px">
                                    <div class="rounded-t" style="width:17px; height:{{ max(2, $b['bar_plus']) }}%; background:#0f766e"></div>
                                    <div class="rounded-t" style="width:17px; height:{{ max(2, $b['bar_min']) }}%; background:#b0453b"></div>
                                </div>
                                <div class="text-[11.5px] text-slate-500 whitespace-nowrap">{{ $b['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-4 text-[12px] text-slate-500 mt-2">
                        <span><span class="inline-block w-2.5 h-2.5 rounded-sm mr-1.5" style="background:#0f766e"></span>poin masuk (positif)</span>
                        <span><span class="inline-block w-2.5 h-2.5 rounded-sm mr-1.5" style="background:#b0453b"></span>poin keluar (negatif)</span>
                    </div>
                </div>

                {{-- Tabel per bulan (PC) --}}
                <div class="hidden sm:block bg-white border border-slate-200 rounded-xl overflow-x-auto mb-3">
                    <table class="w-full text-[13px]">
                        <thead class="bg-slate-50 text-[11.5px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="text-left px-3 py-2">Bulan</th>
                                <th class="text-right px-3 py-2">Entri</th>
                                <th class="text-right px-3 py-2">Total poin</th>
                                <th class="text-right px-3 py-2">Entri plus</th>
                                <th class="text-right px-3 py-2">Santri dapat plus</th>
                                <th class="text-right px-3 py-2">Entri minus</th>
                                <th class="text-right px-3 py-2">Santri dapat minus</th>
                                <th class="text-right px-3 py-2">Δ poin vs bulan lalu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($bulanan as $b)
                                <tr>
                                    <td class="px-3 py-2 font-semibold text-slate-800">{{ $b['label'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($b['entri'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums font-semibold {{ $b['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">
                                        {{ $b['poin'] > 0 ? '+' : '' }}{{ number_format($b['poin'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($b['entri_plus'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ $b['siswa_plus'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($b['entri_min'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ $b['siswa_min'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">
                                        @if($b['delta_poin'] === null)
                                            —
                                        @else
                                            <span class="{{ $b['delta_poin'] < 0 ? 'text-red-700' : 'text-teal-700' }} font-semibold">
                                                {{ $b['delta_poin'] > 0 ? '+' : '' }}{{ number_format($b['delta_poin'], 0, ',', '.') }}
                                                {{ $b['delta_poin'] < 0 ? '▼' : '▲' }}
                                                @if($b['delta_persen'] !== null)({{ $b['delta_persen'] > 0 ? '+' : '' }}{{ number_format($b['delta_persen'], 1, ',', '.') }}%)@endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kartu per bulan (HP) --}}
                <div class="sm:hidden space-y-2.5 mb-3">
                    @foreach($bulanan as $b)
                        <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                            <div class="flex items-baseline justify-between">
                                <span class="font-bold text-slate-800 text-[13.5px]">{{ $b['label'] }}</span>
                                <span class="font-bold {{ $b['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">
                                    {{ $b['poin'] > 0 ? '+' : '' }}{{ number_format($b['poin'], 0, ',', '.') }} poin
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 mt-2 text-[12.5px]">
                                <div><span class="block text-[10.5px] uppercase tracking-wide text-slate-400">Entri</span>{{ number_format($b['entri'], 0, ',', '.') }}</div>
                                <div><span class="block text-[10.5px] uppercase tracking-wide text-slate-400">Entri plus / minus</span>{{ $b['entri_plus'] }} / {{ $b['entri_min'] }}</div>
                                <div><span class="block text-[10.5px] uppercase tracking-wide text-slate-400">Santri dapat plus</span>{{ $b['siswa_plus'] }}</div>
                                <div><span class="block text-[10.5px] uppercase tracking-wide text-slate-400">Santri dapat minus</span>{{ $b['siswa_min'] }}</div>
                                <div class="col-span-2"><span class="block text-[10.5px] uppercase tracking-wide text-slate-400">Δ vs bulan lalu</span>
                                    @if($b['delta_poin'] === null) — @else
                                        <span class="{{ $b['delta_poin'] < 0 ? 'text-red-700' : 'text-teal-700' }} font-semibold">
                                            {{ $b['delta_poin'] > 0 ? '+' : '' }}{{ number_format($b['delta_poin'], 0, ',', '.') }}
                                            {{ $b['delta_poin'] < 0 ? '▼' : '▲' }}
                                            @if($b['delta_persen'] !== null)({{ $b['delta_persen'] > 0 ? '+' : '' }}{{ number_format($b['delta_persen'], 1, ',', '.') }}%)@endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ===================== SANTRI PLUS / MINUS ===================== --}}
            @if(count($bulanan) > 0)
                <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Yang menerima poin plus vs minus tiap bulan
                    <span class="font-normal text-[12px] text-slate-500">— dari {{ $jumlah_santri }} santri pada penyaring ini</span>
                </h4>
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead class="bg-slate-50 text-[11.5px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Bulan</th>
                                    <th class="text-right px-3 py-2">Dapat PLUS</th>
                                    <th class="text-right px-3 py-2">Dapat MINUS</th>
                                    <th class="text-right px-3 py-2">Belum dapat poin apa pun</th>
                                    <th class="text-left px-3 py-2">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($bulanan as $b)
                                    @php
                                        $belum = max(0, $jumlah_santri - $b['siswa_terlibat']);
                                        $persenMinus = $jumlah_santri > 0 ? round($b['siswa_min'] / $jumlah_santri * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-800">{{ $b['label'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums text-teal-700 font-semibold">{{ $b['siswa_plus'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums text-red-700 font-semibold">{{ $b['siswa_min'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $belum }}</td>
                                        <td class="px-3 py-2 text-[12px] text-slate-500">
                                            @if($persenMinus >= 80) hampir semua santri pernah kena minus ({{ $persenMinus }}%)
                                            @elseif($persenMinus >= 40) sebagian santri kena minus ({{ $persenMinus }}%)
                                            @else relatif sedikit yang kena minus ({{ $persenMinus }}%)
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="sm:hidden text-[11.5px] text-slate-400 px-3 pb-2">geser tabel ke samping →</p>
                </div>
            @endif

            {{-- ===================== AKTIVITAS TERBANYAK ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Aktivitas paling banyak dilakukan
                <span class="font-normal text-[12px] text-slate-500">— beserta catatan yang paling sering diisi</span>
            </h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                @foreach(['positif' => ['👍 Poin POSITIF teratas', 'text-teal-700'], 'negatif' => ['👎 Poin NEGATIF teratas', 'text-red-700']] as $kunci => $info)
                    <div class="bg-white border border-slate-200 rounded-xl p-4">
                        <h5 class="text-[13.5px] font-bold {{ $info[1] }} mb-1">{{ $info[0] }}</h5>
                        <ul class="divide-y divide-slate-100">
                            @forelse($aktivitas[$kunci] as $a)
                                <li class="py-2.5">
                                    <div class="flex justify-between gap-3 items-baseline">
                                        <span class="font-semibold text-slate-800 text-[13px]">{{ $a['nama'] }}</span>
                                        <span class="shrink-0 text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 whitespace-nowrap">
                                            {{ $a['poin_kriteria'] > 0 ? '+' : '' }}{{ $a['poin_kriteria'] }} · {{ number_format($a['jumlah'], 0, ',', '.') }} entri · {{ $a['siswa'] }} santri
                                        </span>
                                    </div>
                                    @if(!empty($a['catatan']))
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            @foreach($a['catatan'] as $c)
                                                <span class="text-[11.5px] px-2 py-0.5 rounded-md bg-blue-50 text-slate-700">{{ $c['teks'] }} ({{ $c['jumlah'] }}×)</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[11.5px] text-slate-400 mt-1">tanpa catatan</div>
                                    @endif
                                </li>
                            @empty
                                <li class="py-3 text-[12.5px] text-slate-500">Belum ada entri.</li>
                            @endforelse
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- ===================== CATATAN TERBANYAK ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Catatan yang paling sering ditulis</h4>
            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden mb-3">
                <div class="overflow-x-auto">
                    <table class="w-full text-[13px]">
                        <thead class="bg-slate-50 text-[11.5px] uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="text-left px-3 py-2">Catatan</th>
                                <th class="text-left px-3 py-2">Menempel pada kriteria</th>
                                <th class="text-right px-3 py-2">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($catatan as $c)
                                <tr>
                                    <td class="px-3 py-2 text-slate-800">{{ $c['teks'] }}</td>
                                    <td class="px-3 py-2 text-slate-500 text-[12px]">{{ $c['kriteria'] }}</td>
                                    <td class="px-3 py-2 text-right tabular-nums">{{ $c['jumlah'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-3 text-[12.5px] text-slate-500">Belum ada catatan pada periode ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="sm:hidden text-[11.5px] text-slate-400 px-3 pb-2">geser tabel ke samping →</p>
            </div>

            {{-- ===================== SEBARAN ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Sebaran per kelas &amp; grup binaan</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead class="bg-slate-50 text-[11.5px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Kelas</th>
                                    <th class="text-right px-3 py-2">Santri</th>
                                    <th class="text-right px-3 py-2">Entri</th>
                                    <th class="text-right px-3 py-2">Total poin</th>
                                    <th class="text-right px-3 py-2">Rata-rata</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($per_kelas as $k)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-800">{{ $k['kelas'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $k['siswa'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ number_format($k['entri'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums font-semibold {{ $k['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">{{ $k['poin'] > 0 ? '+' : '' }}{{ $k['poin'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $k['rata'] > 0 ? '+' : '' }}{{ $k['rata'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-3 py-3 text-[12.5px] text-slate-500">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="sm:hidden text-[11.5px] text-slate-400 px-3 pb-2">geser tabel ke samping →</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-[13px]">
                            <thead class="bg-slate-50 text-[11.5px] uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="text-left px-3 py-2">Grup</th>
                                    <th class="text-right px-3 py-2">Anggota</th>
                                    <th class="text-right px-3 py-2">Entri</th>
                                    <th class="text-right px-3 py-2">Total poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($per_grup as $g)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-slate-800">{{ $g['grup'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ $g['anggota'] }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{{ number_format($g['entri'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-right tabular-nums font-semibold {{ $g['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">{{ $g['poin'] > 0 ? '+' : '' }}{{ $g['poin'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-3 py-3 text-[12.5px] text-slate-500">Belum ada grup aktif.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="sm:hidden text-[11.5px] text-slate-400 px-3 pb-2">geser tabel ke samping →</p>
                </div>
            </div>

            {{-- ===================== PAPAN SANTRI ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Santri paling menonjol pada periode ini</h4>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-3">
                @foreach([
                    'teratas' => ['🏅 Poin tertinggi', 'text-teal-700'],
                    'minus_teratas' => ['⚠️ Paling banyak poin negatif', 'text-red-700'],
                    'terbawah' => ['🔻 Poin terendah', 'text-red-700'],
                ] as $kunci => $info)
                    <div class="bg-white border border-slate-200 rounded-xl p-4">
                        <h5 class="text-[13px] font-bold {{ $info[1] }} mb-1.5">{{ $info[0] }}</h5>
                        <ol class="text-[12.5px] divide-y divide-slate-100">
                            @forelse($siswa[$kunci] as $i => $s)
                                <li class="py-1.5 flex justify-between gap-2">
                                    <span class="text-slate-700">{{ $i + 1 }}. {{ $s['nama'] }} <span class="text-slate-400">({{ $s['kelas'] }})</span></span>
                                    <span class="tabular-nums font-semibold {{ $s['poin'] < 0 ? 'text-red-700' : 'text-teal-700' }}">{{ $s['poin'] > 0 ? '+' : '' }}{{ $s['poin'] }}</span>
                                </li>
                            @empty
                                <li class="py-2 text-[12.5px] text-slate-500">Belum ada data.</li>
                            @endforelse
                        </ol>
                    </div>
                @endforeach
            </div>

            {{-- ===================== KUALITAS DATA ===================== --}}
            <h4 class="text-[14px] font-bold text-slate-800 mb-2 mt-5">Kualitas data
                <span class="font-normal text-[12px] text-slate-500">— temuan, bukan kesalahan santri</span>
            </h4>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-3 text-[13px] text-amber-900">
                @if($kualitas['entri_salah_kriteria'] > 0)
                    <p class="font-semibold mb-1.5">⚠️ {{ number_format($kualitas['entri_salah_kriteria'], 0, ',', '.') }} entri poin positif memakai kriteria yang tidak sesuai catatannya</p>
                    <p class="mb-2">Catatannya kegiatan asrama, tetapi menempel pada kriteria lain — sisa mekanisme lama sebelum kriteria Kamar Terbersih dibuat. Daftar di bawah untuk dirapikan petugas/TU lewat menu input poin (data TIDAK diubah otomatis).</p>
                    <ul class="list-disc ml-5 space-y-0.5">
                        @foreach($kualitas['contoh_salah_kriteria'] as $teks => $jumlah)
                            <li>{{ $teks }} <span class="text-amber-700">({{ $jumlah }}×)</span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="font-semibold">✅ Tidak ditemukan entri yang catatannya tidak cocok dengan kriterianya.</p>
                @endif

                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-[12.5px]">
                    <div class="bg-white/70 rounded-lg px-3 py-2">
                        Entri tanpa catatan: <strong>{{ number_format($kualitas['entri_tanpa_catatan'], 0, ',', '.') }}</strong>
                        @if($ringkasan['entri'] > 0)
                            ({{ round($kualitas['entri_tanpa_catatan'] / $ringkasan['entri'] * 100) }}%)
                        @endif
                    </div>
                    <div class="bg-white/70 rounded-lg px-3 py-2">
                        Kriteria belum pernah dipakai: <strong>{{ count($kualitas['kriteria_tak_terpakai']) }}</strong>
                    </div>
                </div>

                @if(count($kualitas['kriteria_tak_terpakai']) > 0)
                    <div class="mt-2 text-[12px] text-amber-800">
                        @foreach($kualitas['kriteria_tak_terpakai'] as $k)
                            <span class="inline-block bg-white/70 rounded-md px-2 py-0.5 mr-1.5 mb-1">{{ $k['kategori'] }} {{ $k['poin'] > 0 ? '+' : '' }}{{ $k['poin'] }} · {{ $k['nama'] }}</span>
                        @endforeach
                    </div>
                @endif
            </div>

            <p class="text-[12px] text-slate-400 mt-6">
                Angka dihitung langsung dari entri poin (sr_point_entries) pada {{ $labelPeriode }}.
                Periode mengikuti semester tahun ajaran berjalan; pilih “Semua data” untuk melihat seluruh riwayat.
            </p>
        </div>
    </div>
</x-app-layout>
