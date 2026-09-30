<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-5">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Kulliyyat Diiniyyah Al-Arafah</p>
                    <h1 class="text-xl font-extrabold text-slate-800">Absensi Kajian Diniyah</h1>
                    <p class="text-xs font-semibold text-slate-500">Dicatat per pertemuan (tanggal + mapel + jam ke). Rekapnya masuk rapor otomatis.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('diniyah.nilai') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Input Nilai</a>
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
                <form method="GET" action="{{ route('diniyah.absensi') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pertemuan</span>
                        <select name="pertemuan" onchange="this.form.submit()" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            @forelse($pertemuanList as $pt)
                                <option value="{{ $pt->id }}" @selected($pertemuan && $pertemuan->id === $pt->id)>
                                    {{ $pt->tanggal->format('d/m/Y') }}@if($pt->jam_ke) · jam ke-{{ $pt->jam_ke }}@endif @if($pt->materi) · {{ \Illuminate\Support\Str::limit($pt->materi, 28) }} @endif
                                </option>
                            @empty
                                <option value="">— belum ada pertemuan —</option>
                            @endforelse
                        </select>
                    </label>
                </form>
                @if($periode)
                    <p class="text-[11px] font-semibold text-slate-500 mt-2">Periode aktif: {{ $periode->label() }}@if(! $periode->terbuka) <span class="font-bold text-amber-700">(tertutup — absensi masih tersimpan sebagai draf)</span>@endif</p>
                @endif
            </div>

            @if($terpilih)
                <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                    <h2 class="text-sm font-extrabold text-slate-800 mb-3">Catat pertemuan baru</h2>
                    <form method="POST" action="{{ route('diniyah.absensi.pertemuan') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        @csrf
                        <input type="hidden" name="mapel_id" value="{{ $terpilih->id }}">
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal</span>
                            <input type="date" name="tanggal" required value="{{ now()->toDateString() }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                        </label>
                        <label class="block">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jam ke</span>
                            <input type="number" name="jam_ke" min="1" max="20" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Materi / topik kajian</span>
                            <input name="materi" placeholder="mis. Bab Isim &amp; Fi'il — latihan i'rab" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                        </label>
                        <div class="sm:col-span-4">
                            <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Simpan pertemuan &amp; isi absensi</button>
                        </div>
                    </form>
                </div>
            @endif

            @if($pertemuan)
                <form method="POST" action="{{ route('diniyah.absensi.simpan') }}" class="bg-white rounded-xl border border-slate-200 kertas p-5">
                    @csrf
                    <input type="hidden" name="pertemuan_id" value="{{ $pertemuan->id }}">

                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-800">{{ $terpilih->nama }} — {{ $pertemuan->tanggal->format('d/m/Y') }}@if($pertemuan->jam_ke) (jam ke-{{ $pertemuan->jam_ke }})@endif</p>
                            <p class="text-[11px] font-semibold text-slate-500">{{ $pertemuan->materi ?: 'belum ada materi' }} · {{ $sudahTercatat }} dari {{ count($daftar) }} sudah pernah tercatat</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.querySelectorAll('select[name^=status]').forEach(s => s.value = 'hadir')"
                                    class="px-3 py-2 rounded-lg bg-emerald-50 border border-emerald-300 text-[11px] font-extrabold text-emerald-800">Semua hadir</button>
                            <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Simpan absensi</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                                    <th class="py-2 w-10">No</th>
                                    <th class="py-2">Nama santri</th>
                                    <th class="py-2 w-16">Kelas</th>
                                    <th class="py-2 w-40 text-center">Status kehadiran</th>
                                    <th class="py-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-semibold">
                                @forelse($daftar as $i => $baris)
                                    <tr>
                                        <td class="py-2">{{ $i + 1 }}</td>
                                        <td class="py-2">{{ $baris->siswa->nama_lengkap }}</td>
                                        <td class="py-2">{{ $baris->siswa->kelas }}</td>
                                        <td class="py-2">
                                            <select name="status[{{ $baris->siswa->id }}]" class="w-full h-9 rounded-lg border border-slate-300 px-2 text-xs font-bold">
                                                @foreach($statusList as $kode => $label)
                                                    <option value="{{ $kode }}" @selected($baris->status === $kode)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-2">
                                            <input name="keterangan[{{ $baris->siswa->id }}]" value="{{ $baris->keterangan }}" placeholder="—" class="w-full h-9 rounded-lg border border-slate-200 px-2 text-xs">
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-3 text-xs font-semibold text-slate-500">Tidak ada santri untuk mapel ini pada wewenang Anda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($rekap)
                        <p class="text-[11px] font-bold text-slate-600 mt-3">
                            Tercatat pada pertemuan ini: {{ $rekap['hadir'] }} hadir · {{ $rekap['sakit'] }} sakit · {{ $rekap['izin'] }} izin · {{ $rekap['alpa'] }} alpa
                        </p>
                    @endif
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
