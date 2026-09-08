<x-app-layout>
    <!-- MEMANGGIL CSS TOM SELECT -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css" rel="stylesheet">
    <style>
        .ts-control { border-radius: 0.5rem !important; border: 1px solid #d1d5db !important; padding: 0.5rem 0.625rem !important; font-size: 0.875rem !important; box-shadow: none !important; background-color: white !important; min-height: 46px; }
        .ts-control.focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 1px #3b82f6 !important; }
        .ts-dropdown { border-radius: 0.5rem !important; font-size: 0.875rem !important; border: 1px solid #d1d5db !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important; }
        .ts-dropdown .active { background-color: #eff6ff !important; color: #1e3a8a !important; }
        .ts-control .item { background: #e0e7ff !important; color: #1e40af !important; border-radius: 4px !important; border: 1px solid #bfdbfe !important; padding: 4px 8px !important; font-weight: bold !important; margin-bottom: 4px !important; }
    </style>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛏️ Kelola Penghuni: <span class="text-blue-900">{{ $kamar->nama_kamar }}</span></h3>
                    <p class="text-sm text-slate-500 mt-0.5">Atur daftar siswa penghuni kamar {{ $kamar->nama_kamar }}</p>
                </div>
                <a href="{{ route('asrama.kamar.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    ⬅️ Kembali ke Daftar Kamar
                </a>
            </div>

            <!-- Info Musyrif Kamar -->
            <div class="bg-sky-50 border border-sky-200 rounded-xl px-4 py-3.5 mb-6">
                <p class="text-sm font-bold text-blue-900 m-0">Guru Musyrif: {{ $kamar->musyrif->name ?? 'Belum Ditentukan' }}</p>
                <p class="text-[13px] text-sky-900/70 mt-0.5 m-0">Gunakan kotak di bawah ini untuk menambahkan penghuni baru. Siswa yang dikeluarkan dari daftar ini akan dihapus secara permanen dari kamar tanpa histori.</p>
            </div>

            <!-- Notifikasi -->
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    ❌ {{ session('error') }}
                </div>
            @endif

            <!-- Form Tambah Penghuni Kamar -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6 mb-6">
                <h4 class="text-[15px] font-bold text-slate-800 mb-4">➕ Tambah Penghuni Baru</h4>
                <form action="{{ route('asrama.kamar.addMember', $kamar->id) }}" method="POST" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[260px]">
                        <label for="cari-siswa" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pilih Siswa</label>
                        <select id="cari-siswa" name="student_id[]" multiple required>
                            @foreach($students as $siswa)
                                <option value="{{ $siswa->id }}">{{ $siswa->nama_lengkap }} (Kelas: {{ $siswa->kelas }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                        ➕ Tambahkan ke Kamar
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Penghuni Kamar -->
            <div class="bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h4 class="text-[15px] font-bold text-slate-800">Daftar Penghuni Saat Ini</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Siswa</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelas</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Tanggal Masuk</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $member->student->nama_lengkap ?? 'Siswa Dihapus' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $member->student->kelas ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 text-center font-medium">{{ \Carbon\Carbon::parse($member->tanggal_masuk)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route('asrama.kamar.removeMember', $member->id) }}" method="POST" class="m-0 inline-block" onsubmit="return confirm('Keluarkan siswa ini dari kamar? Data penghuni akan dihapus permanen dari daftar ini.');">
                                        @csrf @method('PUT')
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold transition">🚪 Keluarkan</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-12 text-center text-slate-500 font-medium">
                                    <span class="text-4xl block mb-3">🛏️</span>
                                    Belum ada penghuni di kamar ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MEMANGGIL SCRIPT TOM SELECT -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#cari-siswa", {
                plugins: ['remove_button'],
                create: false,
                sortField: { field: "text", direction: "asc" },
                maxOptions: null,
                placeholder: "Ketik dan pilih beberapa siswa sekaligus...",
            });
        });
    </script>
</x-app-layout>
