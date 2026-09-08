<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            📝 Input Poin Sikap Siswa
        </h2>
    </x-slot>

    <!-- Memanggil CSS Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
    <style>
        /* Menyesuaikan tampilan Tom Select agar sama persis dengan form Tailwind */
        .ts-control {
            border-radius: 0.5rem !important; /* rounded-lg */
            border: 1px solid #d1d5db !important; /* border-gray-300 */
            padding: 0.625rem !important; /* p-2.5 */
            font-size: 0.875rem !important; /* text-sm */
            box-shadow: none !important;
            background-color: white !important;
        }
        .ts-control.focus {
            border-color: #3b82f6 !important; /* focus:border-blue-500 */
            box-shadow: 0 0 0 1px #3b82f6 !important; /* focus:ring-blue-500 */
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            border: 1px solid #d1d5db !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        .ts-dropdown .active {
            background-color: #eff6ff !important; /* bg-blue-50 */
            color: #1e3a8a !important; /* text-blue-900 */
        }
    </style>

    <div class="py-12 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm font-bold">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Bagian Form Input (Kiri) -->
                <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-sm border border-gray-200 self-start">
                    <h3 class="text-lg font-extrabold mb-4 text-gray-800 border-b pb-2">Form Pencatatan</h3>
                    <form action="{{ route('sr.poin.store') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Siswa <span class="text-red-500">*</span></label>
                            <select id="student_id" name="student_id" class="w-full" required>
                                <option value="">🔍 Ketik nama atau kelas...</option>
                                @foreach($students as $siswa)
                                    <option value="{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas {{ $siswa->kelas }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kriteria Sikap <span class="text-red-500">*</span></label>
                            <select id="criteria_id" name="criteria_id" class="w-full" required>
                                <option value="">🔍 Ketik jenis perilaku...</option>
                                @foreach($criterias as $kriteria)
                                    <option value="{{ $kriteria->id }}">
                                        {{ $kriteria->kategori == 'positif' ? '✅' : '❌' }} {{ $kriteria->nama_perilaku }} (Poin: {{ $kriteria->poin > 0 ? '+'.$kriteria->poin : $kriteria->poin }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Kejadian <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kejadian" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-lg text-sm p-2.5 focus:border-blue-500 focus:ring-blue-500" required>
                            <p class="text-xs text-gray-500 mt-1">Hanya bisa mengisi tanggal hari ini atau mundur ke belakang.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Catatan/Keterangan</label>
                            <textarea name="catatan" rows="3" class="w-full border-gray-300 rounded-lg text-sm p-2.5 focus:border-blue-500 focus:ring-blue-500" placeholder="Tambahkan detail kejadian jika diperlukan..."></textarea>
                        </div>

                        <button type="submit" style="background-color: #1e293b; color: white;" class="w-full py-4 rounded-lg text-sm font-bold shadow-sm hover:opacity-90 transition">
                            Simpan Poin
                        </button>
                    </form>
                </div>

                <!-- Bagian Histori Input Terakhir (Kanan) -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <h3 class="text-lg font-extrabold mb-4 text-gray-800 border-b pb-2">🕒 Riwayat Input Terakhir Anda</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-full">
                            <thead>
                                <tr class="bg-gray-100 border-b-2 border-gray-200">
                                    <th class="p-3 text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap">Tgl Kejadian</th>
                                    <th class="p-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Siswa</th>
                                    <th class="p-3 text-xs font-bold text-gray-600 uppercase tracking-wider">Perilaku & Catatan</th>
                                    <th class="p-3 text-xs font-bold text-center text-gray-600 uppercase tracking-wider">Poin</th>
                                    <th class="p-3 text-xs font-bold text-center text-gray-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_entries as $entry)
                                    @php
                                        // Pengecekan otomatis kolom catatan atau keterangan
                                        $catatan = $entry->catatan ?? $entry->keterangan ?? '';
                                    @endphp
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 text-sm whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($entry->tanggal_kejadian)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="p-3 text-sm font-bold text-gray-800 leading-tight">
                                        {{ $entry->student->nama_lengkap ?? 'Siswa Dihapus' }}
                                    </td>
                                    <td class="p-3 text-sm">
                                        <div class="font-bold text-gray-700">{{ $entry->criteria->nama_perilaku ?? 'Kriteria Dihapus' }}</div>
                                        @if($catatan)
                                            <div class="text-xs text-gray-500 mt-1 flex items-start">
                                                <span class="mr-1 text-gray-400">↳</span> 
                                                <span class="italic">"{{ $catatan }}"</span>
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 mt-1 italic">- Tanpa catatan -</div>
                                        @endif
                                    </td>
                                    <td class="p-3 text-sm font-bold text-center">
                                        @if($entry->poin > 0)
                                            <span class="text-green-700 bg-green-100 px-3 py-1 rounded-full whitespace-nowrap">+{{ $entry->poin }}</span>
                                        @else
                                            <span class="text-red-700 bg-red-100 px-3 py-1 rounded-full whitespace-nowrap">{{ $entry->poin }}</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Tombol Edit Pop-up -->
                                            <button type="button" 
                                                data-id="{{ $entry->id }}" 
                                                data-poin="{{ $entry->poin }}" 
                                                data-catatan="{{ $catatan }}"
                                                onclick="openTailwindModal(this)"
                                                class="p-1.5 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-200 transition" title="Edit Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            
                                            <!-- Tombol Hapus -->
                                            @if(Route::has('poin.destroy'))
                                                <form action="{{ route('poin.destroy', $entry->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin membatalkan/menghapus input poin ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 bg-red-50 text-red-500 rounded-md hover:bg-red-200 transition" title="Batalkan/Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-500 italic text-sm">Belum ada riwayat input poin yang Anda catat hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT TAILWIND -->
    <div id="modalEditTW" class="fixed inset-0 z-50 hidden flex items-center justify-center transition-opacity duration-300 opacity-0">
        <!-- Latar belakang gelap (Backdrop) -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" onclick="closeTailwindModal()"></div>
        
        <!-- Kotak Modal -->
        <div id="modalBoxTW" class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 z-10 transform scale-95 transition-transform duration-300">
            <!-- Header Modal -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-800">✏️ Edit Data Poin</h3>
                <button type="button" onclick="closeTailwindModal()" class="text-gray-400 hover:text-red-500 text-2xl font-bold leading-none transition">&times;</button>
            </div>
            
            <!-- Form Modal -->
            <form id="formEditTW" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Ubah Nilai Poin</label>
                    <input type="number" name="poin" id="inputPoinTW" class="w-full border-gray-300 rounded-lg text-sm p-3 focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 uppercase tracking-wide">Catatan / Alasan</label>
                    <textarea name="catatan" id="inputCatatanTW" rows="4" class="w-full border-gray-300 rounded-lg text-sm p-3 focus:border-blue-500 focus:ring-blue-500" placeholder="Kosongkan jika tidak ada catatan..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" onclick="closeTailwindModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-bold rounded-lg hover:bg-gray-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md hover:shadow-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Memanggil Script JS Tom Select & Inisialisasi -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Aktivasi search dropdown untuk Nama Siswa
            new TomSelect("#student_id", {
                create: false,
                maxOptions: null, // Menghilangkan batas jumlah hasil agar tidak terpotong
            });

            // Aktivasi search dropdown untuk Kriteria Sikap
            new TomSelect("#criteria_id", {
                create: false,
                maxOptions: null,
            });
        });

        // FUNGSI UNTUK MENGENDALIKAN MODAL POP-UP EDIT (TAILWIND)
        function openTailwindModal(button) {
            // Ambil data dari tombol
            let id = button.getAttribute('data-id');
            let poin = button.getAttribute('data-poin');
            let catatan = button.getAttribute('data-catatan');

            // Set URL Action
            let baseUrl = "{{ url('/poin') }}";
            document.getElementById('formEditTW').action = baseUrl + '/' + id;
            
            // Set Isi Input
            document.getElementById('inputPoinTW').value = poin;
            document.getElementById('inputCatatanTW').value = catatan;
            
            // Munculkan Modal dengan Animasi
            const modal = document.getElementById('modalEditTW');
            const box = document.getElementById('modalBoxTW');
            
            modal.classList.remove('hidden');
            // Trigger reflow (memaksa browser memproses DOM sebelum animasi berjalan)
            void modal.offsetWidth; 
            
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
            box.classList.add('scale-100');
        }

        function closeTailwindModal() {
            const modal = document.getElementById('modalEditTW');
            const box = document.getElementById('modalBoxTW');
            
            // Hilangkan dengan Animasi
            modal.classList.add('opacity-0');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            
            // Sembunyikan elemen setelah animasi selesai (300ms)
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</x-app-layout>