<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                👥 Kelola Anggota Grup: <span class="font-extrabold text-blue-700">{{ $group->nama_grup }}</span>
            </h2>
            <a href="{{ route('sr.grup.index') }}" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                ⬅️ Kembali ke Daftar Grup
            </a>
        </div>
    </x-slot>

    <!-- MEMANGGIL CSS TOM SELECT -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
    <style>
        /* Mengubah styling Tom Select agar mendukung mode Tagging/Multiple */
        .ts-control {
            border-radius: 0.5rem !important; 
            border: 1px solid #d1d5db !important; 
            padding: 0.5rem 0.625rem !important; 
            font-size: 0.875rem !important; 
            box-shadow: none !important;
            background-color: white !important;
            min-height: 46px; /* Tinggi minimum yang proporsional */
        }
        .ts-control.focus {
            border-color: #3b82f6 !important; 
            box-shadow: 0 0 0 1px #3b82f6 !important; 
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            font-size: 0.875rem !important;
            border: 1px solid #d1d5db !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        .ts-dropdown .active {
            background-color: #eff6ff !important; 
            color: #1e3a8a !important; 
        }
        /* Style khusus untuk Chip/Tag Item yang dipilih */
        .ts-control .item {
            background: #e0e7ff !important; /* bg-blue-100 */
            color: #1e40af !important; /* text-blue-800 */
            border-radius: 4px !important;
            border: 1px solid #bfdbfe !important;
            padding: 4px 8px !important;
            font-weight: bold !important;
            margin-bottom: 4px !important;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Info Mentor & Notifikasi -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded shadow-sm">
                <p class="text-blue-800"><strong>Guru Mentor:</strong> {{ $group->mentor->name ?? '-' }} (Mulai: {{ $group->tahun_ajaran_mulai }})</p>
                <p class="text-sm text-blue-600 mt-1">Siswa yang dikeluarkan dari grup tidak akan terhapus dari sistem, namun hanya akan tercatat tanggal keluarnya untuk keperluan histori pembinaan 3 tahun.</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Form Tambah Anggota -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <!-- Penambahan items-start agar form tidak berantakan saat kotak pencarian meninggi ke bawah -->
                <form action="{{ route('sr.grup.addMember', $group->id) }}" method="POST" class="flex flex-col md:flex-row gap-4 items-start md:items-center">
                    @csrf
                    <label class="font-extrabold text-gray-800 whitespace-nowrap md:mt-0">➕ Masukkan Siswa:</label>
                    
                    <div class="w-full md:flex-1">
                        <!-- PERUBAHAN: Ditambahkan name="student_id[]" dan atribut multiple -->
                        <select id="cari-siswa" name="student_id[]" class="w-full" multiple required>
                            @foreach($students as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas: {{ $siswa->kelas }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" style="background-color: #1e293b; color: white;" class="px-6 py-3 rounded-lg text-sm font-bold shadow-sm hover:opacity-90 w-full md:w-auto whitespace-nowrap flex items-center justify-center h-full">Tambahkan Terpilih</button>
                </form>
            </div>

            <!-- Tabel Daftar Anggota -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-200">
                            <th class="p-4 text-sm font-bold">Nama Siswa</th>
                            <th class="p-4 text-sm font-bold">Kelas Saat Ini</th>
                            <th class="p-4 text-sm font-bold text-center">Tanggal Gabung</th>
                            <th class="p-4 text-sm font-bold text-center">Status Keanggotaan</th>
                            <th class="p-4 text-sm font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $member->tanggal_keluar ? 'opacity-60 bg-gray-50' : '' }}">
                            <td class="p-4 font-bold text-gray-800">{{ $member->student->nama_lengkap ?? 'Siswa Dihapus' }}</td>
                            <td class="p-4">{{ $member->student->kelas ?? '-' }}</td>
                            <td class="p-4 text-center">{{ \Carbon\Carbon::parse($member->tanggal_gabung)->translatedFormat('d M Y') }}</td>
                            <td class="p-4 text-center">
                                @if($member->tanggal_keluar)
                                    <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2 py-1 rounded">Keluar: {{ \Carbon\Carbon::parse($member->tanggal_keluar)->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Aktif di Grup</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if(!$member->tanggal_keluar)
                                <form action="{{ route('sr.grup.removeMember', $member->id) }}" method="POST" onsubmit="return confirm('Keluarkan siswa ini dari grup? Histori gabungnya akan tetap disimpan.');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="bg-red-100 text-red-700 border border-red-200 px-3 py-1.5 rounded text-xs font-bold hover:bg-red-200 transition">Keluarkan</button>
                                </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500 italic">Belum ada siswa di grup ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MEMANGGIL SCRIPT TOM SELECT -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi Pencarian Instan Multi-Select
            new TomSelect("#cari-siswa", {
                plugins: ['remove_button'], // Plugin agar ada tombol "x" untuk menghapus pilihan
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                maxOptions: null,
                placeholder: "Ketik dan pilih beberapa siswa sekaligus...",
            });
        });
    </script>
</x-app-layout>