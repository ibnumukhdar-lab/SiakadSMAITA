<x-app-layout>
    <div class="py-8" x-data="{
        showEditModal: false,
        editForm: { actionUrl: '', nama_kamar: '', kategori: 'putra', musyrif_id: '', kapasitas: '', status: 'aktif' }
    }">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛏️ Manajemen Kamar Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola kamar, musyrif, dan kapasitas asrama</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    Ada kesalahan input:
                    <ul class="list-disc list-inside ml-4 mt-1 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Tambah Kamar -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-6">
                <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                    <h4 class="text-[15px] font-bold text-slate-800">➕ Tambah Kamar Baru</h4>
                </div>
                <form action="{{ route('asrama.kamar.store') }}" method="POST" class="p-5 sm:p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label for="nama_kamar" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Kamar</label>
                            <input type="text" id="nama_kamar" name="nama_kamar" placeholder="cth: Al-Amin" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>
                        <div>
                            <label for="kategori" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori</label>
                            <select id="kategori" name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="putra">Asrama Putra</option>
                                <option value="putri">Asrama Putri</option>
                            </select>
                        </div>
                        <div>
                            <label for="musyrif_id" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Musyrif</label>
                            <select id="musyrif_id" name="musyrif_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="">-- Musyrif --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="kapasitas" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kapasitas</label>
                            <input type="number" id="kapasitas" name="kapasitas" placeholder="Kapasitas" value="4" min="1" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required title="Kapasitas Kamar">
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Kamar -->
            <div class="bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Kamar</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Musyrif</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Kapasitas</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Penghuni</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kamars as $kamar)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-sm font-bold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($kamar->kategori == 'putra')
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $kamar->musyrif->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-700 text-center font-semibold">{{ $kamar->kapasitas }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="font-bold text-blue-900">{{ $kamar->members_count }}</span>
                                    <span class="text-slate-500 font-medium">Org</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-center gap-1.5">
                                        <a href="{{ route('asrama.kamar.show', $kamar->id) }}" title="Kelola Penghuni" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👥</a>

                                        <button type="button" @click="
                                            editForm.actionUrl = '{{ route('asrama.kamar.update', $kamar->id) }}';
                                            editForm.nama_kamar = '{{ addslashes($kamar->nama_kamar) }}';
                                            editForm.kategori = '{{ $kamar->kategori }}';
                                            editForm.musyrif_id = '{{ $kamar->musyrif_id }}';
                                            editForm.kapasitas = '{{ $kamar->kapasitas }}';
                                            editForm.status = '{{ $kamar->status }}';
                                            showEditModal = true;
                                        " title="Edit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition">✏️</button>

                                        <form action="{{ route('asrama.kamar.destroy', $kamar->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus kamar permanen?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-500 font-medium">
                                    <span class="text-4xl block mb-3">🛏️</span>
                                    Belum ada kamar yang terdaftar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- MODAL EDIT -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-opacity duration-300">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden" @click.away="showEditModal = false">
                <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between bg-slate-50/60">
                    <h3 class="text-[15px] font-bold text-slate-800">✏️ Edit Data Kamar</h3>
                    <button type="button" @click="showEditModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition text-xl leading-none">&times;</button>
                </div>
                <form :action="editForm.actionUrl" method="POST">
                    @csrf @method('PUT')
                    <div class="p-5 sm:p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Kamar</label>
                            <input type="text" name="nama_kamar" x-model="editForm.nama_kamar" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori Asrama</label>
                            <select name="kategori" x-model="editForm.kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="putra">Asrama Putra</option>
                                <option value="putri">Asrama Putri</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Guru Musyrif</label>
                            <select name="musyrif_id" x-model="editForm.musyrif_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="">-- Pilih Guru Musyrif --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kapasitas</label>
                                <input type="number" name="kapasitas" x-model="editForm.kapasitas" min="1" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                                <select name="status" x-model="editForm.status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 bg-slate-50/60 px-5 py-4 flex justify-end gap-2.5">
                        <button type="button" @click="showEditModal = false" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
