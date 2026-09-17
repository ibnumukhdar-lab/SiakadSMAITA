@php
    $pengaturan = \App\Models\Pengaturan::first();
    $nama = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $logoUrl = ($pengaturan && $pengaturan->logo_path) ? url('berkas/' . $pengaturan->logo_path) : null;
@endphp
<style>
    /* ===== Navigasi: drawer & seksi tanpa JavaScript (robust HP) ===== */
    summary.nav-sum { list-style: none; }
    summary.nav-sum::-webkit-details-marker { display: none; }
    summary.nav-user { list-style: none; }
    summary.nav-user::-webkit-details-marker { display: none; }
    .chev { transition: transform .24s cubic-bezier(.22,.61,.36,1); }
    .nav-sec[open] > summary .chev { transform: rotate(180deg); }

    /* ===== Kelompok menu: pemisah garis halus + label seragam ===== */
    .nav-grup { margin-top: 14px; padding-top: 14px; border-top: 1px solid #eef2f7; }
    .nav-grup:first-of-type { margin-top: 0; padding-top: 0; border-top: 0; }
    .nav-grup-label {
        padding: 0 12px 7px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    /* ===== Ikon: warna diseragamkan (abu tenang), berwarna saat aktif/hover ===== */
    .nav-ico { filter: grayscale(1); opacity: .75; transition: filter .24s ease, opacity .24s ease, transform .24s ease; }
    .nav-item:hover .nav-ico,
    .nav-item.nav-aktif .nav-ico,
    .nav-sum:hover .nav-ico,
    .nav-sec[open] > .nav-sum .nav-ico {
        filter: none;
        opacity: 1;
        transform: translateY(-1px);
    }
    .nav-item.nav-aktif .nav-ico { transform: none; }

    /* ===== Drawer HP: keluar-masuk halus (bukan lompat) ===== */
    .drawer-panel {
        transform: translateX(-102%);
        opacity: 0;
        pointer-events: none;
        will-change: transform, opacity;
        transition: transform .42s cubic-bezier(.22,.61,.36,1), opacity .34s ease;
    }
    #navDrawer:checked ~ .drawer-panel {
        transform: translateX(0);
        opacity: 1;
        pointer-events: auto;
    }
    .drawer-mask {
        opacity: 0;
        pointer-events: none;
        transition: opacity .38s ease;
    }
    #navDrawer:checked ~ .drawer-mask {
        opacity: 1;
        pointer-events: auto;
    }
    /* Saat drawer terbuka: halaman di belakang tidak ikut bergulir */
    body:has(#navDrawer:checked) { overflow: hidden; }

    .nav-user[open] .user-pop { display: block; }
    .user-pop { display: none; }

    @media (prefers-reduced-motion: reduce) {
        .drawer-panel, .drawer-mask, .nav-ico, .chev { transition: none; }
    }
</style>

{{-- ============ MOBILE: top bar + drawer (checkbox CSS, tanpa Alpine) ============ --}}
<div class="lg:hidden">
    {{-- Checkbox pemicu drawer (hidden) --}}
    <input type="checkbox" id="navDrawer" class="sr-only" aria-hidden="true">

    {{-- Top bar --}}
    <div class="bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800 shadow-md sticky top-0 z-40">
        <div class="px-3 py-2.5 flex items-center justify-between gap-2">
            {{-- Kiri: hamburger + branding --}}
            <div class="flex items-center gap-1.5 min-w-0">
                <label for="navDrawer" class="inline-flex items-center justify-center p-2 -ml-1 rounded-lg text-white hover:bg-white/10 transition shrink-0 cursor-pointer" aria-label="Buka menu" role="button">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </label>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="h-8 w-8 rounded bg-white/90 p-0.5 object-contain shrink-0" alt="Logo">
                    @else
                        <div class="h-8 w-8 bg-white text-blue-900 rounded-lg flex items-center justify-center font-black text-sm shrink-0">{{ mb_substr($nama, 0, 1) }}</div>
                    @endif
                    <div class="leading-tight min-w-0">
                        <div class="font-black text-white text-[13px] uppercase tracking-tight truncate">{{ $nama }}</div>
                        <div class="text-[10px] text-blue-200 font-bold tracking-widest uppercase truncate">{{ $motto }}</div>
                    </div>
                </a>
            </div>

            {{-- Kanan: avatar profil --}}
            <a href="{{ route('profile.edit') }}" class="h-9 w-9 rounded-full bg-white text-blue-900 flex items-center justify-center font-black text-sm shadow shrink-0" title="{{ Auth::user()->name }}">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </a>
        </div>
    </div>

    {{-- Backdrop (label: klik = tutup) --}}
    <label for="navDrawer" class="drawer-mask fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 cursor-pointer" aria-hidden="true"></label>

    {{-- Panel drawer --}}
    <div class="drawer-panel fixed inset-y-0 left-0 w-[290px] max-w-[85vw] bg-white shadow-2xl z-50 flex flex-col">
        <div class="bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800 px-4 py-4 flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5 min-w-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="h-9 w-9 rounded bg-white/90 p-0.5 shrink-0" alt="Logo">
                @else
                    <div class="h-9 w-9 bg-white text-blue-900 rounded-lg flex items-center justify-center font-black shrink-0">{{ mb_substr($nama, 0, 1) }}</div>
                @endif
                <div class="leading-tight min-w-0">
                    <div class="font-black text-white text-[12px] uppercase tracking-tight truncate">{{ $nama }}</div>
                    <div class="text-[9px] text-blue-200 font-bold tracking-widest uppercase truncate">{{ $motto }}</div>
                </div>
            </div>
            <label for="navDrawer" class="text-white/90 hover:text-white p-1 rounded-lg hover:bg-white/10 text-xl leading-none cursor-pointer" role="button" aria-label="Tutup">&times;</label>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            @include('layouts.partials._nav-links')
        </nav>

        <div class="border-t border-gray-200 px-4 py-3 space-y-2 bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-blue-900 text-white flex items-center justify-center font-black text-sm shrink-0">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
                <div class="min-w-0 leading-tight">
                    <div class="text-[13px] font-bold text-slate-800 truncate">{{ Auth::user()->name }}</div>
                    <div class="text-[11px] text-slate-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profile.edit') }}" class="flex-1 text-center text-[12px] font-bold text-blue-900 bg-white border border-gray-200 rounded-lg py-1.5 hover:bg-blue-50 transition">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full text-[12px] font-bold text-red-600 bg-white border border-gray-200 rounded-lg py-1.5 hover:bg-red-50 transition">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============ SIDEBAR DESKTOP (lg ke atas) ============ --}}
<aside class="hidden lg:flex lg:flex-col lg:sticky lg:top-0 lg:h-screen lg:self-start w-72 shrink-0 bg-white border-r border-gray-200">
    {{-- Brand --}}
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" class="h-11 w-auto max-w-[52px] rounded object-contain bg-white/90 p-1 shrink-0" alt="Logo">
        @else
            <div class="h-11 w-11 bg-white text-blue-900 rounded-xl flex items-center justify-center font-black text-lg shrink-0">{{ mb_substr($nama, 0, 1) }}</div>
        @endif
        <div class="leading-tight min-w-0">
            <div class="font-black text-white text-[15px] uppercase tracking-tight truncate">{{ $nama }}</div>
            <div class="text-[10px] text-blue-200 font-bold tracking-[0.18em] uppercase truncate">{{ $motto }}</div>
        </div>
    </a>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5">
        @include('layouts.partials._nav-links')
    </nav>

    {{-- Footer profil --}}
    <div class="border-t border-gray-200 px-4 py-3 flex items-center gap-3">
        <div class="h-9 w-9 rounded-full bg-blue-900 text-white flex items-center justify-center font-black text-sm shrink-0">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
        <div class="min-w-0 flex-1 leading-tight">
            <div class="text-[13px] font-bold text-slate-800 truncate">{{ Auth::user()->name }}</div>
            <div class="text-[11px] text-slate-400 truncate">{{ Auth::user()->email }}</div>
        </div>
        <details class="nav-user relative">
            <summary class="nav-user text-slate-500 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 cursor-pointer flex" aria-label="Menu akun">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </summary>
            <div class="user-pop absolute bottom-full right-0 mb-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl py-1.5 z-50">
                @can('buka-menu-pengaturan')
                <a href="{{ route('pengaturan.edit') }}" class="block px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">⚙️ Pengaturan Lembaga</a>
                @endcan
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50">👤 Profile Saya</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-[13px] font-bold text-red-600 hover:bg-red-50">🚪 Log Out</button>
                </form>
            </div>
        </details>
    </div>
</aside>
