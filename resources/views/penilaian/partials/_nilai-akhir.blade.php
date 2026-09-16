@php
    /** @var array $nilai  ['rata' => ?, 'predikat' => ?, 'jumlah_penilai' => int, 'ada_draft' => bool] */
    $ringkas = $ringkas ?? false;
@endphp

@if($nilai['rata'] === null)
    <span class="text-[13px] text-slate-300">—</span>
@else
    <div class="{{ $ringkas ? 'mt-1 flex items-center gap-2' : 'inline-flex items-center justify-center gap-2' }}">
        <span class="text-[{{ $ringkas ? '14' : '13' }}px] font-bold text-slate-800">{{ $nilai['rata'] }}%</span>
        <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($nilai['predikat']) }}">
            {{ $nilai['predikat'] }}
        </span>
        @if($nilai['jumlah_penilai'] === 1)
            <span class="text-[10px] font-semibold text-slate-400" title="Baru dinilai satu penilai">1 penilai</span>
        @endif
        @if(! empty($nilai['ada_draft']))
            <span class="text-[10px] font-bold text-amber-500" title="Masih ada lembar berstatus draft">draft</span>
        @endif
    </div>
@endif
