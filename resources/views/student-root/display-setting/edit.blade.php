<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Display TV Koridor</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Sesuaikan teks, durasi, dan gambar di layar TV koridor</p>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <h4 class="text-[15px] font-bold text-slate-800">Form Pengaturan Tampilan</h4>
                </div>

                <form action="{{ route('sr.display.update') }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Input Judul Utama -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Judul Utama Tampilan</label>
                        <input type="text" name="judul_utama" value="{{ old('judul_utama', $setting->judul_utama) }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Contoh: STUDENT ROOT AGREGATOR" required>
                    </div>

                    <!-- Input Durasi Animasi Slide -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Durasi Pergantian Slide (Detik)</label>
                        <input type="number" name="durasi_slide" value="{{ old('durasi_slide', $setting->durasi_slide) }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" min="10" required>
                        <p class="text-xs text-slate-500 mt-1.5">Rekomendasi: 60 detik (1 Menit).</p>
                    </div>

                    <!-- Input Upload Logo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-5 border-t border-slate-100">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Logo Atas (Opsional)</label>
                            @if($setting->logo)
                                <div class="mb-3 relative group w-max">
                                    <!-- DIUBAH KE ASSET BERKAS -->
                                    <img src="{{ asset('berkas/' . $setting->logo) }}" alt="Logo TV" class="h-16 object-contain bg-slate-100 p-2 rounded-lg border border-slate-200">
                                </div>
                            @endif
                            <input type="file" name="logo" accept="image/png, image/jpeg, image/webp" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full border border-slate-300 rounded-lg p-2.5">
                        </div>

                        <!-- Input Upload Background -->
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Gambar Latar / Background (Opsional)</label>
                            @if($setting->background_image)
                                <div class="mb-3">
                                    <!-- DIUBAH KE ASSET BERKAS -->
                                    <img src="{{ asset('berkas/' . $setting->background_image) }}" alt="Background TV" class="h-24 w-full object-cover bg-slate-100 rounded-lg border border-slate-200">
                                </div>
                            @endif
                            <input type="file" name="background_image" accept="image/png, image/jpeg, image/webp" class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full border border-slate-300 rounded-lg p-2.5">
                            <p class="text-xs text-slate-500 mt-2">Disarankan rasio 16:9 (seukuran layar TV), maksimal 5MB.</p>
                        </div>
                    </div>

                    <!-- Tombol Aksi (Simpan & Preview) -->
                    <div class="pt-5 flex flex-col sm:flex-row justify-between items-center border-t border-slate-100 gap-3">

                        <!-- Tombol Buka TV Layar Lebar -->
                        <a href="{{ route('sr.display.tv') }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:w-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Buka Layar TV
                        </a>

                        <!-- Tombol Simpan -->
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-6 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:w-auto">
                            💾 Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
