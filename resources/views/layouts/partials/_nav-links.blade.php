{{-- Daftar menu navigasi — dipakai ulang di sidebar desktop & drawer mobile.
     Tanpa Alpine: seksi collapsible pakai <details>/<summary> native (robust di HP). --}}
@php
    $srOn    = request()->routeIs('sr.*');
    $asOn    = request()->routeIs('asrama.*');
    $beeOn   = request()->routeIs('bee.*');
    $arsipOn = request()->routeIs('arsip.*');
    $siswaOn = request()->routeIs('siswa.*');
    $kelasOn = request()->routeIs('kelas.*');
    $kelolaOn = request()->routeIs('kelola-akun.*');
    $adminOn = request()->routeIs('dashboard');
@endphp

@php
    $linkBase = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition';
    $linkAct  = 'bg-blue-50 text-blue-900 ring-1 ring-blue-100';
    $secSum   = 'nav-sum flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition';
    $secAct   = 'bg-blue-50 text-blue-900';
    $ico = 'w-6 text-center text-[16px] shrink-0';
@endphp

<div class="space-y-0.5">
    {{-- ===== UTAMA ===== --}}
    <div class="px-3 pt-1 pb-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Utama</div>
    <a href="{{ route('dashboard') }}" class="{{ $linkBase }} {{ $adminOn ? $linkAct : '' }}">
        <span class="{{ $ico }}">🏠</span> Dashboard
    </a>

    @can('buka-menu-arsip')
    <a href="{{ route('arsip.index') }}" class="{{ $linkBase }} {{ $arsipOn ? $linkAct : '' }}">
        <span class="{{ $ico }}">📁</span> Data E-Arsip
    </a>
    @endcan

    @can('buka-menu-siswa')
    <a href="{{ route('siswa.index') }}" class="{{ $linkBase }} {{ $siswaOn ? $linkAct : '' }}">
        <span class="{{ $ico }}">🎓</span> Data Siswa
    </a>
    <a href="{{ route('kelas.index') }}" class="{{ $linkBase }} {{ $kelasOn ? $linkAct : '' }}">
        <span class="{{ $ico }}">🏫</span> Kelola Kelas
    </a>
    @endcan

    {{-- ===== STUDENT ROOT ===== --}}
    <div class="pt-3">
        <div class="px-3 pb-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-500/90">🌱 Karakter</div>
        <details class="nav-sec" {{ $srOn ? 'open' : '' }}>
            <summary class="{{ $secSum }} {{ $srOn ? $secAct : '' }}">
                <span class="{{ $ico }}">🌱</span> Student Root
                <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="mt-0.5 space-y-0.5">
                <a href="{{ route('sr.poin.create') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📝</span> Input Poin Sikap
                </a>
                <a href="{{ route('sr.dashboard') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📊</span> Dashboard Karakter
                </a>
                @can('buka-menu-grup-binaan')
                <a href="{{ route('sr.mygroup') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">👥</span> Grup Binaan Saya
                </a>
                @endcan
                @can('buka-menu-master-student-root')
                <a href="{{ route('sr.grup.index') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">🏢</span> Manajemen Grup
                </a>
                <a href="{{ route('sr.kriteria.index') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">⚙️</span> Master Kriteria
                </a>
                <a href="{{ route('sr.display.setting') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📺</span> Display TV
                </a>
                @endcan
            </div>
        </details>
    </div>

    {{-- ===== ASRAMA ===== --}}
    @can('buka-menu-asrama')
    <div class="pt-3">
        <div class="px-3 pb-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-blue-500/90">🛏️ Asrama</div>
        <details class="nav-sec" {{ $asOn ? 'open' : '' }}>
            <summary class="{{ $secSum }} {{ $asOn ? $secAct : '' }}">
                <span class="{{ $ico }}">🛏️</span> Asrama
                <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="mt-0.5 space-y-0.5">
                <a href="{{ route('asrama.dashboard') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📊</span> Dashboard Asrama
                </a>
                <a href="{{ route('asrama.kamar.binaan') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">🏠</span> Kamar Binaan Saya
                </a>
                @can('buka-menu-manajemen-kamar')
                <a href="{{ route('asrama.kamar.index') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">🏢</span> Manajemen Kamar
                </a>
                @endcan
                <a href="{{ route('asrama.penilaian.hariIni') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📝</span> Inspeksi Hari Ini
                </a>
                <a href="{{ route('asrama.penilaian.index') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📚</span> Histori Inspeksi
                </a>
            </div>
        </details>
    </div>
    @endcan

    {{-- ===== BEE SMART ===== --}}
    <div class="pt-3">
        <div class="px-3 pb-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-amber-500/90">🐝 Bahasa</div>
        <details class="nav-sec" {{ $beeOn ? 'open' : '' }}>
            <summary class="{{ $secSum }} {{ $beeOn ? $secAct : '' }}">
                <span class="{{ $ico }}">🐝</span> BEE Smart
                <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="mt-0.5 space-y-0.5">
                <a href="{{ route('bee.index') }}" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📊</span> Dashboard Modul
                </a>
                <a href="{{ route('bee.classroom') }}" target="_blank" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">👨‍🏫</span> Mode Kelas (TV)
                    <span class="ms-auto text-[10px] font-bold text-slate-400">↗</span>
                </a>
                <a href="{{ route('bee.buku-saku') }}" target="_blank" class="flex items-center gap-3 ps-10 pe-3 py-2 rounded-xl text-[13px] font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition">
                    <span class="w-5 text-center text-[14px] shrink-0">📱</span> Buku Saku Siswa
                    <span class="ms-auto text-[10px] font-bold text-slate-400">↗</span>
                </a>
            </div>
        </details>
    </div>

    {{-- ===== ADMINISTRASI ===== --}}
    @role('Super Admin')
    <div class="pt-3">
        <div class="px-3 pb-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Administrasi</div>
        <a href="{{ route('kelola-akun.index') }}" class="{{ $linkBase }} {{ $kelolaOn ? $linkAct : '' }}">
            <span class="{{ $ico }}">🛡️</span> Kelola Akun
        </a>
    </div>
    @endrole
</div>
