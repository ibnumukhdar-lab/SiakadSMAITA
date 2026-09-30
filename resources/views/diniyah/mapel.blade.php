<x-app-layout>
    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-5">

            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Kulliyyat Diiniyyah Al-Arafah</p>
                    <h1 class="text-xl font-extrabold text-slate-800">Mata Pelajaran Kajian Diniyah</h1>
                    <p class="text-xs font-semibold text-slate-500">Ditetapkan Kepala Diniyah. Daftar santri ditarik otomatis dari kelas di Data Induk Siswa.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('diniyah.nilai') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Input Nilai</a>
                    <a href="{{ route('diniyah.absensi') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Absensi</a>
                    <a href="{{ route('diniyah.periode') }}" class="px-4 py-2 rounded-lg bg-slate-100 border border-slate-300 text-xs font-extrabold text-slate-700">Periode</a>
                    <a href="{{ route('diniyah.rapor') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Rapor</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-xs font-bold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-xs font-bold text-rose-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-lg bg-rose-50 border border-rose-200 p-3">
                    @foreach($errors->all() as $e)
                        <p class="text-xs font-bold text-rose-800">{{ $e }}</p>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <p class="text-xs font-extrabold text-slate-700">Periode aktif: {{ $periode ? $periode->label() . ($periode->terbuka ? ' (terbuka)' : ' (tertutup)') : 'belum ada — buat dulu di menu Periode' }}</p>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <h2 class="text-sm font-extrabold text-slate-800 mb-3">Tambah mata pelajaran</h2>
                <form method="POST" action="{{ route('diniyah.mapel.simpan') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @csrf
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama mata pelajaran</span>
                        <input name="nama" required value="{{ old('nama') }}" placeholder="mis. Bahasa Arab — Nahwu" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Arab (untuk rapor)</span>
                        <input name="nama_arab" value="{{ old('nama_arab') }}" dir="rtl" placeholder="النحو" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Berlaku untuk kelas</span>
                        <div class="mt-1 flex flex-wrap gap-3">
                            @foreach($daftarKelas as $namaKelas => $jumlah)
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 bg-white text-xs font-bold text-slate-700">
                                    <input type="checkbox" name="kelas[]" value="{{ $namaKelas }}" class="rounded border-slate-300">
                                    {{ $namaKelas }} <span class="text-slate-400">({{ $jumlah }} santri)</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Guru pengampu</span>
                        <select name="guru_id" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            <option value="">— pilih dari daftar guru —</option>
                            @foreach($daftarGuru as $g)
                                <option value="{{ $g->id }}" @selected(old('guru_id') == $g->id)>{{ $g->name }}@if($g->nipa) · {{ $g->nipa }}@endif</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2 mt-2">
                            <input name="guru_nama" value="{{ old('guru_nama') }}" placeholder="atau ketik nama manual" class="flex-1 h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            <input name="guru_nipa" value="{{ old('guru_nipa') }}" placeholder="NIPA (opsional)" class="w-40 h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Kalau memilih dari daftar, nama &amp; NIPA terisi otomatis. Kolom manual dipakai bila pengampunya bukan pengguna SIAKAD.</p>
                    </div>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Jam pelajaran / pekan</span>
                        <input name="jp_pekan" type="number" min="1" max="20" value="{{ old('jp_pekan') }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Urutan di rapor</span>
                        <input name="urutan" type="number" min="0" max="999" value="{{ old('urutan', 0) }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <div class="md:col-span-2">
                        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Simpan mata pelajaran</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <h2 class="text-sm font-extrabold text-slate-800 mb-3">Daftar mata pelajaran</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                                <th class="py-2">Mata pelajaran</th>
                                <th class="py-2">Kelas</th>
                                <th class="py-2">Guru pengampu</th>
                                <th class="py-2 text-center">JP</th>
                                <th class="py-2 text-center">Santri</th>
                                <th class="py-2 text-center">Nilai terisi</th>
                                <th class="py-2 text-center">Status</th>
                                <th class="py-2 text-right">Ubah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-semibold">
                            @forelse($ringkas as $r)
                                <tr>
                                    <td class="py-2.5">
                                        {{ $r->mapel->nama }}
                                        @if($r->mapel->nama_arab)
                                            <span class="text-slate-500" style="font-family: 'Amiri', serif;">{{ $r->mapel->nama_arab }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5">{{ implode(', ', $r->kelas) ?: '—' }}</td>
                                    <td class="py-2.5">{{ $r->mapel->guruTampil() }}</td>
                                    <td class="py-2.5 text-center">{{ $r->mapel->jp_pekan ?? '—' }}</td>
                                    <td class="py-2.5 text-center">{{ $r->santri }}</td>
                                    <td class="py-2.5 text-center">
                                        <span class="{{ $r->terisi >= $r->santri && $r->santri > 0 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $r->terisi }}/{{ $r->santri }}</span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded border text-[11px] font-extrabold {{ $r->mapel->status === 'aktif' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-100 border-slate-200 text-slate-600' }}">{{ $r->mapel->status }}</span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <form method="POST" action="{{ route('diniyah.mapel.status', $r->mapel->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="text-[11px] font-extrabold text-slate-500">{{ $r->mapel->status === 'aktif' ? 'nonaktifkan' : 'aktifkan' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-3 text-xs font-semibold text-slate-500">Belum ada mata pelajaran. Tambahkan lewat form di atas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-[11px] text-slate-500 font-semibold mt-3">Mata pelajaran tidak dihapus, hanya dinonaktifkan — supaya nilai yang sudah diisi tidak hilang dari rapór semester lalu.</p>
            </div>
        </div>
    </div>
</x-app-layout>
