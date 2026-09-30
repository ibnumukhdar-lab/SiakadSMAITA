<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Kulliyyat Diiniyyah Al-Arafah</p>
                    <h1 class="text-xl font-extrabold text-slate-800">Rapor Diniyah (Berbahasa Arab)</h1>
                    <p class="text-xs font-semibold text-slate-500">Satu rapor per santri per semester: nilai tiap mapel + rekap kehadiran dari absensi pertemuan.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('diniyah.nilai') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Input Nilai</a>
                    <a href="{{ route('diniyah.mapel') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Mata Pelajaran</a>
                </div>
            </div>

            @if(session('error'))
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs font-bold text-rose-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <form method="GET" action="{{ route('diniyah.rapor') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Periode</span>
                        <select name="periode" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            @foreach($periodeList as $p)
                                <option value="{{ $p->id }}" @selected($periode && $periode->id === $p->id)>{{ $p->label() }}{{ $p->terbuka ? ' (terbuka)' : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas</span>
                        <select name="kelas" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            <option value="">semua kelas</option>
                            @foreach(['X', 'XI', 'XII', 'Lulus'] as $k)
                                <option value="{{ $k }}" @selected($kelas === $k)>{{ $k }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Cari nama / NISN</span>
                        <input name="q" value="{{ $q }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <div class="flex items-end">
                        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Tampilkan</button>
                    </div>
                </form>
                <p class="text-[11px] font-semibold text-slate-500 mt-2">
                    {{ $santri->count() }} santri · {{ $jumlahMapel }} mata pelajaran aktif
                    @if($tanpaArab > 0)
                        · <span class="font-bold text-amber-700">{{ $tanpaArab }} santri belum punya transliterasi nama Arab (nama Arab bisa diisi di Data Induk Siswa)</span>
                    @endif
                </p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                                <th class="py-2 w-10">No</th>
                                <th class="py-2">Nama santri</th>
                                <th class="py-2">Nama Arab</th>
                                <th class="py-2 w-16">Kelas</th>
                                <th class="py-2 w-32">NISN</th>
                                <th class="py-2 text-right">Rapor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold">
                            @forelse($santri as $i => $s)
                                <tr>
                                    <td class="py-2">{{ $i + 1 }}</td>
                                    <td class="py-2">{{ $s->nama_lengkap }}</td>
                                    <td class="py-2" style="font-family: 'Amiri', serif; direction: rtl;">{{ $s->nama_arab ?: '—' }}</td>
                                    <td class="py-2">{{ $s->kelas }}</td>
                                    <td class="py-2">{{ $s->nisn }}</td>
                                    <td class="py-2 text-right">
                                        <a href="{{ route('diniyah.rapor.cetak', ['siswa' => $s->id, 'periode' => $periode?->id]) }}" target="_blank"
                                           class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-[11px] font-extrabold">Buka rapor</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-3 text-xs font-semibold text-slate-500">Tidak ada santri pada wewenang Anda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
