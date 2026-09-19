{{-- Daftar menu navigasi — dipakai ulang di sidebar desktop & drawer mobile.
     Tanpa Alpine: seksi collapsible pakai <details>/<summary> native (robust di HP).

     TATA RAPI (17 Sep 2026):
     - Kelompok menu dipisah garis halus (.nav-grup) dengan label seragam.
     - Ikon warna diseragamkan (.nav-ico: abu-abu tenang, berwarna hanya saat aktif/hover)
       dan hanya dipakai di menu induk — anak menu tanpa ikon supaya tidak ramai.
     - Tidak ada menu ganda: tiap tujuan hanya muncul sekali. --}}
@php
    $srOn    = request()->routeIs('sr.*') || request()->routeIs('project-sr.*');
    $asOn    = request()->routeIs('asrama.*');
    $beeOn   = request()->routeIs('bee.*');
    $arsipOn = request()->routeIs('arsip.*');
    $siswaOn = request()->routeIs('siswa.*');
    $nilaiOn = request()->routeIs('penilaian.*');
    $kelasOn = request()->routeIs('kelas.*');
    $tahunOn = request()->routeIs('tahun-ajaran.*');
    $kenaikanOn = request()->routeIs('kenaikan.*');
    $kelolaOn = request()->routeIs('kelola-akun.*');
    $adminOn = request()->routeIs('dashboard');

    // Gaya seragam
    $item    = 'nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition';
    $itemAct = 'nav-aktif bg-blue-50 text-blue-900 ring-1 ring-blue-100';
    $sum     = 'nav-sum flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition';
    $sumAct  = 'nav-aktif bg-blue-50 text-blue-900';
    $sub     = 'flex items-center ps-11 pe-3 py-2 rounded-lg text-[13px] font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition';
    $subAct  = 'bg-blue-50 text-blue-900 font-semibold';
    $ico     = 'nav-ico w-6 text-center text-[16px] shrink-0';
@endphp

