<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">➕ Ajukan Izin Santri</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Pengajuan akan masuk ke Kepala Diniyah untuk disetujui</p>
                </div>
                <a href="{{ route('asrama.izin.index') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">⬅️ Daftar Izin</a>
            </div>

            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <p class="font-bold mb-1">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @if($penghuni->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">🛏️</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum ada santri yang bisa diizinkan</h3>
                    <p class="text-sm text-slate-400">Pastikan Anda musyrif kamar binaan, atau minta Kepala Diniyah memetakan kamar Anda.</p>
                </div>
            @else
                <form action="{{ route('asrama.izin.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Santri</label>
                        <select name="student_id" required class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">-- pilih santri --</option>
                            @php $kamarTerakhir = null; @endphp
                            @foreach($penghuni as $p)
                                @if($kamarTerakhir !== $p->nama_kamar)
                                    @if($kamarTerakhir !== null) </optgroup> @endif
                                    <optgroup label="Kamar {{ $p->nama_kamar }} ({{ ucfirst($p->kategori) }})">
                                    @php $kamarTerakhir = $p->nama_kamar; @endphp
                                @endif
                                <option value="{{ $p->student_id }}" @selected($pilihSiswa === (int) $p->student_id)>{{ $p->nama_lengkap }} — {{ $p->kelas ?? '-' }}</option>
                            @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Jenis Izin</label>
                            <select name="jenis" required class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                @foreach(\App\Models\AsramaIzin::JENIS as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected(old('jenis') === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Mulai</label>
                            <input type="date" name="mulai" value="{{ old('mulai', now()->toDateString()) }}" required class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sampai (harus kembali)</label>
                            <input type="date" name="sampai" value="{{ old('sampai', now()->toDateString()) }}" required class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Alasan</label>
                        <textarea name="alasan" rows="3" required maxlength="1000" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="cth: menghadiri acara keluarga di rumah">{{ old('alasan') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tujuan (opsional)</label>
                            <input type="text" name="tujuan" value="{{ old('tujuan') }}" maxlength="150" placeholder="cth: rumah orang tua, Bandung" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Penanggung jawab (opsional)</label>
                            <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" maxlength="150" placeholder="cth: ayah / wali santri" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">📨 Ajukan Izin</button>
                        <a href="{{ route('asrama.izin.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
