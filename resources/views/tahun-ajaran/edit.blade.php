<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-2xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">✏️ Edit Tahun Ajaran</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">{{ $tahun->nama }} · dipakai {{ $dipakai }} data siswa</p>
                </div>
                <a href="{{ route('tahun-ajaran.index') }}"
                   class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">← Kembali</a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
                <form action="{{ route('tahun-ajaran.update', $tahun->id) }}" method="POST" class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    @csrf
                    @method('PUT')

                    <div class="col-span-2 sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama tahun ajaran *</label>
                        <input type="text" name="nama" value="{{ old('nama', $tahun->nama) }}" maxlength="20" required placeholder="2026/2027"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan', $tahun->keterangan) }}" maxlength="150"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal mulai</label>
                        <input type="date" name="awal" value="{{ old('awal', $tahun->awal?->format('Y-m-d')) }}"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal berakhir</label>
                        <input type="date" name="akhir" value="{{ old('akhir', $tahun->akhir?->format('Y-m-d')) }}"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>

                    <div class="col-span-2 sm:col-span-6 space-y-2.5">
                        <label class="inline-flex items-center gap-2 text-[13px] text-slate-600">
                            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $tahun->aktif)) class="rounded border-slate-300">
                            Jadikan tahun ajaran aktif
                        </label>

                        @if($dipakai > 0)
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                                <label class="inline-flex items-start gap-2 text-[12.5px] text-amber-900">
                                    <input type="checkbox" name="perbarui_siswa" value="1" @checked(old('perbarui_siswa')) class="mt-0.5 rounded border-amber-400">
                                    <span>
                                        Ikut perbarui <strong>{{ $dipakai }} data siswa</strong> bila namanya diubah.<br>
                                        <span class="text-amber-700">Kalau tidak dicentang, mengganti nama tahun ajaran akan ditolak supaya data siswa tidak menunjuk tahun ajaran yang tidak ada. Tanggal/keterangan/status tetap bisa diubah bebas.</span>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="col-span-2 sm:col-span-6 flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="h-10 px-4 rounded-lg bg-blue-900 text-white text-[13px] font-bold hover:bg-blue-800 transition">Simpan Perubahan</button>
                        <a href="{{ route('tahun-ajaran.index') }}" class="h-10 px-3.5 inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