<div>
    {{-- ===== UTAMA ===== --}}
    <div class="nav-grup">
        <div class="nav-grup-label">Utama</div>
        <a href="{{ route('dashboard') }}" class="{{ $item }} {{ $adminOn ? $itemAct : '' }}">
            <span class="{{ $ico }}">🏠</span> Dashboard
        </a>
    </div>

    {{-- ===== DATA & AKADEMIK ===== --}}
    @if(auth()->user()->can('buka-menu-siswa') || auth()->user()->can('buka-menu-arsip'))
        <div class="nav-grup">
            <div class="nav-grup-label">Data &amp; Akademik</div>

            @can('buka-menu-arsip')
                <a href="{{ route('arsip.index') }}" class="{{ $item }} {{ $arsipOn ? $itemAct : '' }}">
                    <span class="{{ $ico }}">📁</span> Data E-Arsip
                </a>
            @endcan

            @can('buka-menu-siswa')
                <a href="{{ route('siswa.index') }}" class="{{ $item }} {{ $siswaOn ? $itemAct : '' }}">
                    <span class="{{ $ico }}">🎓</span> Data Siswa
                </a>
                <a href="{{ route('kelas.index') }}" class="{{ $item }} {{ $kelasOn ? $itemAct : '' }}">
                    <span class="{{ $ico }}">🏫</span> Kelola Kelas
                </a>
                <a href="{{ route('tahun-ajaran.index') }}" class="{{ $item }} {{ $tahunOn ? $itemAct : '' }}">
                    <span class="{{ $ico }}">📅</span> Tahun Ajaran
                </a>
                <a href="{{ route('kenaikan.index') }}" class="{{ $item }} {{ $kenaikanOn ? $itemAct : '' }}">
                    <span class="{{ $ico }}">⬆️</span> Kenaikan Kelas
                </a>
            @endcan
        </div>
    @endif

    {{-- ===== KARAKTER / STUDENT ROOT ===== --}}
    <div class="nav-grup">
        <div class="nav-grup-label">Karakter</div>
        <details class="nav-sec" {{ $srOn ? 'open' : '' }}>
            <summary class="{{ $sum }} {{ $srOn ? $sumAct : '' }}">
                <span class="{{ $ico }}">🌱</span> Student Root
                <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="mt-0.5 space-y-0.5">
                <a href="{{ route('sr.poin.create') }}" class="{{ $sub }}">Input Poin Sikap</a>
                <a href="{{ route('sr.dashboard') }}" class="{{ $sub }}">Dashboard Karakter</a>

                @can('buka-menu-grup-binaan')
                    <a href="{{ route('sr.mygroup') }}" class="{{ $sub }}">Grup Binaan Saya</a>
                @endcan

                @if(auth()->user()->can('buka-menu-grup-binaan') || auth()->user()->can('buka-menu-master-student-root'))
                    <a href="{{ route('sr.rapot') }}" class="{{ $sub }}">Rapot Student Root</a>
                @endif

                @can('buka-menu-project-sr')
                    <a href="{{ route('project-sr.index') }}" class="{{ $sub }}">Project Student Root</a>
                    <a href="{{ route('project-sr.rekap') }}" class="{{ $sub }}">Rekap Project</a>
                    <a href="{{ route('project-sr.portofolio.index') }}" class="{{ $sub }}">Penyusunan Portofolio</a>
                @endcan

                @can('kelola-master-project-sr')
                    <a href="{{ route('project-sr.master') }}" class="{{ $sub }}">Tahap &amp; Bobot Project</a>
                @endcan

                @can('buka-menu-master-student-root')
                    <a href="{{ route('sr.grup.index') }}" class="{{ $sub }}">Manajemen Grup</a>
                    <a href="{{ route('sr.kriteria.index') }}" class="{{ $sub }}">Master Kriteria</a>
                    <a href="{{ route('sr.display.setting') }}" class="{{ $sub }}">Display TV</a>
                @endcan
            </div>
        </details>
    </div>

    {{-- ===== PENILAIAN: RAPOR ADAB & KEASRAMaan (alur sederhana 19 Sep 2026) ===== --}}
    @can('buka-menu-penilaian')
        <div class="nav-grup">
            <div class="nav-grup-label">Penilaian</div>
            <details class="nav-sec" {{ $nilaiOn ? 'open' : '' }}>
                <summary class="{{ $sum }} {{ $nilaiOn ? $sumAct : '' }}">
                    <span class="{{ $ico }}">📋</span> Rapor Adab &amp; Keasramaan
                    <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </summary>
                <div class="mt-0.5 space-y-0.5">
                    @if(auth()->user()->can('nilai-adab') || auth()->user()->can('nilai-keasramaan'))
                        <a href="{{ route('penilaian.isi.rapor') }}" class="{{ $sub }}">Isi Rapor</a>
                    @endif

                    <a href="{{ route('penilaian.cetak') }}" class="{{ $sub }}">Cetak Rapor</a>

                    @can('kelola-sesi-rapor')
                        <a href="{{ route('penilaian.sesi-rapor') }}" class="{{ $sub }}">Sesi &amp; Progres</a>
                    @endcan

                    @can('kelola-master-penilaian')
                        <a href="{{ route('penilaian.master') }}" class="{{ $sub }}">Pertanyaan &amp; Ambang</a>
                    @endcan
                </div>
            </details>
        </div>
    @endcan

    {{-- ===== ASRAMA ===== --}}
    @can('buka-menu-asrama')
        <div class="nav-grup">
            <div class="nav-grup-label">Asrama</div>
            <details class="nav-sec" {{ $asOn ? 'open' : '' }}>
                <summary class="{{ $sum }} {{ $asOn ? $sumAct : '' }}">
                    <span class="{{ $ico }}">🛏️</span> Asrama
                    <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </summary>
                <div class="mt-0.5 space-y-0.5">
                    <a href="{{ route('asrama.dashboard') }}" class="{{ $sub }}">Dashboard Asrama</a>
                    <a href="{{ route('asrama.kamar.binaan') }}" class="{{ $sub }}">Kamar Binaan Saya</a>

                    @can('buka-menu-manajemen-kamar')
                        <a href="{{ route('asrama.kamar.index') }}" class="{{ $sub }}">Manajemen Kamar</a>
                    @endcan

                    <a href="{{ route('asrama.penilaian.hariIni') }}" class="{{ $sub }}">Inspeksi Hari Ini</a>
                    <a href="{{ route('asrama.penilaian.index') }}" class="{{ $sub }}">Histori Inspeksi</a>
                    <a href="{{ route('asrama.peringkat') }}" class="{{ $sub }}">Peringkat Bulanan</a>
                    <a href="{{ route('asrama.absensi.index') }}" class="{{ $sub }}">Absensi Malam</a>
                    <a href="{{ route('asrama.izin.index') }}" class="{{ $sub }}">Izin Pulang / Keluar</a>
                    <a href="{{ route('asrama.analitik') }}" class="{{ $sub }}">Analitik Kamar</a>

                    @can('buka-menu-manajemen-kamar')
                        <a href="{{ route('asrama.kamar.riwayat') }}" class="{{ $sub }}">Riwayat Mutasi</a>
                    @endcan
                </div>
            </details>
        </div>
    @endcan

    {{-- ===== BAHASA ===== --}}
    <div class="nav-grup">
        <div class="nav-grup-label">Bahasa</div>
        <details class="nav-sec" {{ $beeOn ? 'open' : '' }}>
            <summary class="{{ $sum }} {{ $beeOn ? $sumAct : '' }}">
                <span class="{{ $ico }}">🐝</span> BEE Smart
                <svg class="chev ms-auto h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </summary>
            <div class="mt-0.5 space-y-0.5">
                <a href="{{ route('bee.index') }}" class="{{ $sub }}">Dashboard Modul</a>
                <a href="{{ route('bee.classroom') }}" target="_blank" class="{{ $sub }}">
                    Mode Kelas (TV) <span class="ms-auto text-[10px] font-bold text-slate-400">↗</span>
                </a>
                <a href="{{ route('bee.buku-saku') }}" target="_blank" class="{{ $sub }}">
                    Buku Saku Siswa <span class="ms-auto text-[10px] font-bold text-slate-400">↗</span>
                </a>
            </div>
        </details>
    </div>

    {{-- ===== ADMINISTRASI ===== --}}
    @role('Super Admin')
        <div class="nav-grup">
            <div class="nav-grup-label">Administrasi</div>
            <a href="{{ route('kelola-akun.index') }}" class="{{ $item }} {{ $kelolaOn ? $itemAct : '' }}">
                <span class="{{ $ico }}">🛡️</span> Kelola Akun
            </a>
        </div>
    @endrole
</div>
