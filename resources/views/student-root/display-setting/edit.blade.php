<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📺 Pengaturan Display TV Koridor
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 relative">
                
                <!-- Label Bantuan -->
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-extrabold text-gray-800">Form Pengaturan Tampilan</h3>
                    <p class="text-sm text-gray-500">Sesuaikan teks, durasi, dan gambar yang akan muncul di layar TV koridor.</p>
                </div>

                <form action="{{ route('sr.display.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Input Judul Utama -->
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-2">Judul Utama Tampilan</label>
                        <input type="text" name="judul_utama" value="{{ old('judul_utama', $setting->judul_utama) }}" class="border-gray-300 rounded-lg w-full focus:ring-blue-500 focus:border-blue-500 p-3" placeholder="Contoh: STUDENT ROOT AGREGATOR" required>
                    </div>

                    <!-- Input Durasi Animasi Slide -->
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-2">Durasi Pergantian Slide (Detik)</label>
                        <input type="number" name="durasi_slide" value="{{ old('durasi_slide', $setting->durasi_slide) }}" class="border-gray-300 rounded-lg w-full focus:ring-blue-500 focus:border-blue-500 p-3" min="10" required>
                        <p class="text-xs text-gray-500 mt-1">Rekomendasi: 60 detik (1 Menit).</p>
                    </div>

                    <!-- Input Upload Logo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-2">Logo Atas (Opsional)</label>
                            @if($setting->logo)
                                <div class="mb-3 relative group w-max">
                                    <!-- DIUBAH KE ASSET BERKAS -->
                                    <img src="{{ asset('berkas/' . $setting->logo) }}" alt="Logo TV" class="h-16 object-contain bg-gray-100 p-2 rounded border border-gray-200">
                                </div>
                            @endif
                            <input type="file" name="logo" accept="image/png, image/jpeg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full border border-gray-200 rounded p-2">
                        </div>

                        <!-- Input Upload Background -->
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-2">Gambar Latar / Background (Opsional)</label>
                            @if($setting->background_image)
                                <div class="mb-3">
                                    <!-- DIUBAH KE ASSET BERKAS -->
                                    <img src="{{ asset('berkas/' . $setting->background_image) }}" alt="Background TV" class="h-24 w-full object-cover bg-gray-100 rounded border border-gray-200">
                                </div>
                            @endif
                            <input type="file" name="background_image" accept="image/png, image/jpeg, image/webp" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 w-full border border-gray-200 rounded p-2">
                            <p class="text-xs text-gray-500 mt-2">Disarankan rasio 16:9 (seukuran layar TV), maksimal 5MB.</p>
                        </div>
                    </div>

                    <!-- Tombol Aksi (Simpan & Preview) -->
                    <div class="pt-6 mt-6 flex flex-col sm:flex-row justify-between items-center border-t border-gray-100 gap-4">
                        
                        <!-- Tombol Buka TV Layar Lebar -->
                        <a href="{{ route('sr.display.tv') }}" target="_blank" style="background-color: #f59e0b; color: white;" class="px-6 py-3 rounded-lg font-bold shadow-md hover:opacity-90 transition flex items-center justify-center w-full sm:w-auto">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            Buka Layar TV
                        </a>
                        
                        <!-- Tombol Simpan -->
                        <button type="submit" style="background-color: #1e3a8a; color: white;" class="px-8 py-3 rounded-lg font-bold shadow-md hover:opacity-90 transition w-full sm:w-auto">
                            💾 Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>