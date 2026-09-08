<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-xl font-extrabold tracking-tight text-slate-900">🐝 BEE Smart</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Kelola modul kosakata mingguan untuk layar TV &amp; papan interaktif kelas</p>
                </div>

                <!-- Form Tambah Minggu -->
                <form action="{{ route('bee.storeWeek') }}" method="POST" class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                    @csrf
                    <input type="text" name="judul" placeholder="Contoh: Minggu 1: Lingkungan" required
                        class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition sm:w-72">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                        ➕ Buat Modul
                    </button>
                </form>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[850px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Judul Modul</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Isi Kosakata</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status Tayang</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($weeks as $week)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="px-4 py-3.5">
                                    <div class="text-[15px] font-bold text-slate-900">{{ $week->judul }}</div>
                                    <div class="mt-0.5 text-xs font-medium text-slate-400">Dibuat: {{ \Carbon\Carbon::parse($week->tanggal_mulai)->translatedFormat('d F Y') }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-block px-3 py-1 text-xs font-bold rounded-full border border-slate-200 bg-slate-100 text-slate-600">
                                        {{ $week->vocabs_count }} / 10 Kata
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <form action="{{ route('bee.updateStatus', $week->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                            class="h-9 min-w-[130px] cursor-pointer appearance-none rounded-full border px-3 text-center text-xs font-bold outline-none transition {{ $week->status == 'aktif' ? 'bg-green-50 text-green-700 border-green-200' : ($week->status == 'draft' ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                            <option value="draft" {{ $week->status == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                            <option value="aktif" {{ $week->status == 'aktif' ? 'selected' : '' }}>📺 TAYANG DI TV</option>
                                            <option value="arsip" {{ $week->status == 'arsip' ? 'selected' : '' }}>📚 Arsip</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Kelola -->
                                        <a href="{{ route('bee.manage', $week->id) }}" title="Input Kosakata"
                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">✍️</a>

                                        <!-- Tombol Edit Judul (Memicu JS) -->
                                        <button type="button" onclick="editJudul('{{ $week->id }}', '{{ addslashes($week->judul) }}')" title="Edit Judul"
                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</button>

                                        <!-- Form Tersembunyi untuk Edit Judul -->
                                        <form id="edit-form-{{ $week->id }}" action="{{ route('bee.updateWeek', $week->id) }}" method="POST" style="display: none;">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="judul" id="edit-input-{{ $week->id }}">
                                        </form>

                                        <!-- Tombol Hapus (Hard Delete) -->
                                        <form action="{{ route('bee.destroyWeek', $week->id) }}" method="POST" onsubmit="return confirm('⚠️ PERINGATAN KERAS!\n\nYakin ingin menghapus modul ini?\n\nSeluruh KOSAKATA beserta REKAMAN AUDIO di dalamnya akan TERHAPUS PERMANEN (Hard Delete) dan tidak dapat dikembalikan.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus Modul"
                                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-slate-500 font-medium">
                                    <span class="text-4xl block mb-3">📭</span>
                                    Belum ada modul kosakata. Silakan buat modul pertama Anda!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPT UNTUK POP-UP EDIT JUDUL -->
    <script>
        function editJudul(id, oldJudul) {
            let newJudul = prompt("Masukkan Judul Modul yang baru:", oldJudul);
            if (newJudul != null && newJudul.trim() !== "") {
                document.getElementById('edit-input-' + id).value = newJudul;
                document.getElementById('edit-form-' + id).submit();
            }
        }
    </script>
</x-app-layout>
