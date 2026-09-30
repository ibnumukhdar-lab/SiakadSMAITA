<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">

            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400">Kulliyyat Diiniyyah Al-Arafah</p>
                <h1 class="text-xl font-extrabold text-slate-800">Periode Penilaian Diniyah</h1>
                <p class="text-xs font-semibold text-slate-500">Hanya satu periode boleh terbuka supaya angka rapor tidak bercampur. Dibuka/ditutup Kepala Diniyah.</p>
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
                <h2 class="text-sm font-extrabold text-slate-800 mb-3">Tambah periode</h2>
                <form method="POST" action="{{ route('diniyah.periode.simpan') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @csrf
                    <label class="block md:col-span-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama periode</span>
                        <input name="nama" required value="{{ old('nama', $usulanNama) }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun ajaran</span>
                        <input name="tahun_ajaran" value="{{ old('tahun_ajaran', $usulanTahun) }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Semester</span>
                        <select name="semester" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                            @foreach($semesterList as $kode => $label)
                                <option value="{{ $kode }}" @selected(\App\Models\DiniyahPeriode::semesterKalender() === $kode)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tahun hijriah (kop rapor)</span>
                        <input name="tahun_hijriah" value="{{ old('tahun_hijriah', $usulanHijriah) }}" class="mt-1 w-full h-10 rounded-lg border border-slate-300 px-3 text-sm font-semibold">
                    </label>
                    <div class="md:col-span-2">
                        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs font-extrabold">Simpan periode</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 kertas p-5">
                <h2 class="text-sm font-extrabold text-slate-800 mb-3">Daftar periode</h2>
                <div class="space-y-2">
                    @forelse($periode as $p)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border {{ $p->terbuka ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200' }} p-3">
                            <div>
                                <p class="text-sm font-extrabold text-slate-800">{{ $p->label() }}</p>
                                <p class="text-[11px] font-semibold text-slate-500">
                                    {{ $semesterList[$p->semester] ?? $p->semester }}
                                    @if($p->tahun_hijriah) · {{ $p->tahun_hijriah }} @endif
                                    @if($p->dibuka_pada) · dibuka {{ $p->dibuka_pada->format('d/m/Y H:i') }} @endif
                                    @if($p->ditutup_pada) · ditutup {{ $p->ditutup_pada->format('d/m/Y H:i') }} @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded border text-[11px] font-extrabold {{ $p->terbuka ? 'bg-emerald-100 border-emerald-300 text-emerald-900' : 'bg-slate-100 border-slate-200 text-slate-600' }}">{{ $p->terbuka ? 'TERBUKA' : 'TERTUTUP' }}</span>
                                <form method="POST" action="{{ $p->terbuka ? route('diniyah.periode.tutup', $p->id) : route('diniyah.periode.buka', $p->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-3 py-1.5 rounded-lg {{ $p->terbuka ? 'bg-slate-800 text-white' : 'bg-emerald-700 text-white' }} text-[11px] font-extrabold">{{ $p->terbuka ? 'Tutup' : 'Buka' }}</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs font-semibold text-slate-500">Belum ada periode.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
