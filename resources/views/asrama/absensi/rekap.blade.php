<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5 no-print">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📊 Rekap Absensi Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Ringkasan kehadiran per kamar dan siswa yang sering tidak hadir</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('asrama.absensi.index') }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📋 Isi Absensi</a>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[13px] font-semibold shadow-sm transition whitespace-nowrap">🖨️ Cetak</button>
                </div>
            </div>

            <form method="GET" action="{{ route('asrama.absensi.rekap') }}" class="no-print bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bulan</label>
                        <input type="month" name="bulan" value="{{ $bulan }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
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

            @if($baris->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum ada data absensi</h3>
                    <p class="text-sm text-slate-400">Mulai isi absensi lewat halaman Absensi Asrama.</p>
                </div>
            @else
                <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kamar</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Hadir</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Telat</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Izin</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Sakit</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Pulang</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Alpa</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">% Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($baris as $b)
                                    @php $persenHadir = (int) $b->catatan > 0 ? round((int) $b->hadir / (int) $b->catatan * 100) : 0; @endphp
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-bold text-slate-800">{{ $b->nama_kamar }}</div>
                                            <div class="text-[11px] text-slate-400 font-semibold uppercase">{{ $b->kategori }} · {{ $b->hari }} hari tercatat</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center font-bold text-emerald-600">{{ (int) $b->hadir }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $b->telat }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $b->izin }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $b->sakit }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $b->pulang }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-bold {{ (int) $b->alpa > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ (int) $b->alpa }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-bold {{ $persenHadir >= 90 ? 'text-emerald-600' : ($persenHadir >= 75 ? 'text-amber-600' : 'text-rose-600') }}">{{ $persenHadir }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kartu di HP --}}
                <div class="md:hidden space-y-3 mb-6">
                    @foreach($baris as $b)
                        @php $persenHadir = (int) $b->catatan > 0 ? round((int) $b->hadir / (int) $b->catatan * 100) : 0; @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-extrabold text-slate-900">{{ $b->nama_kamar }}</div>
                                <span class="text-[12px] font-bold {{ $persenHadir >= 90 ? 'text-emerald-600' : ($persenHadir >= 75 ? 'text-amber-600' : 'text-rose-600') }}">{{ $persenHadir }}% hadir</span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-semibold uppercase mt-0.5">{{ $b->kategori }} · {{ $b->hari }} hari tercatat</div>
                            <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                                <div class="bg-slate-50 rounded-lg py-2"><div class="text-[11px] text-slate-400 font-bold uppercase">Hadir</div><div class="text-sm font-bold text-emerald-600">{{ (int) $b->hadir }}</div></div>
                                <div class="bg-slate-50 rounded-lg py-2"><div class="text-[11px] text-slate-400 font-bold uppercase">Izin/Sakit</div><div class="text-sm font-bold text-slate-600">{{ (int) $b->izin + (int) $b->sakit }}</div></div>
                                <div class="bg-slate-50 rounded-lg py-2"><div class="text-[11px] text-slate-400 font-bold uppercase">Alpa</div><div class="text-sm font-bold text-rose-600">{{ (int) $b->alpa }}</div></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($siswaBermasalah->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h4 class="text-[15px] font-bold text-slate-800">⚠️ Perlu Perhatian (alpa / telat terbanyak)</h4>
                            <p class="text-[12px] text-slate-500 mt-0.5">Bulan {{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulan) }}</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[620px]">
                                <thead>
                                    <tr class="bg-slate-50/80 border-b border-slate-200">
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Siswa</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kamar</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Alpa</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Telat</th>
                                        <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswaBermasalah as $s)
                                        <tr class="border-b border-slate-100">
                                            <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $s->nama_lengkap }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $s->kelas ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $s->nama_kamar ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-center font-bold {{ (int) $s->alpa > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ (int) $s->alpa }}</td>
                                            <td class="px-4 py-3 text-sm text-center font-semibold text-slate-600">{{ (int) $s->telat }}</td>
                                            <td class="px-4 py-3 text-center no-print">
                                                <a href="{{ route('asrama.izin.create', ['student_id' => $s->student_id]) }}" class="inline-flex items-center gap-1 rounded-lg bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">🚪 Catat izin</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <style>
        @media print {
            .no-print, aside, nav, header, footer { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</x-app-layout>
