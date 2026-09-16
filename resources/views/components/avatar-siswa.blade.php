@props(['siswa', 'ukuran' => 'kecil'])

@php
    $dimensi = $ukuran === 'besar' ? 'w-11 h-11 text-base' : 'w-8 h-8 text-[12px]';
    $awal = mb_strtoupper(mb_substr(trim((string) $siswa->nama_lengkap), 0, 1));
    $inisialWarna = [
        'A' => 'bg-blue-100 text-blue-800', 'B' => 'bg-emerald-100 text-emerald-800',
        'C' => 'bg-amber-100 text-amber-800', 'D' => 'bg-rose-100 text-rose-800',
        'E' => 'bg-indigo-100 text-indigo-800', 'F' => 'bg-teal-100 text-teal-800',
        'G' => 'bg-orange-100 text-orange-800', 'H' => 'bg-cyan-100 text-cyan-800',
        'I' => 'bg-violet-100 text-violet-800', 'J' => 'bg-lime-100 text-lime-800',
        'K' => 'bg-pink-100 text-pink-800', 'L' => 'bg-sky-100 text-sky-800',
        'M' => 'bg-fuchsia-100 text-fuchsia-800', 'N' => 'bg-green-100 text-green-800',
        'O' => 'bg-yellow-100 text-yellow-800', 'P' => 'bg-purple-100 text-purple-800',
        'Q' => 'bg-slate-100 text-slate-700', 'R' => 'bg-red-100 text-red-800',
        'S' => 'bg-blue-100 text-blue-800', 'T' => 'bg-emerald-100 text-emerald-800',
        'U' => 'bg-amber-100 text-amber-800', 'V' => 'bg-indigo-100 text-indigo-800',
        'W' => 'bg-teal-100 text-teal-800', 'X' => 'bg-slate-100 text-slate-700',
        'Y' => 'bg-orange-100 text-orange-800', 'Z' => 'bg-cyan-100 text-cyan-800',
    ];
    $kelasWarna = $inisialWarna[$awal] ?? 'bg-slate-100 text-slate-700';
@endphp

@if($siswa->foto)
    <img src="{{ url('berkas/' . $siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}" loading="lazy"
         class="{{ $dimensi }} rounded-full object-cover border border-slate-200 shrink-0">
@else
    <span class="{{ $dimensi }} {{ $kelasWarna }} rounded-full border border-slate-200 shrink-0 inline-flex items-center justify-center font-bold"
          title="Belum ada foto">{{ $awal !== '' ? $awal : '?' }}</span>
@endif
