<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">✅ Kelengkapan Penilaian Adab &amp; Keasramaan</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Setiap musyrif/musyrifah wajib menilai seluruh santri divisinya — putra menilai putra, putri menilai putri</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <a href="{{ route('penilaian.rekap', ['periode' => $periodeId]) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">📊 Rekap</a>
                    <a href="{{ route('penilaian.rapot', ['periode' => $periodeId]) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">🖨️ Rapot</a>
                </div>
            </div>

            <form action="{{ route('penilaian.kelengkapan') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-4 flex flex-wrap items-end gap-2.5">
                <div class="min-w-[220px]">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode</label>
                    <select name="periode" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" @selected($periodeId === $p->id)>{{ $p->nama }}@if($p->aktif) — aktif @endif</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="h-10 px-4 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>
                <div class="text-[11.5px] text-slate-400 pb-2.5">
                    {{ $totalSantri }} santri asrama aktif · {{ count($musyrifRows) }} akun musyrif/musyrifah
                </div>
            </form>

            {{-- Ringkasan per divisi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                @foreach($ringkasDivisi as $div => $r)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-[13.5px] font-bold text-slate-800">
                                {{ $div === 'putri' ? '🧕' : '👨‍🎓' }} Divisi {{ $r['label'] }}
                            </div>
                            <div class="text-[11.5px] text-slate-400">{{ $r['santri'] }} santri · {{ $r['musyrif'] }} penilai</div>
                        </div>
                        @foreach($r['jenis'] as $kunci => $j)
                            <div class="mb-3 last:mb-0">
                                <div class="flex items-center justify-between text-[12px] mb-1">
                                    <span class="font-semibold text-slate-600">{{ $j['label'] }}</span>
                                    <span class="text-slate-500">{{ $j['masuk'] }} / {{ $j['target'] }} penilaian · <b class="{{ $j['persen'] >= 100 ? 'text-emerald-600' : 'text-slate-700' }}">{{ $j['persen'] }}%</b></span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $j['persen'] >= 100 ? 'bg-emerald-500' : ($j['persen'] > 0 ? 'bg-blue-700' : 'bg-slate-300') }}" style="width: {{ $j['persen'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">
                            Target = {{ $r['santri'] }} santri × {{ $r['musyrif'] }} penilai. Rapor menampilkan rata-rata dari semua penilai.
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Per musyrif --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                    <h4 class="text-[13.5px] font-bold text-slate-800">Kelengkapan per musyrif/musyrifah</h4>
                </div>

                {{-- Desktop --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-[12.5px]">
                        <thead class="bg-white">
                            <tr class="text-left text-[10.5px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-4 py-2.5">Musyrif / Musyrifah</th>
                                @foreach($jenisList as $kunci => $label)
                                    <th class="px-3 py-2.5">{{ $label }}</th>
                                @endforeach
                                <th class="px-3 py-2.5">Status lembar</th>
                                <th class="px-3 py-2.5">Terakhir mengisi</th>
                                <th class="px-3 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($musyrifRows as $m)
                                <tr class="border-b border-slate-50 last:border-0">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $m['nama'] }}</div>
                                        <div class="text-[11px] text-slate-400">
                                            {{ $m['labelDivisi'] }} · {{ $m['jumlah_kamar'] }} kamar
                                            @if(! $m['divisi']) <span class="text-amber-600">(perlu dipetakan)</span> @endif
                                        </div>
                                    </td>
                                    @foreach($jenisList as $kunci => $label)
                                        @php $j = $m['jenis'][$kunci]; @endphp
                                        <td class="px-3 py-3">
                                            <div class="text-[12px] font-semibold {{ $j['kurang'] === 0 && $m['total_wajib'] > 0 ? 'text-emerald-600' : 'text-slate-700' }}">
                                                {{ $j['terisi'] }} / {{ $m['total_wajib'] }}
                                            </div>
                                            <div class="h-1.5 w-24 rounded-full bg-slate-100 overflow-hidden mt-1">
                                                <div class="h-1.5 rounded-full {{ $j['kurang'] === 0 && $m['total_wajib'] > 0 ? 'bg-emerald-500' : 'bg-blue-700' }}" style="width: {{ $j['persen'] }}%"></div>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-3">
                                        @if(! $m['divisi'])
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 text-[11px] font-bold">Belum dipetakan</span>
                                        @elseif($m['lengkap'])
                                            <span class="inline-flex items-center rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-1 text-[11px] font-bold">Lengkap</span>
                                        @else
                                            <span class="inline-flex items-center rounded-lg bg-amber-50 text-amber-700 border border-amber-200 px-2 py-1 text-[11px] font-bold">Kurang {{ collect($m['jenis'])->sum('kurang') }} santri</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-slate-500 text-[11.5px]">
                                        @php $terakhir = collect($m['jenis'])->pluck('terakhir')->filter()->max(); @endphp
                                        @if($terakhir)
                                            {{ \Illuminate\Support\Carbon::parse($terakhir)->diffForHumans() }}
                                        @else
                                            <span class="text-slate-300">belum mulai</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        @php $sesiAdab = $m['jenis']['adab']['sesi_id'] ?? null; @endphp
                                        @if($sesiAdab)
                                            <a href="{{ route('penilaian.sesi', $sesiAdab) }}"
                                               class="inline-flex items-center justify-center h-7 px-2.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-[11.5px] font-semibold hover:bg-slate-50 transition">Lihat lembar</a>
                                        @else
                                            <span class="text-[11.5px] text-slate-300">belum ada lembar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($jenisList) + 4 }}" class="px-4 py-6 text-center text-slate-400 text-[13px]">Belum ada akun musyrif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- HP --}}
                <div class="md:hidden divide-y divide-slate-100">
                    @foreach($musyrifRows as $m)
                        <div class="px-3 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="text-[13px] font-semibold text-slate-800 truncate">{{ $m['nama'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $m['labelDivisi'] }} · {{ $m['jumlah_kamar'] }} kamar</div>
                                </div>
                                @if(! $m['divisi'])
                                    <span class="shrink-0 inline-flex items-center rounded-lg bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 text-[10.5px] font-bold">Belum dipetakan</span>
                                @elseif($m['lengkap'])
                                    <span class="shrink-0 inline-flex items-center rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 text-[10.5px] font-bold">Lengkap</span>
                                @else
                                    <span class="shrink-0 inline-flex items-center rounded-lg bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 text-[10.5px] font-bold">Kurang {{ collect($m['jenis'])->sum('kurang') }}</span>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach($jenisList as $kunci => $label)
                                    @php $j = $m['jenis'][$kunci]; @endphp
                                    <div class="rounded-lg border border-slate-100 bg-slate-50/60 px-2 py-1.5">
                                        <div class="text-[10.5px] text-slate-400 uppercase tracking-wider">{{ $label }}</div>
                                        <div class="text-[12.5px] font-semibold text-slate-700">{{ $j['terisi'] }} / {{ $m['total_wajib'] }}</div>
                                        <div class="h-1.5 rounded-full bg-white overflow-hidden mt-1">
                                            <div class="h-1.5 rounded-full {{ $j['kurang'] === 0 && $m['total_wajib'] > 0 ? 'bg-emerald-500' : 'bg-blue-700' }}" style="width: {{ $j['persen'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Per kamar --}}
            @foreach($jenisList as $kunci => $label)
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-4">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Kelengkapan per kamar — {{ $label }}</h4>
                        <span class="text-[11px] text-slate-400">santri lengkap = sudah dinilai semua musyrif divisinya</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-[12.5px]">
                            <thead>
                                <tr class="text-left text-[10.5px] uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                    <th class="px-4 py-2.5">Kamar</th>
                                    <th class="px-3 py-2.5">Divisi</th>
                                    <th class="px-3 py-2.5">Santri</th>
                                    <th class="px-3 py-2.5">Penilaian masuk</th>
                                    <th class="px-3 py-2.5">Lengkap</th>
                                    <th class="px-3 py-2.5">Belum lengkap</th>
                                    <th class="px-3 py-2.5">Musyrif binaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kamarPerJenis[$kunci] as $k)
                                    <tr class="border-b border-slate-50 last:border-0">
                                        <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $k['nama'] }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ ucfirst($k['kategori']) }}</td>
                                        <td class="px-3 py-2.5 text-slate-600">{{ $k['santri'] }}</td>
                                        <td class="px-3 py-2.5">
                                            <span class="text-slate-700 font-semibold">{{ $k['penilaian_masuk'] }}</span><span class="text-slate-400"> / {{ $k['target'] }}</span>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            @if($k['belum'] === 0 && $k['santri'] > 0)
                                                <span class="text-emerald-600 font-semibold">{{ $k['penuh'] }}</span>
                                            @else
                                                <span class="text-slate-600">{{ $k['penuh'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5">
                                            @if($k['belum'] > 0)
                                                <span class="inline-flex items-center rounded-lg bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 text-[11px] font-bold">{{ $k['belum'] }} santri</span>
                                            @else
                                                <span class="text-[11px] text-emerald-600 font-semibold">lengkap ✓</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $k['musyrif_binaan'] ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <p class="text-[11.5px] text-slate-400 leading-relaxed">
                Catatan: satu musyrif hanya bisa mengisi divisinya sendiri (putra menilai putra, putri menilai putri).
                Rapor Adab &amp; Keasramaan memakai rata-rata skor dari semua musyrif/musyrifah yang menilai.
                Jumlah aspek per jenis saat ini: {{ collect($jumlahKriteria)->map(fn ($n, $k) => $jenisList[$k] . ' ' . $n . ' aspek')->implode(' · ') }}.
            </p>
        </div>
    </div>
</x-app-layout>
