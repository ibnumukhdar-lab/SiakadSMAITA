<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📈 Analitik Kamar Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Rata-rata skor, kriteria terlemah, tren bulanan, dan temuan data</p>
                </div>
                <a href="{{ route('asrama.analitik.rapor', ['bulan' => $bulanTerpilih, 'kategori' => $kategori]) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">🧾 Rapor Asrama</a>
            </div>

            <form method="GET" action="{{ route('asrama.analitik') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bulan</label>
                        <select name="bulan" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            @foreach($daftarBulan as $b)
                                <option value="{{ $b }}" @selected($bulanTerpilih === $b)>{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($b) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Divisi</label>
                        <select name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua divisi</option>
                            <option value="putra" @selected($kategori === 'putra')>Putra</option>
                            <option value="putri" @selected($kategori === 'putri')>Putri</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Tampilkan</button>
                </div>
            </form>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">📝 Sidak</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['sidak'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">⭐ Rata-rata</div>
                    <div class="text-xl font-extrabold text-blue-900 mt-0.5">
                        {{ $ringkasan['rata'] !== null ? $ringkasan['rata'] : '-' }}<span class="text-slate-400 text-sm font-semibold">/25</span>
                    </div>
                    @if($ringkasan['persen'] !== null)
                        <div class="text-[11px] font-bold {{ $ringkasan['persen'] >= 70 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $ringkasan['persen'] }}%</div>
                    @endif
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">🛏️ Kamar</div>
                    <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $ringkasan['kamar'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">📭 Kosong</div>
                    <div class="text-xl font-extrabold {{ $ringkasan['kamarKosong'] > 0 ? 'text-amber-600' : 'text-slate-400' }} mt-0.5">{{ $ringkasan['kamarKosong'] }}</div>
                </div>
            </div>

            {{-- Rata-rata kriteria keseluruhan --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 mb-5">
                <h4 class="text-[15px] font-bold text-slate-800 mb-1">Rata-rata per Kriteria ({{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulanTerpilih) }})</h4>
                <p class="text-[12px] text-slate-500 mb-4">Skala 1–5. Batang pendek = item yang paling sering kurang.</p>
                <div class="space-y-2.5">
                    @foreach(\App\Http\Controllers\AsramaAnalitikController::KRITERIA as $kolom => $label)
                        @php
                            $nilai = $rataKriteriaGlobal[$kolom];
                            $persenBar = $nilai !== null ? min(100, (int) round($nilai / 5 * 100)) : 0;
                            $warna = $nilai === null ? 'bg-slate-200' : ($nilai >= 4 ? 'bg-emerald-500' : ($nilai >= 3 ? 'bg-blue-600' : 'bg-rose-500'));
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-52 shrink-0 text-[13px] font-semibold text-slate-600">{{ $label }}</div>
                            <div class="flex-1">
                                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $warna }}" style="width: {{ $persenBar }}%"></div>
                                </div>
                            </div>
                            <div class="w-12 text-right text-[13px] font-bold text-slate-700">{{ $nilai ?? '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if(empty($perKamar))
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl mb-5">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum ada inspeksi pada bulan ini</h3>
                    <p class="text-sm text-slate-400">Angka akan terisi setelah ada inspeksi kamar yang difinalisasi.</p>
                </div>
            @else
                <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-5">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h4 class="text-[15px] font-bold text-slate-800">Rekap per Kamar</h4>
                        <p class="text-[12px] text-slate-500 mt-0.5">Diurutkan dari nilai terendah — yang paling perlu dibenahi di atas.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[1050px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kamar</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Penghuni</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Sidak</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Nilai</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">👑 / ⚠️</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kriteria Terlemah</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tren 6 Bulan</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($perKamar as $row)
                                    @php
                                        $k = $row['kamar'];
                                        $warnaNilai = $row['persen'] === null ? 'text-slate-400' : ($row['persen'] >= 70 ? 'text-emerald-600' : 'text-rose-600');
                                    @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-bold text-slate-800">{{ $k->nama_kamar }}</div>
                                            <div class="text-[11px] text-slate-400 font-semibold uppercase">{{ $k->kategori }} · {{ $k->musyrif->name ?? 'tanpa musyrif' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $k->penghuni_count }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ $row['sidak'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-sm font-bold {{ $warnaNilai }}">{{ $row['rata'] !== null ? $row['rata'] : '-' }}</span>
                                            @if($row['persen'] !== null)
                                                <span class="block text-[11px] font-bold {{ $warnaNilai }}">{{ $row['persen'] }}%</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[13px] text-center font-semibold">
                                            <span class="text-emerald-600">{{ $row['terbersih'] }}</span>
                                            <span class="text-slate-300">/</span>
                                            <span class="{{ $row['terkotor'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $row['terkotor'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-[13px] font-medium text-slate-600">{{ $row['terlemah'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-end gap-1">
                                                @foreach($row['tren'] as $bulanTren => $nilaiTren)
                                                    @php
                                                        $tinggi = $nilaiTren !== null ? max(4, (int) round($nilaiTren / 25 * 34)) : 3;
                                                        $warnaTren = $nilaiTren === null ? 'bg-slate-200' : ($nilaiTren >= 17.5 ? 'bg-emerald-500' : 'bg-rose-400');
                                                    @endphp
                                                    <div class="w-3.5 rounded-t {{ $warnaTren }}" style="height: {{ $tinggi }}px" title="{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulanTren) }}: {{ $nilaiTren ?? 'tidak ada data' }}"></div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('asrama.analitik.rapor', ['kamar_id' => $k->id, 'bulan' => $bulanTerpilih]) }}" class="inline-flex items-center gap-1 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">🧾 Rapor</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kartu di HP --}}
                <div class="md:hidden space-y-3 mb-5">
                    @foreach($perKamar as $row)
                        @php
                            $k = $row['kamar'];
                            $warnaNilai = $row['persen'] === null ? 'text-slate-400' : ($row['persen'] >= 70 ? 'text-emerald-600' : 'text-rose-600');
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">{{ $k->nama_kamar }}</div>
                                    <div class="text-[11px] text-slate-400 font-semibold uppercase">{{ $k->kategori }} · {{ $k->musyrif->name ?? 'tanpa musyrif' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-extrabold {{ $warnaNilai }}">{{ $row['rata'] !== null ? $row['rata'] : '-' }}/25</div>
                                    @if($row['persen'] !== null)<div class="text-[11px] font-bold {{ $warnaNilai }}">{{ $row['persen'] }}%</div>@endif
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-2.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-slate-50 text-slate-600 border border-slate-200">{{ $row['sidak'] }}x sidak</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">👑 {{ $row['terbersih'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $row['terkotor'] > 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}">⚠️ {{ $row['terkotor'] }}</span>
                            </div>
                            @if($row['terlemah'])
                                <div class="text-[12px] text-slate-500 mt-2">Kriteria terlemah: <span class="font-semibold text-slate-700">{{ $row['terlemah'] }}</span></div>
                            @endif
                            <div class="flex items-end gap-1 mt-3">
                                @foreach($row['tren'] as $bulanTren => $nilaiTren)
                                    @php
                                        $tinggi = $nilaiTren !== null ? max(4, (int) round($nilaiTren / 25 * 34)) : 3;
                                        $warnaTren = $nilaiTren === null ? 'bg-slate-200' : ($nilaiTren >= 17.5 ? 'bg-emerald-500' : 'bg-rose-400');
                                    @endphp
                                    <div class="w-3.5 rounded-t {{ $warnaTren }}" style="height: {{ $tinggi }}px"></div>
                                @endforeach
                            </div>
                            <a href="{{ route('asrama.analitik.rapor', ['kamar_id' => $k->id, 'bulan' => $bulanTerpilih]) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 text-[13px] font-semibold mt-3">🧾 Rapor Kamar</a>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- TEMUAN DATA (laporan saja) --}}
            @php
                $jumlahTemuan = $temuan['gender']->count() + $temuan['ganda']->count() + $temuan['over']->count()
                    + $temuan['tidak_aktif']->count() + count($temuan['tanpa_musyrif']) + $temuan['tanpa_kamar'] + $temuan['tanpa_gender'];
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h4 class="text-[15px] font-bold text-slate-800">🔍 Pemeriksaan Data Kamar</h4>
                        <p class="text-[12px] text-slate-500 mt-0.5">Hanya laporan — sistem tidak mengubah data siswa/kamar secara otomatis. Perbaikan data dilakukan admin/TU lewat menu terkait.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-bold {{ $jumlahTemuan > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ $jumlahTemuan > 0 ? $jumlahTemuan . ' hal perlu ditindak' : 'Tidak ada temuan' }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100">
                    <div class="px-5 py-4">
                        <div class="text-[13px] font-bold text-slate-700 mb-1.5">
                            Jenis kelamin tidak sesuai kamar @if($temuan['gender']->isEmpty()) <span class="text-emerald-600 font-semibold">— tidak ada</span> @endif
                        </div>
                        @if($temuan['gender']->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($temuan['gender'] as $g)
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $g->nama_lengkap }} ({{ $g->jk ?: 'jk kosong' }}) · kamar {{ $g->nama_kamar }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-5 py-4">
                        <div class="text-[13px] font-bold text-slate-700 mb-1.5">
                            Siswa tercatat di lebih dari satu kamar @if($temuan['ganda']->isEmpty()) <span class="text-emerald-600 font-semibold">— tidak ada</span> @endif
                        </div>
                        @if($temuan['ganda']->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($temuan['ganda'] as $g)
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">{{ $g->nama_lengkap }} · {{ $g->jumlah }} kamar</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-5 py-4">
                        <div class="text-[13px] font-bold text-slate-700 mb-1.5">
                            Kamar melebihi kapasitas @if($temuan['over']->isEmpty()) <span class="text-emerald-600 font-semibold">— tidak ada</span> @endif
                        </div>
                        @if($temuan['over']->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($temuan['over'] as $k)
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">{{ $k->nama_kamar }} · {{ (int) $k->penghuni_count }}/{{ $k->kapasitas }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-5 py-4">
                        <div class="text-[13px] font-bold text-slate-700 mb-1.5">
                            Penghuni yang datanya sudah tidak aktif / terhapus @if($temuan['tidak_aktif']->isEmpty()) <span class="text-emerald-600 font-semibold">— tidak ada</span> @endif
                        </div>
                        @if($temuan['tidak_aktif']->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($temuan['tidak_aktif'] as $t)
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[12px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">{{ $t->nama_lengkap }} ({{ $t->status }}) · kamar {{ $t->nama_kamar }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <div class="text-[13px] font-bold text-slate-700">Kamar tanpa musyrif</div>
                            <div class="text-[13px] text-slate-600 mt-0.5">{{ empty($temuan['tanpa_musyrif']) ? 'Tidak ada' : implode(', ', $temuan['tanpa_musyrif']) }}</div>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-700">Kamar tanpa penghuni</div>
                            <div class="text-[13px] text-slate-600 mt-0.5">{{ empty($temuan['kosong']) ? 'Tidak ada' : implode(', ', $temuan['kosong']) }}</div>
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-700">Siswa aktif belum dapat kamar</div>
                            <div class="text-[13px] text-slate-600 mt-0.5">{{ $temuan['tanpa_kamar'] }} siswa · {{ $temuan['tanpa_gender'] }} siswa belum ada jenis kelamin</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
