<x-app-layout>

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

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">👥 Kelola Anggota Grup</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Grup {{ $group->nama_grup }} — kelola keanggotaan &amp; histori binaan</p>
                </div>
                <a href="{{ route('sr.grup.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    ⬅️ Kembali ke Daftar Grup
                </a>
            </div>
            <!-- Info Mentor & Notifikasi -->
            <div class="bg-sky-50 border border-sky-200 rounded-xl px-5 py-4 mb-6">
                <p class="text-sky-900 font-medium text-sm"><strong>Guru Mentor:</strong> {{ $group->mentor->name ?? '-' }} (Mulai: {{ $group->tahun_ajaran_mulai }})</p>
                <p class="text-xs text-sky-700 mt-1">Siswa yang dikeluarkan dari grup tidak akan terhapus dari sistem, namun hanya akan tercatat tanggal keluarnya untuk keperluan histori pembinaan 3 tahun.</p>
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
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h4 class="text-[15px] font-bold text-slate-800">➕ Tambah Anggota Baru</h4>
                </div>
                <!-- Penambahan items-start agar form tidak berantakan saat kotak pencarian meninggi ke bawah -->
                <form action="{{ route('sr.grup.addMember', $group->id) }}" method="POST" class="flex flex-col md:flex-row gap-4 items-start md:items-center p-5 sm:p-6">
                    @csrf
                    <label class="text-sm font-bold text-slate-700 whitespace-nowrap md:mt-0">Masukkan Siswa:</label>
                    
                    <div class="w-full md:flex-1">
                        <!-- PERUBAHAN: Ditambahkan name="student_id[]" dan atribut multiple -->
                        <select id="cari-siswa" name="student_id[]" class="w-full" multiple required>
                            @foreach($students as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas: {{ $siswa->kelas }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap w-full md:w-auto">Tambahkan Terpilih</button>
                </form>
            </div>

            <!-- Tabel Daftar Anggota -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[820px]">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Siswa</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas Saat Ini</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Tanggal Gabung</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status Keanggotaan</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition {{ $member->tanggal_keluar ? 'opacity-60 bg-slate-50' : '' }}">
                            <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $member->student->nama_lengkap ?? 'Siswa Dihapus' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $member->student->kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ \Carbon\Carbon::parse($member->tanggal_gabung)->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($member->tanggal_keluar)
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Keluar: {{ \Carbon\Carbon::parse($member->tanggal_keluar)->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif di Grup</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(!$member->tanggal_keluar)
                                <form action="{{ route('sr.grup.removeMember', $member->id) }}" method="POST" onsubmit="return confirm('Keluarkan siswa ini dari grup? Histori gabungnya akan tetap disimpan.');">
                                    @csrf @method('PUT')
                                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-9 px-3.5 rounded-lg bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 text-xs font-bold transition whitespace-nowrap">Keluarkan</button>
                                </form>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500 italic">Belum ada siswa di grup ini.</td>
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