@php
    $pengaturan = \App\Models\Pengaturan::first();
@endphp

<!-- ================= BANNER PERINGATAN IMPERSONATE ================= -->
@if(session()->has('impersonate_by'))
    <div class="bg-red-600 text-white text-center py-2 px-4 font-bold shadow-md relative z-50 flex flex-col sm:flex-row justify-center items-center gap-3">
        <span>🕵️‍♂️ PERHATIAN: Anda sedang menyamar sebagai <strong>{{ Auth::user()->name }}</strong>.</span>
        <a href="{{ route('impersonate.leave') }}" class="bg-white text-red-600 px-4 py-1 rounded-full text-sm hover:bg-gray-100 transition shadow">
            Kembali ke Akun Admin
        </a>
    </div>
@endif
<!-- =============================================================== -->

<nav x-data="{ open: false }" class="bg-white shadow-md sticky top-0 z-40">
    
    <!-- ================= BARIS 1: BRANDING & PROFIL (TOP BAR) ================= -->
    <div class="bg-slate-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">
                
                <!-- KIRI: Branding Sekolah -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 hover:opacity-80 transition duration-150">
                        @if($pengaturan && $pengaturan->logo_path)
                            <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                            <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="h-10 w-auto rounded object-contain drop-shadow-sm" style="max-height: 45px;">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-700 to-blue-900 text-white rounded-lg flex items-center justify-center font-bold text-xl shadow-md shrink-0">
                                {{ substr($pengaturan->nama_sekolah ?? 'S', 0, 1) }}
                            </div>
                        @endif

                        <div class="flex flex-col justify-center">
                            <span class="font-black text-gray-900 text-base md:text-lg leading-none tracking-tight whitespace-nowrap uppercase">{{ $pengaturan->nama_sekolah ?? 'SMA IT ARAFAH' }}</span>
                            <span class="hidden sm:block text-[11px] text-blue-600 font-bold tracking-widest mt-1 uppercase">{{ $pengaturan->motto ?? 'Cerdas & Beradab' }}</span>
                        </div>
                    </a>
                </div>

                <!-- KANAN: Settings Dropdown (Desktop) -->
                <div class="hidden sm:flex sm:items-center sm:ms-6 shrink-0">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-bold rounded-full text-gray-700 bg-white hover:text-blue-700 hover:bg-blue-50 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                                <div class="hidden md:block mr-1">👤 {{ Auth::user()->name }}</div>
                                <div class="block md:hidden text-lg">👤</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('buka-menu-pengaturan')
                            <x-dropdown-link :href="route('pengaturan.edit')" class="text-blue-600 font-bold border-b border-gray-100">
                                ⚙️ {{ __('Pengaturan Lembaga') }}
                            </x-dropdown-link>
                            @endcan
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile Saya') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    <span class="text-red-600 font-bold">{{ __('Log Out') }}</span>
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger (Hanya untuk Mobile/HP) -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ================= BARIS 2: MENU NAVIGASI UTAMA (MENGGUNAKAN CSS MURNI) ================= -->
    <div class="custom-nav-bar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="custom-nav-container">
                
                <div class="custom-nav-item">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="whitespace-nowrap h-full">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
                
                @can('buka-menu-arsip')
                <div class="custom-nav-item">
                    <x-nav-link :href="route('arsip.index')" :active="request()->routeIs('arsip.*')" class="whitespace-nowrap h-full">
                        {{ __('Data E-Arsip') }}
                    </x-nav-link>
                </div>
                @endcan

                @can('buka-menu-siswa')
                <div class="custom-nav-item">
                    <x-nav-link :href="route('siswa.index')" :active="request()->routeIs('siswa.*')" class="whitespace-nowrap h-full">
                        {{ __('Data Siswa') }}
                    </x-nav-link>
                </div>
                @endcan

                <!-- MENU STUDENT ROOT -->
                <div class="custom-nav-item">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-600 hover:text-green-700 hover:border-green-300 focus:outline-none focus:text-green-700 focus:border-green-300 transition duration-150 ease-in-out h-full whitespace-nowrap">
                                🌱 Student Root
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('sr.poin.create')">📝 Input Poin Sikap</x-dropdown-link>
                            <x-dropdown-link :href="route('sr.dashboard')">📊 Dashboard Karakter</x-dropdown-link>
                            @can('buka-menu-grup-binaan')
                            <x-dropdown-link :href="route('sr.mygroup')" class="text-green-700 font-extrabold border-t border-gray-100 bg-green-50">👥 Grup Binaan Saya</x-dropdown-link>
                            @endcan
                            @can('buka-menu-master-student-root')
                            <div class="border-t border-gray-100"></div>
                            <x-dropdown-link :href="route('sr.grup.index')">🏢 Manajemen Grup</x-dropdown-link>
                            <x-dropdown-link :href="route('sr.kriteria.index')">⚙️ Master Kriteria</x-dropdown-link>
                            <x-dropdown-link :href="route('sr.display.setting')">📺 Pengaturan Display TV</x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- MENU ASRAMA (tampil hanya utk pemegang buka-menu-asrama) -->
                @can('buka-menu-asrama')
                <div class="custom-nav-item">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-600 hover:text-blue-700 hover:border-blue-300 focus:outline-none focus:text-blue-700 focus:border-blue-300 transition duration-150 ease-in-out h-full whitespace-nowrap">
                                🛏️ Asrama
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[10px] font-black text-blue-600 bg-blue-50 border-b border-gray-100 uppercase tracking-widest">Modul Asrama</div>
                            <x-dropdown-link :href="route('asrama.dashboard')" class="font-bold border-b border-gray-100">📊 Dashboard Asrama</x-dropdown-link>
                            <x-dropdown-link :href="route('asrama.kamar.binaan')" class="text-blue-700 font-extrabold bg-blue-50 border-b border-gray-100">🏠 Kamar Binaan Saya</x-dropdown-link>
                            @can('buka-menu-manajemen-kamar')
                            <x-dropdown-link :href="route('asrama.kamar.index')">🏢 Manajemen Kamar</x-dropdown-link>
                            @endcan
                            <x-dropdown-link :href="route('asrama.penilaian.hariIni')">📝 Inspeksi Hari Ini</x-dropdown-link>
                            <x-dropdown-link :href="route('asrama.penilaian.index')">📚 Histori Inspeksi</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
                @endcan

                <!-- MENU BEE SMART -->
                <div class="custom-nav-item">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-600 hover:text-yellow-600 hover:border-yellow-400 focus:outline-none focus:text-yellow-600 focus:border-yellow-400 transition duration-150 ease-in-out h-full whitespace-nowrap {{ request()->routeIs('bee.*') ? 'border-yellow-400 text-yellow-600' : '' }}">
                                🐝 BEE Smart
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-2 text-[10px] font-black text-yellow-600 bg-yellow-50 border-b border-gray-100 uppercase tracking-widest">Bahasa & Gamifikasi</div>
                            <x-dropdown-link :href="route('bee.index')" class="font-bold">📊 Dashboard Modul</x-dropdown-link>
                            <x-dropdown-link :href="route('bee.classroom')" target="_blank" class="font-bold text-blue-600">👨‍🏫 Mode Kelas (TV)</x-dropdown-link>
                            <x-dropdown-link :href="route('bee.buku-saku')" target="_blank" class="font-bold text-emerald-600">📱 Buku Saku Siswa</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
                
                @role('Super Admin')
                <div class="custom-nav-item">
                    <x-nav-link :href="route('kelola-akun.index')" :active="request()->routeIs('kelola-akun.*')" class="whitespace-nowrap font-bold text-indigo-600 h-full">
                        🛡️ Kelola Akun
                    </x-nav-link>
                </div>
                @endrole

            </div>
        </div>
    </div>

    <!-- ================= RESPONSIVE MENU (MOBILE/HP TETAP MENGGUNAKAN BAWAAN) ================= -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white shadow-lg absolute w-full z-50 border-b border-gray-200">
        <div class="pt-2 pb-3 space-y-1 max-h-[70vh] overflow-y-auto">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @can('buka-menu-arsip')
            <x-responsive-nav-link :href="route('arsip.index')" :active="request()->routeIs('arsip.*')">
                {{ __('Data E-Arsip') }}
            </x-responsive-nav-link>
            @endcan
            
            @can('buka-menu-siswa')
            <x-responsive-nav-link :href="route('siswa.index')" :active="request()->routeIs('siswa.*')">
                {{ __('Data Siswa') }}
            </x-responsive-nav-link>
            @endcan

            <div class="pt-4 pb-2 border-t border-gray-100 bg-green-50/40">
                <div class="px-4 text-[10px] font-black text-green-600 uppercase tracking-widest mb-1">🌱 Student Root</div>
                <x-responsive-nav-link :href="route('sr.poin.create')">📝 Input Poin Sikap</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sr.dashboard')">📊 Dashboard Karakter</x-responsive-nav-link>
                @can('buka-menu-grup-binaan')
                <x-responsive-nav-link :href="route('sr.mygroup')" class="text-green-700 font-bold bg-green-100 border-y border-green-200">👥 Grup Binaan Saya</x-responsive-nav-link>
                @endcan
                @can('buka-menu-master-student-root')
                <x-responsive-nav-link :href="route('sr.grup.index')">🏢 Manajemen Grup</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sr.kriteria.index')">⚙️ Master Kriteria</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sr.display.setting')">📺 Pengaturan Display TV</x-responsive-nav-link>
                @endcan
            </div>

            @can('buka-menu-asrama')
            <div class="pt-4 pb-2 border-t border-gray-100 bg-blue-50/40">
                <div class="px-4 text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">🛏️ Modul Asrama</div>
                <x-responsive-nav-link :href="route('asrama.dashboard')" class="font-bold">📊 Dashboard Asrama</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asrama.kamar.binaan')" class="text-blue-700 font-bold bg-blue-100 border-y border-blue-200">🏠 Kamar Binaan Saya</x-responsive-nav-link>
                @can('buka-menu-manajemen-kamar')
                <x-responsive-nav-link :href="route('asrama.kamar.index')">🏢 Manajemen Kamar</x-responsive-nav-link>
                @endcan
                <x-responsive-nav-link :href="route('asrama.penilaian.hariIni')">📝 Inspeksi Hari Ini</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('asrama.penilaian.index')">📚 Histori Inspeksi</x-responsive-nav-link>
            </div>
            @endcan

            <div class="pt-4 pb-2 border-t border-gray-100 bg-yellow-50/40">
                <div class="px-4 text-[10px] font-black text-yellow-600 uppercase tracking-widest mb-1">🐝 Modul BEE Smart</div>
                <x-responsive-nav-link :href="route('bee.index')" :active="request()->routeIs('bee.*')" class="font-bold border-l-4 border-yellow-500 text-yellow-700 bg-yellow-100">
                    📊 Dashboard Modul
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bee.classroom')" target="_blank" class="font-bold text-blue-600">
                    👨‍🏫 Mode Kelas (TV)
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bee.buku-saku')" target="_blank" class="font-bold text-emerald-600">
                    📱 Buku Saku Siswa
                </x-responsive-nav-link>
            </div>
            
            @role('Super Admin')
            <div class="border-t border-gray-100 mt-2"></div>
            <x-responsive-nav-link :href="route('kelola-akun.index')" :active="request()->routeIs('kelola-akun.*')" class="font-bold text-indigo-700">
                🛡️ {{ __('Kelola Akun') }}
            </x-responsive-nav-link>
            @endrole
        </div>

        <div class="pt-4 pb-4 border-t border-gray-200 bg-gray-50">
            <div class="px-4 flex items-center gap-3">
                <div class="bg-white border border-gray-300 shadow-sm w-10 h-10 rounded-full flex items-center justify-center font-bold text-gray-700">👤</div>
                <div>
                    <div class="font-bold text-base text-gray-900">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-4 space-y-1">
                @can('buka-menu-pengaturan')
                <x-responsive-nav-link :href="route('pengaturan.edit')" :active="request()->routeIs('pengaturan.*')" class="text-blue-600 font-bold">
                    ⚙️ {{ __('Pengaturan Lembaga') }}
                </x-responsive-nav-link>
                @endcan
                
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile Saya') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        <span class="text-red-600 font-bold">{{ __('Log Out') }}</span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- INJEKSI CSS MURNI ANTI-GAGAL UNTUK BARIS KE-2 -->
<style>
    .custom-nav-bar {
        display: none; /* Disembunyikan di layar HP, karena digantikan menu hamburger */
        background-color: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }
    
    @media (min-width: 640px) {
        .custom-nav-bar {
            display: block !important;
        }
        .custom-nav-container {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 1.5rem !important; /* Jarak antar menu */
            min-height: 55px !important; /* Pakai min-height, bukan height mati */
            flex-wrap: wrap !important; /* Jika menu kepanjangan, otomatis turun ke baris baru dengan rapi */
        }
        
        .custom-nav-item {
            display: flex;
            align-items: center;
            height: 55px; /* Menjaga tinggi area klik menu */
        }
    }
</style>