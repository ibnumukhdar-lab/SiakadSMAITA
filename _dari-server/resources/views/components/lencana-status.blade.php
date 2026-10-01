@props(['status'])

@php
    $teks = (string) $status;
    $gaya = match ($teks) {
        'Aktif' => 'bg-green-50 text-green-700 border-green-200',
        'Alumni' => 'bg-sky-50 text-sky-700 border-sky-200',
        'Pindah' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-red-50 text-red-700 border-red-200',
    };
@endphp

<span class="text-[11px] font-bold px-2 py-0.5 rounded-full border whitespace-nowrap {{ $gaya }}">{{ $teks !== '' ? $teks : '-' }}</span>
