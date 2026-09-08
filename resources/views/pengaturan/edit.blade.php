<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Lembaga</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Identitas, logo &amp; sampul dashboard lembaga</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-5 bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 lg:p-8">
                    <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Lembaga / Sekolah</label>
                                <input type="text" name="nama_sekolah" value="{{ $pengaturan->nama_sekolah }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Motto / Tagline</label>
                                <input type="text" name="motto" value="{{ $pengaturan->motto }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>
                        </div>

                        <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-5 sm:p-6 mb-6">
                            <div class="mb-5">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">🖼️ Upload Logo Sekolah</label>
                                @if($pengaturan->logo_path)
                                    <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                                    <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="h-16 mb-3 rounded-lg shadow-sm bg-white p-1 border border-slate-200 object-contain">
                                @endif
                                <input type="file" name="logo_path" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-900 file:text-white hover:file:bg-blue-800 transition">
                            </div>

                            <hr class="my-5 border-slate-200">

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">🏔️ Upload Sampul Dashboard (Opsional)</label>
                                @if($pengaturan->sampul_path)
                                    <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                                    <img src="{{ url('berkas/' . $pengaturan->sampul_path) }}" class="h-24 w-auto mb-3 rounded-lg shadow-sm bg-white p-1 border border-slate-200 object-contain">
                                @endif
                                <input type="file" name="sampul_path" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-900 file:text-white hover:file:bg-blue-800 transition">
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-6 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                💾 Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
