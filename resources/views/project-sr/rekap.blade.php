<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📈 Rekap Project Student Root</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Nilai akhir = rata-rata project yang dilaksanakan · predikat A ≥ {{ $ambang['a'] }}, B ≥ {{ $ambang['b'] }}, C ≥ {{ $ambang['c'] }}, D &lt; {{ $ambang['c'] }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($grupTerpilih)
                        <a href="{{ route('project-sr.ekspor', ['grup_id' => $grupTerpilih->id, 'q' => $filter['q']]) }}"
                           class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">⬇️ Ekspor CSV</a>
                    @endif
                    <a href="{{ route('project-sr.index') }}"
                       class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">📁 Daftar Project</a>
                </div>
            </div>

            {{-- ===== FILTER ===== --}}
            <form action="{{ route('project-sr.rekap') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3 grid grid-cols-2 sm:grid-cols-12 gap-2">
                @if($bolehSemua)
                    <div class="col-span-2 sm:col-span-5">
                        <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Grup binaan</label>
                        <select name="grup_id" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            @forelse($daftarGrup as $g)
                                <option value="{{ $g->id }}" @selected($grupTerpilih && $grupTerpilih->id === $g->id)>{{ $g->nama_grup }}@if($g->mentor) · {{ $g->mentor->name }}@endif</option>
                            @empty
                                <option value="">Belum ada grup</option>
                            @endforelse
                        </select>
                    </div>
                @endif
                <div class="col-span-2 sm:col-span-4">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari nama / NISN</label>
                    <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Ketik untuk mencari"
                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                </div>
                <div class="col-span-2 sm:col-span-2 flex items-end">
                    <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>
                </div>
            </form>

            @if(! $grupTerpilih)
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                    Belum ada grup binaan yang bisa ditampilkan.
                </div>
            @else
                {{-- ===== RINGKASAN ===== --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Grup</div>
                        <div class="text-[15px] font-extrabold text-slate-900 mt-0.5 truncate">{{ $grupTerpilih->nama_grup }}</div>
                        <div class="text-[11px] text-slate-400 truncate">mentor {{ $grupTerpilih->mentor->name ?? '-' }}</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Jumlah project</div>
                        <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ $projects->count() }}</div>
                        <div class="text-[11px] text-slate-400">bebas sesuai kebutuhan grup</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Anggota grup</div>
                        <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ $baris->count() }} siswa</div>
                        <div class="text-[11px] text-slate-400">{{ $baris->filter(fn ($r) => $r['rata'] !== null)->count() }} sudah punya nilai akhir</div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sebaran predikat</div>
                        <div class="flex flex-wrap gap-1">
                            @foreach(['A', 'B', 'C', 'D'] as $p)
                                <span class="inline-flex items-center gap-1 rounded-lg border px-1.5 py-0.5 text-[11px] font-bold {{ \App\Models\PenilaianPengaturan::warnaPredikat($p) }}">
                                    {{ $p }} <span class="font-semibold opacity-70">{{ $baris->where('predikat', $p)->count() }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ===== RATA PER TAHAP (kekuatan grup) ===== --}}
                @if($projects->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3">
                        <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Rata-rata nilai per tahap (seluruh project)</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($tahap as $t)
                                @php
                                    $kumpulan = $baris->pluck("per_tahap.{$t->id}")->filter(fn ($v) => $v !== null);
                                    $rataTahap = $kumpulan->count() ? round($kumpulan->sum() / $kumpulan->count(), 2) : null;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600">
                                    {{ $t->nama }} <span class="text-slate-400">({{ $t->bobot }}%)</span>
                                    <span class="font-bold text-slate-800">{{ $rataTahap !== null ? $rataTahap . '%' : '—' }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($projects->isEmpty())
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                        Grup ini belum punya project. Tambahkan project dulu di halaman Daftar Project.
                    </div>
                @elseif($baris->isEmpty())
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                        Tidak ada anggota yang cocok dengan pencarian.
                    </div>
                @else
                    {{-- ===== TABEL (desktop) ===== --}}
                    <div class="hidden sm:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50/80 text-[10.5px] font-black uppercase tracking-wider text-slate-400">
                                <tr>
                                    <th class="px-4 py-2.5">Siswa</th>
                                    <th class="px-3 py-2.5">Kelas</th>
                                    @foreach($projects as $p)
                                        <th class="px-2 py-2.5 text-center" title="{{ $p->nama }}">{{ Str::limit($p->nama, 14) }}</th>
                                    @endforeach
                                    <th class="px-3 py-2.5 text-center">Nilai Akhir</th>
                                    <th class="px-3 py-2.5 text-center">Project</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($baris as $r)
                                    <tr class="border-t border-slate-100 hover:bg-slate-50/60 transition">
                                        <td class="px-4 py-2.5">
                                            <div class="text-[13px] font-semibold text-slate-800">{{ $r['nama'] }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $r['nisn'] ?: '-' }}</div>
                                        </td>
                                        <td class="px-3 py-2.5 text-[12.5px] text-slate-600">{{ $r['kelas'] ?: '-' }}</td>
                                        @foreach($projects as $p)
                                            @php $n = $r['per_project'][$p->id] ?? null; @endphp
                                            <td class="px-2 py-2.5 text-center">
                                                @if($n && $n['rata'] !== null)
                                                    <div class="inline-flex items-center gap-1" @if(! ($n['lengkap'] ?? false)) title="Belum lengkap: {{ $n['terisi'] }} dari {{ count($tahap) }} tahap" @endif>
                                                        <span class="text-[12.5px] font-bold {{ ($n['lengkap'] ?? false) ? 'text-slate-700' : 'text-amber-600' }}">{{ $n['rata'] }}@unless($n['lengkap'] ?? false)*@endunless</span>
                                                        <span class="inline-flex items-center justify-center h-5 w-5 rounded-md border text-[10px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($n['predikat']) }}">{{ $n['predikat'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-[13px] text-slate-300">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-2.5 text-center">
                                            @if($r['rata'] !== null)
                                                <div class="inline-flex items-center gap-1.5">
                                                    <span class="text-[13.5px] font-extrabold text-slate-900">{{ $r['rata'] }}%</span>
                                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($r['predikat']) }}">{{ $r['predikat'] }}</span>
                                                </div>
                                            @elseif(($r['project_sebagian'] ?? 0) > 0)
                                                <span class="text-[11.5px] font-semibold text-amber-600">belum lengkap</span>
                                            @else
                                                <span class="text-[13px] text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center text-[12.5px] text-slate-600">
                                            {{ $r['project_dilaksanakan'] }}/{{ $r['jumlah_project'] }}
                                            @if(($r['project_sebagian'] ?? 0) > 0)
                                                <div class="text-[10.5px] text-amber-600">+{{ $r['project_sebagian'] }} sebagian</div>
                                            @endif
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
                                        <div class="text-[11px] text-slate-400">{{ $r['kelas'] ?: '-' }} · {{ $r['nisn'] ?: '-' }}</div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        @if($r['rata'] !== null)
                                            <div class="flex items-center gap-1.5 justify-end">
                                                <span class="text-[15px] font-extrabold text-slate-900">{{ $r['rata'] }}%</span>
                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($r['predikat']) }}">{{ $r['predikat'] }}</span>
                                            </div>
                                        @else
                                            <span class="text-[12px] text-slate-400">Belum dinilai</span>
                                        @endif
                                        <div class="text-[10.5px] text-slate-400">{{ $r['project_dilaksanakan'] }}/{{ $r['jumlah_project'] }} project</div>
                                    </div>
                                </div>

                                <div class="mt-2 space-y-1">
                                    @foreach($projects as $p)
                                        @php $n = $r['per_project'][$p->id] ?? null; @endphp
                                        <div class="flex items-center justify-between gap-2 text-[11.5px]">
                                            <span class="text-slate-500 truncate">📁 {{ $p->nama }}</span>
                                            @if($n && $n['rata'] !== null)
                                                <span class="shrink-0 font-bold {{ ($n['lengkap'] ?? false) ? 'text-slate-700' : 'text-amber-600' }}">{{ $n['rata'] }}%@unless($n['lengkap'] ?? false) (belum lengkap)@endunless</span>
                                            @else
                                                <span class="shrink-0 text-slate-300">—</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
