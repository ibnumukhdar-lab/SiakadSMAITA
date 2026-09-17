<x-app-layout>
    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ================= JUDUL + CETAK ================= --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🏆 Peringkat Kebersihan Kamar</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Rekap poin kebersihan per bulan — juara &amp; kamar yang perlu perhatian</p>
                </div>
                <button type="button" onclick="window.print()" class="no-print inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    🖨️ Cetak
                </button>
            </div>

            {{-- ================= FILTER ================= --}}
            <form method="GET" action="{{ route('asrama.peringkat') }}" class="no-print bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="w-full sm:w-auto">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bulan</label>
                        <select name="bulan" class="h-10 w-full sm:w-56 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">
                            @forelse($daftarBulan as $bulan => $jml)
                                <option value="{{ $bulan }}" {{ $bulan === $bulanTerpilih ? 'selected' : '' }}>
                                    {{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulan) }} ({{ $jml }} sidak)
                                </option>
                            @empty
                                <option value="">Belum ada data</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="w-full sm:w-auto">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Divisi</label>
                        <div class="flex items-center gap-1.5 h-10">
                            @foreach(['semua' => 'Semua', 'putra' => '👦 Putra', 'putri' => '👧 Putri'] as $nilai => $teks)
                                <label class="cursor-pointer">
                                    <input type="radio" name="divisi" value="{{ $nilai }}" class="peer sr-only" {{ $divisi === $nilai ? 'checked' : '' }}>
                                    <span class="inline-flex items-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-[13px] font-semibold text-slate-600 peer-checked:bg-blue-50 peer-checked:text-blue-900 peer-checked:border-blue-200 transition">{{ $teks }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:w-auto">
                        Terapkan
                    </button>
                </div>
            </form>

            @if(empty($daftarBulan))
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum Ada Inspeksi yang Difinalkan</h3>
                    <p class="text-sm text-slate-400">Peringkat akan muncul setelah musyrif memfinalkan inspeksi harian.</p>
                </div>
            @else

                {{-- ================= RINGKASAN BULAN ================= --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Bulan</div>
                        <div class="text-base font-extrabold text-slate-900 mt-0.5">{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulanTerpilih) }}</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Sidak</div>
                        <div class="text-base font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['sidak'] }} lembar</div>
                        <div class="text-[11px] font-semibold text-slate-400 mt-0.5">👦 {{ $ringkasan['putra'] }} · 👧 {{ $ringkasan['putri'] }}</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kamar Masuk Peringkat</div>
                        <div class="text-base font-extrabold text-slate-900 mt-0.5">{{ count($teratas) }} kamar</div>
                        <div class="text-[11px] font-semibold text-slate-400 mt-0.5">poin tertinggi bulan ini</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sidak Terakhir</div>
                        <div class="text-base font-extrabold text-slate-900 mt-0.5">
                            {{ $ringkasan['terakhir'] ? \Carbon\Carbon::parse($ringkasan['terakhir'])->translatedFormat('d M Y') : '—' }}
                        </div>
                    </div>
                </div>

                {{-- ================= JUARA ================= --}}
                @if(count($teratas))
                    @php $juara = $teratas[0]; $kamarJuara = $kamar[$juara['kamar_id']] ?? null; @endphp
                    <div class="bg-gradient-to-br from-blue-900 to-slate-800 rounded-2xl shadow-sm px-5 py-5 mb-5 text-white">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-[0.14em] text-blue-200">🥇 Juara Kebersihan · {{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulanTerpilih) }}</div>
                                <div class="text-2xl font-extrabold tracking-tight mt-1">{{ $kamarJuara->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                <div class="text-[13px] text-blue-100 mt-1">
                                    {{ ucfirst($kamarJuara->kategori ?? '-') }} · {{ $kamarJuara->musyrif->name ?? 'Musyrif belum diatur' }}
                                    · {{ (int) ($penghuni[$juara['kamar_id']] ?? 0) }} penghuni
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-black tracking-tight">{{ $juara['poin'] > 0 ? '+' . $juara['poin'] : $juara['poin'] }}</div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-blue-200">poin bersih</div>
                                <div class="text-[12px] text-blue-100 mt-1">👑 {{ $juara['bersih'] }}× terbersih · ⚠️ {{ $juara['kotor'] }}× terkotor</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================= DUA TABEL PERINGKAT ================= --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

                    {{-- 5 poin tertinggi --}}
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden cetak-kartu">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-extrabold text-slate-900">🏅 5 Kamar Poin Tertinggi</h4>
                            <span class="text-[11px] font-bold text-slate-400">{{ $divisi === 'semua' ? 'Putra &amp; Putri' : ucfirst($divisi) }}</span>
                        </div>

                        @if(count($teratas))
                            <div class="divide-y divide-slate-100">
                                @foreach($teratas as $i => $r)
                                    @php $kr = $kamar[$r['kamar_id']] ?? null; @endphp
                                    <div class="px-5 py-3.5 flex items-center gap-3">
                                        <div class="w-8 text-center text-[15px] font-black text-slate-400 shrink-0">{{ $loop->iteration }}</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[13.5px] font-bold text-slate-900 truncate">{{ $kr->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                                {{ ucfirst($kr->kategori ?? '-') }} · 👑 {{ $r['bersih'] }}× terbersih · ⚠️ {{ $r['kotor'] }}× terkotor
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[13px] font-black {{ $r['poin'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                {{ $r['poin'] > 0 ? '+' . $r['poin'] : $r['poin'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="px-5 py-10 text-center text-sm text-slate-400">Belum ada kamar yang masuk peringkat pada bulan &amp; divisi ini.</div>
                        @endif
                    </div>

                    {{-- 5 poin terendah --}}
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden cetak-kartu">
                        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-3">
                            <h4 class="text-sm font-extrabold text-slate-900">⚠️ 5 Kamar Poin Terendah</h4>
                            <span class="text-[11px] font-bold text-slate-400">perlu perhatian</span>
                        </div>

                        @if(count($terbawah))
                            <div class="divide-y divide-slate-100">
                                @foreach($terbawah as $r)
                                    @php $kr = $kamar[$r['kamar_id']] ?? null; @endphp
                                    <div class="px-5 py-3.5 flex items-center gap-3">
                                        <div class="w-8 text-center text-[15px] font-black text-slate-300 shrink-0">{{ $loop->iteration }}</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[13.5px] font-bold text-slate-900 truncate">{{ $kr->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                                {{ ucfirst($kr->kategori ?? '-') }} · 👑 {{ $r['bersih'] }}× terbersih · ⚠️ {{ $r['kotor'] }}× terkotor
                                            </div>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[13px] font-black {{ $r['poin'] < 0 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                {{ $r['poin'] > 0 ? '+' . $r['poin'] : $r['poin'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="px-5 py-10 text-center text-sm text-slate-400">Semua kamar sudah masuk daftar poin tertinggi.</div>
                        @endif
                    </div>
                </div>

                {{-- ================= REKAP SEMUA BULAN ================= --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden cetak-kartu">
                    <div class="px-5 py-3.5 border-b border-slate-100">
                        <h4 class="text-sm font-extrabold text-slate-900">🗓️ Rekap Semua Bulan</h4>
                        <p class="text-[12px] text-slate-400 mt-0.5">Bandingkan juara tiap bulan — klik “Lihat” untuk membuka rincian bulan itu</p>
                    </div>

                    {{-- Tampilan HP: kartu per bulan (tabel 6 kolom tidak muat di layar kecil) --}}
                    <div class="lg:hidden divide-y divide-slate-100">
                        @foreach($rekapBulan as $bulan => $data)
                            <div class="px-5 py-4 {{ $bulan === $bulanTerpilih ? 'bg-blue-50/50' : '' }}">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <div>
                                        <div class="text-[13.5px] font-extrabold text-slate-900">{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulan) }}</div>
                                        <div class="text-[11px] font-semibold text-slate-400">🗓️ {{ $data['sidak'] }}× sidak</div>
                                    </div>
                                    <a href="{{ route('asrama.peringkat', ['bulan' => $bulan, 'divisi' => $divisi]) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">Lihat</a>
                                </div>
                                <div class="space-y-1.5">
                                    @foreach(['🥇', '🥈', '🥉'] as $n => $medali)
                                        @php $j = $data['juara'][$n] ?? null; @endphp
                                        @if($j)
                                            @php $kj = $kamar[$j['kamar_id']] ?? null; @endphp
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-[12.5px] font-semibold text-slate-600 truncate">{{ $medali }} {{ $kj->nama_kamar ?? 'Kamar Dihapus' }}</span>
                                                <span class="text-[12px] font-bold {{ $j['poin'] > 0 ? 'text-emerald-600' : 'text-slate-400' }} shrink-0">{{ $j['poin'] > 0 ? '+' . $j['poin'] : $j['poin'] }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tampilan lebar: tabel ringkas semua bulan --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50/80">
                                <tr class="text-left text-[11px] font-black uppercase tracking-wider text-slate-400">
                                    <th class="px-5 py-3 whitespace-nowrap">Bulan</th>
                                    <th class="px-5 py-3 whitespace-nowrap">Sidak</th>
                                    <th class="px-5 py-3 whitespace-nowrap">🥇 Juara 1</th>
                                    <th class="px-5 py-3 whitespace-nowrap">🥈 Juara 2</th>
                                    <th class="px-5 py-3 whitespace-nowrap">🥉 Juara 3</th>
                                    <th class="px-5 py-3 no-print"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($rekapBulan as $bulan => $data)
                                    <tr class="{{ $bulan === $bulanTerpilih ? 'bg-blue-50/50' : '' }}">
                                        <td class="px-5 py-3 font-bold text-slate-800 whitespace-nowrap">
                                            {{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulan) }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $data['sidak'] }}×</td>
                                        @for($n = 0; $n < 3; $n++)
                                            @php $j = $data['juara'][$n] ?? null; @endphp
                                            <td class="px-5 py-3 whitespace-nowrap">
                                                @if($j)
                                                    @php $kj = $kamar[$j['kamar_id']] ?? null; @endphp
                                                    <span class="font-semibold text-slate-700">{{ $kj->nama_kamar ?? 'Kamar Dihapus' }}</span>
                                                    <span class="text-[12px] font-bold {{ $j['poin'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">({{ $j['poin'] > 0 ? '+' . $j['poin'] : $j['poin'] }})</span>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                        @endfor
                                        <td class="px-5 py-3 text-right no-print">
                                            <a href="{{ route('asrama.peringkat', ['bulan' => $bulan, 'divisi' => $divisi]) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">Lihat</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 mt-4">
                    Poin dihitung dari hasil finalisasi sidak: kamar terbersih +1 dan kamar terkotor −1 untuk setiap penghuni kamar — sama dengan poin yang masuk ke Student Root.
                </p>
            @endif

        </div>
    </div>

    <style>
        @media print {
            .no-print, aside, nav, header, footer { display: none !important; }
            body { background: #fff !important; }
            .cetak-kartu { break-inside: avoid; page-break-inside: avoid; }
            .max-w-\[1200px\] { max-width: 100% !important; }
        }
    </style>
</x-app-layout>
