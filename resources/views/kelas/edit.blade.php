<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-2xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">✏️ Edit Kelas</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">{{ $kelas->nama }} · dipakai {{ $dipakai }} data siswa</p>
                </div>
                <a href="{{ route('kelas.index') }}"
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
                <form action="{{ route('kelas.update', $kelas->id) }}" method="POST" class="grid grid-cols-2 sm:grid-cols-6 gap-3">
                    @csrf
                    @method('PUT')

                    <div class="col-span-2 sm:col-span-3">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama kelas *</label>
                        <input type="text" name="nama" value="{{ old('nama', $kelas->nama) }}" maxlength="60" required
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tingkat</label>
                        <select name="tingkat" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                            <option value="">-</option>
                            @foreach(\App\Models\Kelas::TINGKAT as $t)
                                <option value="{{ $t }}" @selected(old('tingkat', $kelas->tingkat) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Urutan</label>
                        <input type="number" name="urutan" value="{{ old('urutan', $kelas->urutan) }}" min="0" max="999"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 outline-none">
                    </div>

                    <div class="col-span-2 sm:col-span-6">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Wali Kelas</label>
                        <select name="wali_kelas_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-sm text-slate-700 focus:border-blue-500 outline-none">
                            <option value="">— belum ditentukan —</option>
                            @foreach($daftarGuru as $peran => $grup)
                                <optgroup label="{{ $peran }}">
                                    @foreach($grup as $guru)
                                        <option value="{{ $guru['id'] }}" @selected((int) old('wali_kelas_id', $kelas->wali_kelas_id) === $guru['id'])>
                                            {{ $guru['name'] }}{{ $guru['jabatan'] ? ' — ' . $guru['jabatan'] : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <input type="text" name="wali_kelas_nama" value="{{ old('wali_kelas_nama', $kelas->wali_kelas_nama) }}" maxlength="100"
                               placeholder="Atau tulis nama wali kelas (bila belum punya akun)"
                               class="w-full h-10 mt-2 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Kalau kolom nama diisi, itu yang dipakai; kalau kosong, nama akun di atas yang dipakai.</p>
                    </div>

                    <div class="col-span-2 sm:col-span-6">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan', $kelas->keterangan) }}" maxlength="150" placeholder="Opsional"
                               class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>

                    <div class="col-span-2 sm:col-span-6 space-y-2.5">
                        <label class="inline-flex items-center gap-2 text-[13px] text-slate-600">
                            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $kelas->aktif)) class="rounded border-slate-300">
                            Aktif (muncul di pilihan kelas pada form Data Siswa)
                        </label>

                        @if($dipakai > 0)
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                                <label class="inline-flex items-start gap-2 text-[12.5px] text-amber-900">
                                    <input type="checkbox" name="pindahkan_siswa" value="1" @checked(old('pindahkan_siswa')) class="mt-0.5 rounded border-amber-400">
                                    <span>
                                        Ikut pindahkan <strong>{{ $dipakai }} data siswa</strong> bila nama kelasnya diubah.<br>
                                        <span class="text-amber-700">Kalau tidak dicentang, mengganti nama kelas akan ditolak supaya siswa tidak nyangkut di nama kelas lama. Keterangan/urutan/status tetap bisa diubah bebas.</span>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <div class="col-span-2 sm:col-span-6 flex flex-wrap gap-2 pt-1">
                        <button type="submit" class="h-10 px-4 rounded-lg bg-blue-900 text-white text-[13px] font-bold hover:bg-blue-800 transition">Simpan Perubahan</button>
                        <a href="{{ route('kelas.index') }}" class="h-10 px-3.5 inline-flex items-center rounded-lg border border-slate-300 bg-white text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition">Batal</a>
                    </div>
                </form>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mt-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Mengubah nama kelas hanya memengaruhi penamaan di Data Siswa. Kamar <strong>Asrama</strong> dan grup
                <strong>Student Root</strong> tetap memakai data siswanya sendiri (berdasarkan ID siswa), jadi tidak ikut berubah.
            </div>
        </div>
    </div>
</x-app-layout>
