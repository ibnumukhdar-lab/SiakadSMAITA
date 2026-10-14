@php
    /** Kerangka bersama modul Kulliyyat Diniyyah: judul, penunjuk periode, sub-menu. */
    $dk_judul   = $judul ?? 'Kulliyyat Diiniyyah Al-Arafah';
    $dk_ket     = $keterangan ?? '';
    $dk_tabAktif = $tab ?? '';
    // Halaman Periode mengirim DAFTAR periode pada variabel $periode —
    // ambil periode aktif dari model bila yang diterima bukan satu periode.
    $dk_periode = ($periode ?? null) instanceof \App\Models\DiniyahPeriode
        ? $periode
        : \App\Models\DiniyahPeriode::aktifSekarang();
    $dk_tabs = [
        ['mapel',   'Mata Pelajaran', 'diniyah.mapel',   'kelola-mapel-diniyah'],
        ['periode', 'Periode',        'diniyah.periode', 'kelola-periode-diniyah'],
        ['absensi', 'Absensi',        'diniyah.absensi', 'absensi-diniyah'],
        ['nilai',   'Nilai',          'diniyah.nilai',   'nilai-diniyah'],
        ['rapor',   'Rapor',          'diniyah.rapor',   null],
    ];
    $dk_pengguna = auth()->user();
@endphp

<div class="dk-kepala">
    <div class="dk-kepala-teks">
        <p class="dk-kicker">Kulliyyat Diiniyyah Al-Arafah</p>
        <h1 class="dk-judul">{{ $dk_judul }}</h1>
        @if($dk_ket)<p class="dk-lead">{{ $dk_ket }}</p>@endif
    </div>
    @if($dk_periode)
        <p class="dk-periode {{ $dk_periode->terbuka ? 'buka' : 'tutup' }}">
            <span>Periode</span>
            {{ $dk_periode->label() }} · {{ $dk_periode->terbuka ? 'terbuka' : 'tertutup' }}
        </p>
    @else
        <p class="dk-periode"><span>Periode</span> belum ada</p>
    @endif
</div>

<nav class="dk-tab">
    @foreach($dk_tabs as [$kunci, $label, $rute, $izin])
        @if(! $izin || ($dk_pengguna && $dk_pengguna->can($izin)))
            <a href="{{ route($rute) }}" class="{{ $dk_tabAktif === $kunci ? 'aktif' : '' }}">{{ $label }}</a>
        @endif
    @endforeach
</nav>

@if(session('success'))
    <div class="dk-pesan ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="dk-pesan galat">{{ session('error') }}</div>
@endif
@if(isset($errors) && $errors->any())
    <div class="dk-pesan galat">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif
