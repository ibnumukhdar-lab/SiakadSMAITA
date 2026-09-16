@php /** @var array|null $entri */ @endphp
@if($entri && $entri['persentase'] !== null)
    <div class="inline-flex flex-col items-center">
        <span class="text-[13px] font-bold text-slate-800">{{ $entri['persentase'] }}%</span>
        <span class="text-[10px] font-semibold {{ ($entri['status'] ?? 'draft') === 'final' ? 'text-green-600' : 'text-amber-500' }}">
            {{ ($entri['status'] ?? 'draft') === 'final' ? 'final' : 'draft' }}
        </span>
    </div>
@else
    <span class="text-[13px] text-slate-300">—</span>
@endif
