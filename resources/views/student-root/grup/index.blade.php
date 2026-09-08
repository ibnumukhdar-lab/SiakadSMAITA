<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🏢 Manajemen Grup Student Root
        </h2>
    </x-slot>

    <!-- Tambahkan x-data untuk mengontrol Modal Edit -->
    <div class="py-12" x-data="{
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
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-extrabold mb-4 text-gray-800">➕ Buat Grup Baru</h3>
                
                <form action="{{ route('sr.grup.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                    @csrf
                    <input type="text" name="nama_grup" placeholder="Nama Grup (mis: Al-Fatih)" class="border-gray-300 rounded-lg text-sm p-2.5 w-full" required>
                    
                    <select name="mentor_id" class="border-gray-300 rounded-lg text-sm p-2.5 w-full" required>
                        <option value="">-- Pilih Guru Mentor --</option>
                        @foreach($teachers as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" name="tahun_ajaran_mulai" placeholder="Tahun Mulai (mis: 2026/2027)" class="border-gray-300 rounded-lg text-sm p-2.5 w-full" required>
                    
                    <!-- INPUT WARNA KUSTOM (TAMBAH GRUP) -->
                    <div class="flex items-center justify-between gap-2 border border-gray-300 rounded-lg p-1 bg-white shadow-sm hover:border-blue-400 transition cursor-pointer" onclick="document.getElementById('warna_grup').click()">
                        <label for="warna_grup" class="text-xs font-bold text-gray-600 pl-2 cursor-pointer w-full">Warna Grup</label>
                        <input type="color" id="warna_grup" name="warna_grup" value="#1e3a8a" class="h-8 w-10 cursor-pointer border-0 rounded bg-transparent p-0 m-0 shrink-0" required title="Pilih Warna Grup">
                    </div>

                    <button type="submit" style="background-color: #1e293b; color: white;" class="w-full px-5 py-4 rounded-lg text-xs font-bold shadow-sm hover:opacity-90 transition">Simpan Grup</button>
                </form>
            </div>

            <!-- Tabel Daftar Grup -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-200">
                            <th class="p-4 text-sm font-bold">Nama Grup</th>
                            <th class="p-4 text-sm font-bold">Mentor</th>
                            <th class="p-4 text-sm font-bold text-center">Tahun Mulai</th>
                            <th class="p-4 text-sm font-bold text-center">Anggota Aktif</th>
                            <th class="p-4 text-sm font-bold text-center">Status</th>
                            <th class="p-4 text-sm font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $g)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 font-bold text-gray-800 flex items-center gap-3">
                                <!-- Indikator Warna Bulat -->
                                <div class="w-4 h-4 rounded-full border border-gray-300 shadow-sm shrink-0" style="background-color: {{ $g->warna_grup ?? '#1e3a8a' }};" title="Warna Grup"></div>
                                {{ $g->nama_grup }}
                            </td>
                            <td class="p-4">{{ $g->mentor->name ?? 'Tidak Ada Mentor' }}</td>
                            <td class="p-4 text-center">{{ $g->tahun_ajaran_mulai }}</td>
                            <td class="p-4 text-center font-bold text-blue-600">{{ $g->members_count }} Siswa</td>
                            <td class="p-4 text-center">
                                @if($g->status == 'aktif')
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded">Aktif</span>
                                @else
                                    <span class="bg-gray-200 text-gray-800 text-xs font-bold px-2.5 py-0.5 rounded">Nonaktif</span>
                                @endif
                            </td>
                            <td class="p-4 text-center space-x-2 whitespace-nowrap">
                                <a href="{{ route('sr.grup.show', $g->id) }}" style="background-color: #4f46e5; color: white;" class="inline-block px-4 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90 transition">Kelola Anggota</a>
                                
                                <!-- Tombol Edit Membuka Modal Alpine.js -->
                                <button type="button" @click="
                                    editForm.actionUrl = '{{ route('sr.grup.update', $g->id) }}';
                                    editForm.nama_grup = '{{ addslashes($g->nama_grup) }}';
                                    editForm.mentor_id = '{{ $g->mentor_id }}';
                                    editForm.tahun_ajaran_mulai = '{{ addslashes($g->tahun_ajaran_mulai) }}';
                                    editForm.warna_grup = '{{ $g->warna_grup ?? '#1e3a8a' }}';
                                    editForm.status = '{{ $g->status ?? 'aktif' }}';
                                    showEditModal = true;
                                " style="background-color: #f59e0b; color: white;" class="inline-block px-3 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90 transition" title="Edit Grup">
                                    ✏️
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ route('sr.grup.destroy', $g->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus grup ini secara permanen?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90 transition">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-500 italic">Belum ada grup yang dibuat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL EDIT GRUP (Muncul saat tombol Edit diklik) -->
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden" @click.away="showEditModal = false">
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="text-lg font-extrabold text-gray-800">✏️ Edit Data Grup</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-red-500 font-bold text-xl">&times;</button>
                </div>
                <div class="p-6">
                    <form :action="editForm.actionUrl" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Grup</label>
                            <input type="text" name="nama_grup" x-model="editForm.nama_grup" class="border-gray-300 rounded-lg text-sm p-2.5 w-full focus:ring-blue-500 focus:border-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Guru Mentor</label>
                            <select name="mentor_id" x-model="editForm.mentor_id" class="border-gray-300 rounded-lg text-sm p-2.5 w-full focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Pilih Guru Mentor --</option>
                                @foreach($teachers as $guru)
                                    <option value="{{ $guru->id }}">{{ $guru->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Mulai</label>
                                <input type="text" name="tahun_ajaran_mulai" x-model="editForm.tahun_ajaran_mulai" class="border-gray-300 rounded-lg text-sm p-2.5 w-full focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            
                            <!-- INPUT STATUS -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status Grup</label>
                                <select name="status" x-model="editForm.status" class="border-gray-300 rounded-lg text-sm p-2.5 w-full focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            
                            <!-- INPUT WARNA KUSTOM (MODAL EDIT) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Warna Grup</label>
                                <div class="flex items-center gap-2 border border-gray-300 rounded-lg p-1 bg-white shadow-sm w-full h-[42px] cursor-pointer" onclick="document.getElementById('edit_warna_grup').click()">
                                    <input type="color" id="edit_warna_grup" name="warna_grup" x-model="editForm.warna_grup" class="h-full w-full cursor-pointer border-0 rounded bg-transparent p-0 m-0">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                            <button type="button" @click="showEditModal = false" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-300 transition">Batal</button>
                            <button type="submit" style="background-color: #1e3a8a; color: white;" class="px-5 py-4 rounded-lg text-sm font-bold shadow-sm hover:opacity-90 transition">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- END MODAL EDIT GRUP -->

    </div>
</x-app-layout>