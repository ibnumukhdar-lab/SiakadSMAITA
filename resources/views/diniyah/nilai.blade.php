<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Kulliyyat Diiniyyah Al-Arafah</p>
                    <h1 class="text-xl font-extrabold text-slate-800">Input Nilai Kajian Diniyah</h1>
                    <p class="text-xs font-semibold text-slate-500">
                        @if($bolehSemua)
                            Anda dapat menilai seluruh kelas (pengelola/guru pengampu).
                        @else
                            Anda menilai santri kamar binaan Anda saja.
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('diniyah.absensi') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Absensi</a>
                    <a href="{{ route('diniyah.rapor') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Rapor</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-3">
                    @foreach($errors->all() as $e)
                        <p class="text-xs font-bold text-rose-800">{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <form method="GET" action="{{ route('diniyah.nilai') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Periode</span>
                        <select name="periode" onchange="this.form.submit()" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            @foreach($periodeList as $p)
                                <option value="{{ $p->id }}" @selected($periode && $periode->id === $p->id)>{{ $p->label() }}{{ $p->terbuka ? ' (terbuka)' : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mata pelajaran</span>
                        <select name="mapel" onchange="this.form.submit()" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            @forelse($mapelList as $m)
                                <option value="{{ $m->id }}" @selected($terpilih && $terpilih->id === $m->id)>{{ $m->nama }} ({{ implode(', ', $m->daftarKelas()) }})</option>
                            @empty
                                <option value="">— belum ada mapel untuk Anda —</option>
                            @endforelse
                        </select>
                    </label>
                </form>
                @if($periode && ! $periode->terbuka)
                    <p class="text-[11px] font-bold text-amber-700 mt-2">Periode ini masih tertutup — nilai tetap bisa disimpan sebagai draf, tetapi buka dulu di menu Periode bila penilaian sudah berjalan.</p>
                @endif
            </div>

            @if($terpilih)
                <form method="POST" action="{{ route('diniyah.nilai.simpan') }}" class="bg-white rounded-xl border border-slate-200 kertas p-5">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $periode?->id }}">
                    <input type="hidden" name="mapel_id" value="{{ $terpilih->id }}">

                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-800">{{ $terpilih->nama }}@if($terpilih->nama_arab) <span style="font-family: 'Amiri', serif;" class="text-slate-500">{{ $terpilih->nama_arab }}</span>@endif</p>
                            <p class="text-[11px] font-semibold text-slate-500">Guru pengampu: {{ $terpilih->guruTampil() }} · {{ count($daftar) }} santri</p>
                        </div>
                        <p class="text-[11px] font-semibold text-slate-500">Nilai 0–100 · satu nilai akhir per mapel</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                                    <th class="py-2 w-10">No</th>
                                    <th class="py-2">Nama santri</th>
                                    <th class="py-2 w-16">Kelas</th>
                                    <th class="py-2 w-28 text-center">Nilai</th>
                                    <th class="py-2 w-24 text-center">Predikat</th>
                                    <th class="py-2">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-semibold">
                                @forelse($daftar as $i => $baris)
                                    <tr>
                                        <td class="py-2">{{ $i + 1 }}</td>
                                        <td class="py-2">
                                            {{ $baris->siswa->nama_lengkap }}
                                            @if($baris->dikoreksi)
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-sky-700">· dikoreksi guru</span>
                                            @endif
                                        </td>
                                        <td class="py-2">{{ $baris->siswa->kelas }}</td>
                                        <td class="py-2">
                                            <input type="number" min="0" max="100" name="nilai[{{ $baris->siswa->id }}]" value="{{ $baris->nilai }}"
                                                   class="w-20 h-9 rounded-lg border border-slate-300 px-2 text-sm font-bold text-center">
                                        </td>
                                        <td class="py-2 text-center text-[11px] font-bold text-slate-500">
                                            {{ \App\Support\DiniyahArab::predikatArab($baris->nilai) ?: '—' }}
                                        </td>
                                        <td class="py-2">
                                            <input name="catatan[{{ $baris->siswa->id }}]" value="{{ $baris->catatan }}" placeholder="—"
                                                   class="w-full h-9 rounded-lg border border-slate-200 px-2 text-xs">
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-3 text-xs font-semibold text-slate-500">Tidak ada santri pada mapel ini untuk Anda (cek kamar binaan atau kelas mapel).</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(count($daftar) > 0)
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Simpan nilai</button>
                            <span class="text-[11px] font-semibold text-slate-500">Terisi {{ $daftar->whereNotNull('nilai')->count() }} dari {{ count($daftar) }}</span>
                        </div>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
