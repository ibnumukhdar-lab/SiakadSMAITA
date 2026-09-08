<x-app-layout>
    <script src="https://cdn.tailwindcss.com"></script>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ⚖️ Putusan Final Sidak Asrama {{ ucfirst($kategori) }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ================= ALERT ERROR (BAGIAN YANG HILANG SEBELUMNYA) ================= -->
            @if(session('error')) 
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-xl shadow-sm font-bold">
                    ❌ {{ session('error') }}
                </div> 
            @endif

            @if($errors->any()) 
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-xl shadow-sm font-bold">
                    <p class="mb-2">Terjadi kesalahan pada input Anda:</p>
                    <ul class="list-disc list-inside ml-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div> 
            @endif
            <!-- ============================================================================== -->

            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-6 rounded-3xl shadow-lg mb-8 text-center border-4 border-blue-400/30">
                <h3 class="text-3xl font-black mb-2">Halaman Hak Prerogatif Musyrif</h3>
                <p class="text-blue-100 font-medium text-sm px-4">Tentukan pemenang mutlak dari daftar kandidat di bawah ini, lalu sertakan bukti foto untuk ditampilkan di TV Display Lobi.</p>
            </div>

            <!-- Tambahkan ID form-finalisasi agar lebih mudah dieksekusi Javascript -->
            <form id="form-finalisasi" action="{{ route('asrama.penilaian.finalisasi') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="kategori" value="{{ $kategori }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    
                    <!-- ================= KANDIDAT TERBERSIH ================= -->
                    <div class="bg-emerald-50 p-6 rounded-3xl border-2 border-emerald-200 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 text-9xl opacity-10">🏆</div>
                        <h4 class="font-black text-emerald-700 text-xl mb-5 flex items-center gap-2 relative z-10">
                            🏆 Kandidat Terbersih 
                            <span class="bg-emerald-200 text-emerald-800 px-2 py-1 rounded text-xs">Skor: {{ $maxSkor }}</span>
                        </h4>
                        
                        <div class="space-y-3 relative z-10">
                            @foreach($kandidatBersih as $k)
                                <label class="flex items-center gap-4 p-4 bg-white border-2 border-emerald-100 rounded-2xl cursor-pointer hover:bg-emerald-100 hover:border-emerald-300 transition-all shadow-sm">
                                    <input type="radio" name="kamar_terbersih_id" value="{{ $k->kamar_id }}" class="w-6 h-6 text-emerald-600 focus:ring-emerald-500" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="font-black text-gray-800 text-lg">{{ $k->kamar->nama_kamar }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 pt-5 border-t border-emerald-200 relative z-10">
                            <label class="block text-sm font-black text-emerald-800 mb-2">📸 Upload Bukti Foto (Opsional)</label>
                            <input type="file" name="foto_terbersih" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer bg-white rounded-xl shadow-inner border border-emerald-100">
                            <p class="text-[10px] text-emerald-600 mt-2 font-bold">* Foto ini akan langsung tayang di TV Display.</p>
                        </div>
                    </div>

                    <!-- ================= KANDIDAT TERKOTOR ================= -->
                    <div class="bg-red-50 p-6 rounded-3xl border-2 border-red-200 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-6 -top-6 text-9xl opacity-10">⚠️</div>
                        <h4 class="font-black text-red-700 text-xl mb-5 flex items-center gap-2 relative z-10">
                            ⚠️ Perhatian Ekstra 
                            <span class="bg-red-200 text-red-800 px-2 py-1 rounded text-xs">Skor: {{ $minSkor }}</span>
                        </h4>
                        
                        <div class="space-y-3 relative z-10">
                            @foreach($kandidatKotor as $k)
                                <label class="flex items-center gap-4 p-4 bg-white border-2 border-red-100 rounded-2xl cursor-pointer hover:bg-red-100 hover:border-red-300 transition-all shadow-sm">
                                    <input type="radio" name="kamar_terkotor_id" value="{{ $k->kamar_id }}" class="w-6 h-6 text-red-600 focus:ring-red-500" {{ $loop->first ? 'checked' : '' }} required>
                                    <span class="font-black text-gray-800 text-lg">{{ $k->kamar->nama_kamar }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 pt-5 border-t border-red-200 relative z-10">
                            <label class="block text-sm font-black text-red-800 mb-2">📸 Upload Bukti Foto (Opsional)</label>
                            <input type="file" name="foto_terkotor" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-red-600 file:text-white hover:file:bg-red-700 cursor-pointer bg-white rounded-xl shadow-inner border border-red-100">
                            <p class="text-[10px] text-red-600 mt-2 font-bold">* Foto peringatan untuk tayang di TV Display.</p>
                        </div>
                    </div>

                </div>

                <!-- ================= TOMBOL EKSEKUSI ================= -->
                <div class="flex flex-col sm:flex-row items-center justify-between border-t-4 border-gray-200 pt-6 mt-4 gap-4">
                    <a href="{{ route('asrama.penilaian.hariIni') }}" class="px-6 py-4 bg-gray-200 text-gray-700 font-black rounded-2xl hover:bg-gray-300 w-full sm:w-auto text-center transition">
                        ⬅️ Kembali 
                    </a>
                    
                    <button type="button" onclick="confirmFinalize()" class="px-8 py-4 bg-gradient-to-r from-slate-800 to-black text-white font-black rounded-2xl shadow-[0_10px_20px_rgba(0,0,0,0.2)] hover:scale-105 transform transition text-lg w-full sm:w-auto text-center flex items-center justify-center gap-3 border-2 border-slate-600">
                        🔒 KUNCI FINALISASI & UMUMKAN
                    </button>
                </div>
                
            </form>

        </div>
    </div>

    <!-- Script Konfirmasi SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmFinalize() {
            Swal.fire({
                title: 'Kunci Hasil Sidak?',
                text: "Data akan disimpan permanen, poin akan disuntikkan ke Student Root, dan foto akan ditayangkan di TV Lobi. Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1e293b',
                cancelButtonColor: '#cbd5e1',
                confirmButtonText: 'Ya, Kunci & Umumkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses Data...',
                        html: 'Menganalisis foto dan menyuntikkan poin. Mohon tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });
                    
                    // Metode ini 100% lebih dijamin berjalan daripada memanggil .click() pada tombol tersembunyi
                    document.getElementById('form-finalisasi').submit();
                }
            })
        }
    </script>
</x-app-layout>