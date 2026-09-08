<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            🛡️ Pusat Kendali Akses Website
        </h2>
    </x-slot>

    <!-- Tambahan Alpine.js untuk fitur Loading, Fetch & Bulk Action -->
    <div class="py-12" x-data="{ 
        tab: 'matrix', 
        isLoading: false,
        selectedUsers: [],
        toggleAll(event) {
            if (event.target.checked) {
                // Ambil semua value dari input checkbox yang ada di dalam tabel user
                const checkboxes = document.querySelectorAll('.user-checkbox');
                this.selectedUsers = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selectedUsers = [];
            }
        },
        prosesSetupModul() {
            this.isLoading = true;
            fetch('{{ url('/setup-modul') }}')
                .then(response => {
                    if(response.ok) {
                        // Jika berhasil, langsung refresh halaman otomatis
                        window.location.reload();
                    } else {
                        alert('Terjadi kesalahan saat sinkronisasi.');
                        this.isLoading = false;
                    }
                })
                .catch(error => {
                    alert('Gagal menghubungi server.');
                    this.isLoading = false;
                });
        }
    }">
        
        <!-- POP-UP WAITING (LOADING) -->
        <div x-show="isLoading" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm">
            <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full mx-4 border border-gray-200">
                <!-- Ikon Spinner -->
                <svg class="animate-spin h-14 w-14 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-extrabold text-gray-900 mb-1">Sedang Mensinkronisasi...</h3>
                <p class="text-sm text-gray-500">Membaca modul sistem. Halaman akan dimuat ulang otomatis.</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <button @click="tab = 'matrix'" :style="tab === 'matrix' ? 'background-color: #1e293b; color: white;' : 'background-color: white; color: #334155;'" class="px-5 py-3 rounded-lg font-bold shadow-sm transition border border-gray-200">✅ Matriks Akses Modul</button>
                <button @click="tab = 'users'" :style="tab === 'users' ? 'background-color: #1e293b; color: white;' : 'background-color: white; color: #334155;'" class="px-5 py-3 rounded-lg font-bold shadow-sm transition border border-gray-200">👥 Pengaturan Jabatan Akun</button>
            </div>

            <!-- TAB 1: MATRIKS MODUL -->
            <div x-show="tab === 'matrix'" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-x-auto relative">
                
                <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold mb-1 text-gray-800">Tabel Checklist Akses Modul</h3>
                        <p class="text-sm text-gray-500">Centang kotak di bawah ini untuk memberi izin akses. Jika dibiarkan kosong, jabatan tersebut otomatis tertutup aksesnya ke modul terkait.</p>
                    </div>
                    
                    <!-- TOMBOL SINKRONISASI SETUP MODUL -->
                    <button @click="prosesSetupModul()" type="button" style="background-color: #f59e0b; color: white;" class="px-5 py-2.5 rounded-lg text-xs font-bold shadow-md hover:opacity-90 flex items-center gap-2 whitespace-nowrap transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sinkronisasi Sistem
                    </button>
                </div>
                
                <form action="{{ route('kelola-akun.updateMatrix') }}" method="POST">
                    @csrf @method('PUT')
                    <table class="w-full text-left border-collapse min-w-[600px] mb-6">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-300">
                                <th class="p-4 text-sm font-bold text-gray-700 uppercase">Daftar Modul Website</th>
                                @foreach($roles as $role)
                                    <th class="p-4 text-sm font-bold text-center text-gray-700 uppercase border-l border-gray-200">{{ $role->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $p)
                            @php
                                $namaTampil = ucwords(str_replace(['buka-menu-', '-'], ['', ' '], $p->name));
                            @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-4">
                                    <div class="font-extrabold text-gray-800 text-base">Modul {{ $namaTampil }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-1">Sistem ID: {{ $p->name }}</div>
                                </td>
                                
                                @foreach($roles as $role)
                                <td class="p-4 text-center border-l border-gray-100 cursor-pointer hover:bg-blue-50 transition">
                                    <input type="checkbox" 
                                           name="matrix[{{ $role->id }}][]" 
                                           value="{{ $p->name }}" 
                                           {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }} 
                                           class="w-6 h-6 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer shadow-sm">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" style="background-color: #059669; color: white;" class="px-6 py-3 rounded-lg text-sm font-bold shadow-md w-full sm:w-auto hover:opacity-90 transition">
                        💾 Simpan Matriks Checklist
                    </button>
                </form>

                <!-- Form Tambah Modul Ekstra (Disempurnakan) -->
                <div class="mt-12 pt-6 border-t border-gray-100 bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 mb-2 font-bold">➕ Punya Modul Baru di Luar Sistem?</p>
                    <p class="text-xs text-gray-500 mb-4">Anda cukup mengetik nama modulnya saja (misal: <strong>perpustakaan</strong> atau <strong>nilai ujian</strong>). Sistem otomatis akan menyesuaikan format kodenya.</p>
                    <form action="{{ route('kelola-akun.storePermission') }}" method="POST" class="flex gap-3 max-w-lg">
                        @csrf
                        <input type="text" name="name" placeholder="Ketik nama modul baru di sini..." class="border-gray-300 rounded-lg text-sm w-full p-2.5 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <button type="submit" style="background-color: #1e293b; color: white;" class="px-5 py-2.5 rounded-lg text-xs font-bold whitespace-nowrap shadow-sm hover:opacity-90">Tambahkan Manual</button>
                    </form>
                </div>
            </div>

            <!-- TAB 2: DAFTAR AKUN -->
            <div x-show="tab === 'users'" style="display:none;" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-gray-800">Daftar Akun Pengguna</h3>
                        <p class="text-sm text-gray-500">Tentukan jabatan masing-masing pengguna di sini. Anda bisa memberikan <b>lebih dari satu jabatan</b> pada satu akun.</p>
                    </div>

                    <!-- FORM BULK ACTION -->
                    <form action="{{ route('kelola-akun.bulkUpdateUser') }}" method="POST" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto" x-show="selectedUsers.length > 0" x-transition x-cloak>
                        @csrf
                        @method('PUT')
                        <template x-for="userId in selectedUsers" :key="userId">
                            <input type="hidden" name="user_ids[]" :value="userId">
                        </template>
                        <div class="flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg border border-blue-100">
                            <span class="text-xs font-bold text-blue-800 whitespace-nowrap"><span x-text="selectedUsers.length"></span> Terpilih</span>
                        </div>
                        <select name="bulk_role" class="border-gray-300 rounded text-sm w-full sm:w-auto font-semibold text-gray-700 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">-- Timpa Status Massal --</option>
                            <option value="Cabut Jabatan">❌ Cabut Semua Jabatan</option>
                            <option value="Super Admin">🛡️ Super Admin</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" style="background-color: #1e3a8a; color: white;" class="px-4 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90 transition whitespace-nowrap">
                            Terapkan Massal
                        </button>
                    </form>
                </div>
                
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-200">
                            <th class="p-3 w-10 text-center">
                                <input type="checkbox" @change="toggleAll" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer shadow-sm">
                            </th>
                            <th class="p-3 text-sm font-semibold w-1/4">Nama & Email</th>
                            <th class="p-3 text-sm font-semibold">Pilih Jabatan (Centang yang sesuai)</th>
                            <th class="p-3 text-sm font-semibold w-auto text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b border-gray-100 hover:bg-gray-50" :class="selectedUsers.includes('{{ $user->id }}') ? 'bg-blue-50' : ''">
                            <td class="p-3 text-center align-top">
                                <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" class="user-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer shadow-sm mt-1">
                            </td>
                            <form action="{{ route('kelola-akun.updateUser', $user->id) }}" method="POST">
                                @csrf @method('PUT')
                                <td class="p-3 align-top">
                                    <div class="font-bold text-sky-700">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </td>
                                
                                <!-- MULTI-ROLE CHECKBOXES -->
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-x-6 gap-y-3">
                                        <!-- Checkbox Super Admin (Khusus) -->
                                        <label class="inline-flex items-center cursor-pointer hover:bg-blue-50 px-2 py-1 rounded transition">
                                            <input type="checkbox" name="roles[]" value="Super Admin" {{ $user->hasRole('Super Admin') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 shadow-sm cursor-pointer">
                                            <span class="ml-2 text-sm font-bold text-gray-800">🛡️ Super Admin</span>
                                        </label>
                                        
                                        <!-- Checkbox Dinamis dari Database -->
                                        @foreach($roles as $role)
                                            <label class="inline-flex items-center cursor-pointer hover:bg-gray-100 px-2 py-1 rounded transition">
                                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 shadow-sm cursor-pointer">
                                                <span class="ml-2 text-sm font-semibold text-gray-700">{{ $role->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 text-[11px] text-gray-400 italic font-medium">* Kosongkan semua centang jika ingin mencabut seluruh jabatannya.</div>
                                </td>

                                <td class="p-3 text-center align-top whitespace-nowrap">
                                    <button type="submit" style="background-color: #4f46e5; color: white;" class="px-4 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90 transition">Simpan</button>
                                    
                                    <!-- TOMBOL IMPERSONATE DITAMBAHKAN DI SINI -->
                                    @if($user->id !== auth()->id() && !$user->hasRole('Super Admin'))
                                        <a href="{{ route('impersonate', $user->id) }}" class="inline-flex items-center px-3 py-2 ml-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold rounded shadow-sm transition" onclick="return confirm('Anda akan menyamar dan login sebagai {{ $user->name }}. Lanjutkan?')">
                                            🕵️‍♂️ Login
                                        </a>
                                    @endif
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>