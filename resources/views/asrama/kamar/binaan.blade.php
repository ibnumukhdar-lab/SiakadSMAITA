<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🏠 Kamar Binaan Saya</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Assalamu'alaikum, {{ Auth::user()->name }} — daftar kamar asrama di bawah tanggung jawab Anda</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('asrama.absensi.index') }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📋 Absensi</a>
                    <a href="{{ route('asrama.analitik') }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📈 Analitik</a>
                </div>
            </div>

            {{-- Tabel di layar lebar --}}
            <div class="hidden md:block bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Kamar</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Keterisian</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kamars as $kamar)
                                @php
                                    $p = (int) $kamar->penghuni_count;
                                    $kap = (int) $kamar->kapasitas;
                                    $persenIsi = $kap > 0 ? min(100, (int) round($p / $kap * 100)) : 0;
                                    $warnaBar = ($kap > 0 && $p > $kap) ? 'bg-rose-500' : ($p === 0 ? 'bg-amber-400' : ($p === $kap ? 'bg-emerald-500' : 'bg-blue-600'));
                                @endphp
                                <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 text-sm font-bold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($kamar->kategori == 'putra')
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-28">
                                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-1.5 rounded-full {{ $warnaBar }}" style="width: {{ $persenIsi }}%"></div>
                                                </div>
                                            </div>
                                            <span class="text-sm font-bold {{ $p > $kap ? 'text-rose-600' : 'text-slate-800' }}">{{ $p }}<span class="text-slate-400 font-semibold">/{{ $kap }}</span></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-1.5">
                                            <a href="{{ route('asrama.absensi.form', [$kamar->id, 'tanggal' => now()->toDateString()]) }}" title="Isi absensi kamar ini" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">📋</a>
                                            <a href="{{ route('asrama.kamar.show', $kamar->id) }}" title="Lihat penghuni" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👥</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-slate-500 font-medium">
                                        <span class="text-4xl block mb-3">🏠</span>
                                        Anda belum ditugaskan sebagai Musyrif di kamar manapun.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Kartu di HP --}}
            <div class="md:hidden space-y-3">
                @forelse($kamars as $kamar)
                    @php
                        $p = (int) $kamar->penghuni_count;
                        $kap = (int) $kamar->kapasitas;
                        $persenIsi = $kap > 0 ? min(100, (int) round($p / $kap * 100)) : 0;
                        $warnaBar = ($kap > 0 && $p > $kap) ? 'bg-rose-500' : ($p === 0 ? 'bg-amber-400' : ($p === $kap ? 'bg-emerald-500' : 'bg-blue-600'));
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-sm font-extrabold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</div>
                            @if($kamar->kategori == 'putra')
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-3">
                            <div class="flex-1">
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $warnaBar }}" style="width: {{ $persenIsi }}%"></div>
                                </div>
                            </div>
                            <span class="text-[13px] font-bold text-slate-800">{{ $p }}/{{ $kap }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <a href="{{ route('asrama.absensi.form', [$kamar->id, 'tanggal' => now()->toDateString()]) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-blue-900 text-white text-[13px] font-semibold">📋 Absensi</a>
                            <a href="{{ route('asrama.kamar.show', $kamar->id) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 text-[13px] font-semibold">👥 Penghuni</a>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-10 text-center text-slate-500 text-sm">Anda belum ditugaskan sebagai Musyrif di kamar manapun.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
