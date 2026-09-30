@php
    $s = $siswa;
    $inisial = collect(explode(' ', trim((string) $s->nama_lengkap)))->filter()->take(2)->map(fn ($k) => mb_strtoupper(mb_substr($k, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor {{ $s->nama_lengkap }} — Portal Orang Tua</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">

<header class="bg-gray-800 text-white">
    <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <div>
            <p class="text-xs font-black uppercase tracking-widest opacity-80">Portal Orang Tua</p>
            <p class="text-sm font-black">SMA IT Arafah</p>
        </div>
        <form method="POST" action="{{ route('ortu.keluar') }}">
            @csrf
            <button type="submit" class="px-3 py-2 rounded-lg bg-white text-gray-800 text-xs font-black">Keluar</button>
        </form>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-5 space-y-4">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-lg font-black text-gray-500">{{ $inisial }}</div>
            <div class="flex-1">
                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Rapor ananda</p>
                <p class="text-lg font-black text-gray-800">{{ $s->nama_lengkap }}</p>
                <p class="text-xs font-semibold text-gray-600 mt-0.5">Kelas {{ $s->kelas ?: '-' }} · NISN {{ $s->nisn ?: '-' }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-600 font-semibold mt-3 leading-relaxed">
            Rapor di bawah ini adalah <b>rapor resmi yang sama</b> dengan yang dicetak sekolah — bukan ringkasan terpisah.
            Halaman ini hanya menampilkan <b>rapor ananda sendiri</b> (sesuai NISN yang dipakai masuk) dan <b>hanya untuk dilihat</b>: menyalin/mencetak rapor adalah kewenangan sekolah.
        </p>
    </div>

    {{-- Rapor Student Root --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-sm font-black text-gray-800">Rapor Student Root</p>
                <p class="text-xs font-semibold text-gray-600 mt-0.5">Poin karakter (perilaku positif &amp; negatif), tren bulanan, dan nilai project per tahap.</p>
                @if(! $adaPoinSr)
                    <p class="text-xs font-bold text-amber-700 mt-1">Belum ada catatan poin untuk ananda pada semester ini.</p>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach($semesterList as $kode => $label)
                <a href="{{ route('ortu.rapor.sr', ['semester' => $kode]) }}" target="_blank"
                   class="px-4 py-2 rounded-lg {{ $kode === $semesterAktif ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-800 border border-gray-300' }} text-xs font-black">
                    {{ $label }}{{ $kode === $semesterAktif ? ' (berjalan)' : '' }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Rapot Adab & Keasramaan --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm font-black text-gray-800">Rapot Adab &amp; Keasramaan</p>
        <p class="text-xs font-semibold text-gray-600 mt-0.5">
            Nilai Adab dan Keasramaan per aspek, catatan musyrif/musyrifah, dan kolom tanda tangan.
        </p>
        @if(! $adaNilaiAdab)
            <p class="text-xs font-bold text-amber-700 mt-1">Belum ada penilaian Adab &amp; Keasramaan yang masuk untuk ananda.</p>
        @endif
        @if($periode)
            <p class="text-xs font-semibold text-gray-500 mt-1">Periode terakhir: {{ $periode->nama }}@if($periode->tahun_ajaran) · {{ $periode->tahun_ajaran }}@endif</p>
        @endif
        <a href="{{ route('ortu.rapor.adab') }}" target="_blank" class="inline-block mt-3 px-4 py-2 rounded-lg bg-gray-800 text-white text-xs font-black">Buka Rapot Adab &amp; Keasramaan</a>
    </div>

    {{-- Rapor Diniyah (menyusul) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <p class="text-sm font-black text-gray-800">Rapor Diniyah</p>
        <p class="text-xs font-semibold text-gray-600 mt-0.5">Kulliyyat Diiniyyah Al-Arafah.</p>
        <p class="text-xs font-bold text-gray-500 mt-1">Belum tersedia — menyusul setelah modul penilaian diniyah dipasang di SIAKAD.</p>
    </div>

    <div class="pb-6">
        <a href="{{ route('ortu.dasbor') }}" class="text-xs font-black text-gray-700">&larr; Kembali ke dasbor perkembangan ananda</a>
    </div>
</main>
</body>
</html>
