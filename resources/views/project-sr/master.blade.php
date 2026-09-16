<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">⚙️ Tahap &amp; Bobot Project</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Nama tahap, urutan, bobot penilaian, dan panduan indikator</p>
                </div>
                <a href="{{ route('project-sr.index') }}"
                   class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📁 Daftar Project</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @php $totalBobot = $tahap->where('aktif', true)->sum('bobot'); @endphp
            <div class="{{ abs($totalBobot - 100) < 0.01 ? 'bg-blue-50 border-blue-400 text-blue-900' : 'bg-amber-50 border-amber-400 text-amber-900' }} border-l-4 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Bobot tahap aktif sekarang <strong>{{ $totalBobot }}%</strong>
                @if(abs($totalBobot - 100) >= 0.01)
                    — sebaiknya dibuat pas 100% supaya perbandingan antar tahap adil. Bobot tetap dihitung secara proporsional, jadi nilai tidak rusak walau belum 100%.
                @endif
                Terdapat <strong>{{ $jumlahProject }}</strong> project dan <strong>{{ $jumlahNilai }}</strong> nilai tersimpan.
                Mengubah nama atau bobot tahap tidak menghapus nilai yang sudah ada.
            </div>

            {{-- ===== SIMPAN SEMUA BOBOT (cepat) ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                <h4 class="text-[14px] font-bold text-slate-800 mb-1">Atur bobot cepat</h4>
                <p class="text-[12.5px] text-slate-500 mb-3">Isi bobot tiap tahap (total ideal 100%), lalu simpan sekaligus.</p>
                <form action="{{ route('project-sr.master.bobot') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                    @csrf
                    @foreach($tahap as $t)
                        <div class="col-span-1 sm:col-span-6 lg:col-span-4">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ $t->urutan }}. {{ $t->nama }}</label>
                            <input type="number" name="bobot[{{ $t->id }}]" value="{{ $t->bobot }}" min="0" max="100" step="0.5"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                    @endforeach
                    <div class="col-span-2 sm:col-span-12">
                        <button type="submit" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan semua bobot</button>
                    </div>
                </form>
            </div>

            {{-- ===== UBAH TIAP TAHAP ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5">
                <h4 class="text-[14px] font-bold text-slate-800 mb-3">Ubah tahap</h4>

                <div class="space-y-2">
                    @foreach($tahap as $t)
                        <details class="rounded-xl border {{ $t->aktif ? 'border-slate-200' : 'border-slate-200 bg-slate-50' }}">
                            <summary class="cursor-pointer px-3 py-2.5 select-none">
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500">{{ $t->urutan }}</span>
                                    <span class="text-[13px] font-bold text-slate-800">{{ $t->nama }}</span>
                                    <span class="text-[11.5px] text-slate-400">bobot {{ $t->bobot }}%</span>
                                    @unless($t->aktif)
                                        <span class="text-[10.5px] font-bold text-slate-400">(NONAKTIF)</span>
                                    @endunless
                                </span>
                            </summary>
                            <form action="{{ route('project-sr.master.update', $t->id) }}" method="POST" class="p-3 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                                @csrf
                                @method('PUT')
                                <div class="col-span-2 sm:col-span-6">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama tahap *</label>
                                    <input type="text" name="nama" value="{{ $t->nama }}" maxlength="60" required
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Urutan</label>
                                    <input type="number" name="urutan" value="{{ $t->urutan }}" min="0" max="999"
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Bobot %</label>
                                    <input type="number" name="bobot" value="{{ $t->bobot }}" min="0" max="100" step="0.5"
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                </div>
                                <div class="col-span-2 sm:col-span-2 flex items-end">
                                    <label class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-slate-600 mb-2.5">
                                        <input type="hidden" name="aktif" value="0">
                                        <input type="checkbox" name="aktif" value="1" @checked($t->aktif) class="rounded border-slate-300 text-blue-900 focus:ring-blue-200"> Aktif
                                    </label>
                                </div>
                                <div class="col-span-2 sm:col-span-12">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Panduan indikator (dibaca mentor saat menilai)</label>
                                    <textarea name="panduan" rows="2" maxlength="1000"
                                              class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">{{ $t->panduan }}</textarea>
                                </div>
                                <div class="col-span-2 sm:col-span-12">
                                    <button type="submit" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12px] font-semibold transition">Simpan tahap</button>
                                </div>
                            </form>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
