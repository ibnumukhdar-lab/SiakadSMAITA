<x-app-layout>
    <!-- Tambahan Alpine.js untuk fitur Loading, Fetch & Bulk Action -->
    <div class="py-8" x-data="{
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
            <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full mx-4 border border-slate-200">
                <!-- Ikon Spinner -->
                <svg class="animate-spin h-14 w-14 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-extrabold text-slate-800 mb-1">Sedang Mensinkronisasi...</h3>
                <p class="text-sm text-slate-500">Membaca modul sistem. Halaman akan dimuat ulang otomatis.</p>
            </div>
        </div>

        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🛡️ Kelola Akun</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Pusat kendali jabatan pengguna &amp; matriks akses modul website</p>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-5 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-3 mb-5">
                <button @click="tab = 'matrix'" :style="tab === 'matrix' ? 'background-color: #1e293b; color: white;' : 'background-color: white; color: #334155;'" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg text-sm font-bold shadow-sm transition border border-slate-200">✅ Matriks Akses Modul</button>
                <button @click="tab = 'users'" :style="tab === 'users' ? 'background-color: #1e293b; color: white;' : 'background-color: white; color: #334155;'" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg text-sm font-bold shadow-sm transition border border-slate-200">👥 Pengaturan Jabatan Akun</button>
            </div>

            <!-- TAB 1: MATRIKS MODUL -->
            <div x-show="tab === 'matrix'" class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 overflow-x-auto relative">

                <div class="flex flex-col sm:flex-row sm:items-start justify-between mb-5 gap-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight mb-1">Tabel Checklist Akses Modul</h3>
                        <p class="text-sm text-slate-500">Centang kotak di bawah ini untuk memberi izin akses. Jika dibiarkan kosong, jabatan tersebut otomatis tertutup aksesnya ke modul terkait.</p>
                    </div>

                    <!-- TOMBOL SINKRONISASI SETUP MODUL -->
                    <button @click="prosesSetupModul()" type="button" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sinkronisasi Sistem
                    </button>
                </div>

                <form action="{{ route('kelola-akun.updateMatrix') }}" method="POST">
                    @csrf @method('PUT')
                    <table class="w-full text-left border-collapse min-w-[600px] mb-6">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Daftar Modul Website</th>
                                @foreach($roles as $role)
                                    <th class="px-4 py-3.5 text-[11px] font-bold text-center uppercase tracking-wider text-slate-400 border-l border-slate-200">{{ $role->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $p)
                            @php
                                $namaTampil = ucwords(str_replace(['buka-menu-', '-'], ['', ' '], $p->name));
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-800 text-sm">Modul {{ $namaTampil }}</div>
                                    <div class="text-xs text-slate-400 font-mono mt-1">Sistem ID: {{ $p->name }}</div>
                                </td>

                                @foreach($roles as $role)
                                <td class="px-4 py-3.5 text-center border-l border-slate-100 cursor-pointer hover:bg-blue-50 transition">
                                    <input type="checkbox"
                                           name="matrix[{{ $role->id }}][]"
                                           value="{{ $p->name }}"
                                           {{ $role->hasPermissionTo($p->name) ? 'checked' : '' }}
                                           class="w-6 h-6 text-blue-900 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shadow-sm">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition w-full sm:w-auto whitespace-nowrap">
                        💾 Simpan Matriks Checklist
                    </button>
                </form>

                <!-- Form Tambah Modul Ekstra (Disempurnakan) -->
                <div class="mt-10 pt-5 border-t border-slate-100 bg-slate-50/70 p-4 sm:p-5 rounded-xl">
                    <p class="text-sm text-slate-800 mb-1 font-bold">➕ Punya Modul Baru di Luar Sistem?</p>
                    <p class="text-xs text-slate-500 mb-4">Anda cukup mengetik nama modulnya saja (misal: <strong>perpustakaan</strong> atau <strong>nilai ujian</strong>). Sistem otomatis akan menyesuaikan format kodenya.</p>
                    <form action="{{ route('kelola-akun.storePermission') }}" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-lg">
                        @csrf
                        <input type="text" name="name" placeholder="Ketik nama modul baru di sini..." class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition flex-1" required>
                        <button type="submit" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Tambahkan Manual</button>
                    </form>
                </div>
            </div>

            <!-- TAB 2: DAFTAR AKUN -->
            <div x-show="tab === 'users'" style="display:none;" class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-5 gap-4">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Daftar Akun Pengguna</h3>
                        <p class="text-sm text-slate-500">Tentukan jabatan masing-masing pengguna di sini. Anda bisa memberikan <b>lebih dari satu jabatan</b> pada satu akun.</p>
                    </div>

                    <!-- FORM BULK ACTION -->
                    <form action="{{ route('kelola-akun.bulkUpdateUser') }}" method="POST" class="flex flex-col sm:flex-row gap-2 w-full md:w-auto" x-show="selectedUsers.length > 0" x-transition x-cloak>
                        @csrf
                        @method('PUT')
                        <template x-for="userId in selectedUsers" :key="userId">
                            <input type="hidden" name="user_ids[]" :value="userId">
                        </template>
                        <div class="flex items-center h-10 gap-2 bg-blue-50 px-3 rounded-lg border border-blue-100">
                            <span class="text-xs font-bold text-blue-800 whitespace-nowrap"><span x-text="selectedUsers.length"></span> Terpilih</span>
                        </div>
                        <select name="bulk_role" class="w-full h-10 sm:w-64 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 font-semibold focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            <option value="">-- Timpa Status Massal --</option>
                            <option value="Cabut Jabatan">❌ Cabut Semua Jabatan</option>
                            <option value="Super Admin">🛡️ Super Admin</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                            Terapkan Massal
                        </button>
                    </form>
                </div>

                {{-- Form NIPA (dipasang di luar tabel pakai atribut form=, supaya tidak bersarang di form jabatan) --}}
                @foreach($users as $user)
                    <form id="nipa-{{ $user->id }}" method="POST" action="{{ route('kelola-akun.updateNipa', $user->id) }}" class="hidden">
                        @csrf
                        @method('PUT')
                    </form>
                @endforeach

                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200">
                            <th class="px-4 py-3.5 w-10 text-center">
                                <input type="checkbox" @change="toggleAll" class="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shadow-sm">
                            </th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 w-1/4">Nama &amp; Email</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">NIPA</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Pilih Jabatan (Centang yang sesuai)</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition" :class="selectedUsers.includes('{{ $user->id }}') ? 'bg-blue-50' : ''">
                            <td class="px-4 py-3.5 text-center align-top">
                                <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" class="user-checkbox w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shadow-sm mt-1.5">
                            </td>
                            <form action="{{ route('kelola-akun.updateUser', $user->id) }}" method="POST">
                                @csrf @method('PUT')
                                <td class="px-4 py-3.5 align-top">
                                    <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</div>
                                </td>

                                {{-- NIPA: boleh diisi pemiliknya sendiri di halaman Profil, boleh juga oleh admin di sini --}}
                                <td class="px-4 py-3.5 align-top">
                                    <div class="flex items-center gap-1.5">
                                        <input type="text" name="nipa" form="nipa-{{ $user->id }}" value="{{ $user->nipa }}"
                                            inputmode="numeric" autocomplete="off" placeholder="YMA. 0000 000" oninput="rapikanNipa(this)"
                                            class="w-36 h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] font-semibold tracking-wide text-slate-700 placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                                        <button type="submit" form="nipa-{{ $user->id }}" title="Simpan NIPA"
                                            class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12px] font-semibold shadow-sm transition whitespace-nowrap">Simpan</button>
                                    </div>
                                </td>

                                <!-- MULTI-ROLE CHECKBOXES -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                                        <!-- Checkbox Super Admin (Khusus) -->
                                        <label class="inline-flex items-center cursor-pointer hover:bg-blue-50 px-2 py-1 rounded-lg transition">
                                            <input type="checkbox" name="roles[]" value="Super Admin" {{ $user->hasRole('Super Admin') ? 'checked' : '' }} class="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-500 shadow-sm cursor-pointer">
                                            <span class="ml-2 text-sm font-bold text-slate-800">🛡️ Super Admin</span>
                                        </label>

                                        <!-- Checkbox Dinamis dari Database -->
                                        @foreach($roles as $role)
                                            <label class="inline-flex items-center cursor-pointer hover:bg-slate-50 px-2 py-1 rounded-lg transition">
                                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'checked' : '' }} class="w-4 h-4 text-blue-900 rounded border-slate-300 focus:ring-blue-500 shadow-sm cursor-pointer">
                                                <span class="ml-2 text-sm font-semibold text-slate-600">{{ $role->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 text-[11px] text-slate-400 italic font-medium">* Kosongkan semua centang jika ingin mencabut seluruh jabatannya.</div>
                                </td>

                                <td class="px-4 py-3.5 text-center align-top whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="submit" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">Simpan</button>

                                        <!-- TOMBOL IMPERSONATE DITAMBAHKAN DI SINI -->
                                        @if($user->id !== auth()->id() && !$user->hasRole('Super Admin'))
                                            <a href="{{ route('impersonate', $user->id) }}" class="inline-flex items-center h-9 px-3 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 text-xs font-bold shadow-sm transition whitespace-nowrap" onclick="return confirm('Anda akan menyamar dan login sebagai {{ $user->name }}. Lanjutkan?')">
                                                🕵️‍♂️ Login
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <script>
                    // Ketik 7 angka -> langsung diformat "YMA. 0000 000"
                    function rapikanNipa(el) {
                        const angka = (el.value || '').replace(/\D/g, '').slice(0, 7);
                        if (angka.length === 0) { el.value = ''; return; }
                        let hasil = 'YMA. ' + angka.slice(0, 4);
                        if (angka.length > 4) { hasil += ' ' + angka.slice(4); }
                        el.value = hasil;
                    }
                </script>
            </div>

        </div>
    </div>
</x-app-layout>
