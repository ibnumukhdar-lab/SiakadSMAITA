<x-app-layout>
    <!-- Tambahkan x-data untuk mengontrol Modal Edit -->
    <div class="py-8" x-data="{
        showEditModal: false,
        editForm: {
            actionUrl: '',
            nama_grup: '',
            mentor_id: '',
            tahun_ajaran_mulai: '',
            warna_grup: '#1e3a8a',
            status: 'aktif'
        }
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Manajemen Grup Student Root</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola grup binaan, mentor, dan status keanggotaan</p>
                </div>
            </div>

            <!-- Notifikasi -->
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

            <!-- Form Tambah Grup -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h4 class="text-[15px] font-bold text-slate-800">➕ Buat Grup Baru</h4>
                </div>

                <form action="{{ route('sr.grup.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center p-5 sm:p-6">
                    @csrf
                    <input type="text" name="nama_grup" placeholder="Nama Grup (mis: Al-Fatih)" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                    
                    <select name="mentor_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        <option value="">-- Pilih Guru Mentor --</option>
                        @foreach($teachers as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="tahun_ajaran_mulai" placeholder="Tahun Mulai (mis: 2026/2027)" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                    
                    <!-- INPUT WARNA KUSTOM (TAMBAH GRUP) -->
                    <div class="flex items-center justify-between gap-2 border border-slate-300 rounded-lg p-1 bg-white hover:border-blue-400 transition cursor-pointer" onclick="document.getElementById('warna_grup').click()">
                        <label for="warna_grup" class="text-xs font-bold text-slate-500 pl-2 cursor-pointer w-full">Warna Grup</label>
                        <input type="color" id="warna_grup" name="warna_grup" value="#1e3a8a" class="h-8 w-10 cursor-pointer border-0 rounded bg-transparent p-0 m-0 shrink-0" required title="Pilih Warna Grup">
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 w-full px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan Grup</button>
                </form>
            </div>

            <!-- Tabel Daftar Grup -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[860px]">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Grup</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Mentor</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Tahun Mulai</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Anggota Aktif</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $g)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-bold text-slate-800 flex items-center gap-3">
                                <!-- Indikator Warna Bulat -->
                                <div class="w-4 h-4 rounded-full border border-slate-200 shadow-sm shrink-0" style="background-color: {{ $g->warna_grup ?? '#1e3a8a' }};" title="Warna Grup"></div>
                                {{ $g->nama_grup }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $g->mentor->name ?? 'Tidak Ada Mentor' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 text-center">{{ $g->tahun_ajaran_mulai }}</td>
                            <td class="px-4 py-3 text-sm font-bold text-slate-700 text-center">{{ $g->members_count }} Siswa</td>
                            <td class="px-4 py-3 text-center">
                                @if($g->status == 'aktif')
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-green-50 text-green-700 border border-green-200">Aktif</span>
                                @else
                                    <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('sr.grup.show', $g->id) }}" title="Kelola Anggota" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👥</a>
                                
                                <!-- Tombol Edit Membuka Modal Alpine.js -->
                                <button type="button" @click="
                                    editForm.actionUrl = '{{ route('sr.grup.update', $g->id) }}';
                                    editForm.nama_grup = '{{ addslashes($g->nama_grup) }}';
                                    editForm.mentor_id = '{{ $g->mentor_id }}';
                                    editForm.tahun_ajaran_mulai = '{{ addslashes($g->tahun_ajaran_mulai) }}';
                                    editForm.warna_grup = '{{ $g->warna_grup ?? '#1e3a8a' }}';
                                    editForm.status = '{{ $g->status ?? 'aktif' }}';
                                    showEditModal = true;
                                " class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Edit Grup">
                                    ✏️
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('sr.grup.destroy', $g->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus grup ini secara permanen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Grup">🗑️</button>
                                </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500 italic">Belum ada grup yang dibuat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL EDIT GRUP (Muncul saat tombol Edit diklik) -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden" @click.away="showEditModal = false">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex justify-between items-center">
                    <h3 class="text-[15px] font-bold text-slate-800">✏️ Edit Data Grup</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="p-6">
                    <form :action="editForm.actionUrl" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nama Grup</label>
                            <input type="text" name="nama_grup" x-model="editForm.nama_grup" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Guru Mentor</label>
                            <select name="mentor_id" x-model="editForm.mentor_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                <option value="">-- Pilih Guru Mentor --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tahun Mulai</label>
                                <input type="text" name="tahun_ajaran_mulai" x-model="editForm.tahun_ajaran_mulai" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>
                            
                            <!-- INPUT STATUS -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status Grup</label>
                                <select name="status" x-model="editForm.status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            
                            <!-- INPUT WARNA KUSTOM (MODAL EDIT) -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Warna Grup</label>
                                <div class="flex items-center gap-2 border border-slate-300 rounded-lg p-1 bg-white w-full h-[42px] cursor-pointer" onclick="document.getElementById('edit_warna_grup').click()">
                                    <input type="color" id="edit_warna_grup" name="warna_grup" x-model="editForm.warna_grup" class="h-full w-full cursor-pointer border-0 rounded bg-transparent p-0 m-0">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                            <button type="button" @click="showEditModal = false" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</button>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- END MODAL EDIT GRUP -->

    </div>
</x-app-layout>