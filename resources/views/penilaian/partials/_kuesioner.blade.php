{{-- Partial kuesioner: dipakai form per siswa & form per kamar.
     Butuh: $kriteria (koleksi PenilaianKriteria), $jawaban (array kriteria_id => skor) --}}
@php
    $skala = [1 => 'Sangat kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'];
@endphp

<div class="space-y-3">
    @foreach($kriteria as $i => $k)
        @php $terpilih = (int) ($jawaban[$k->id] ?? 0); @endphp
        <div class="bg-white border border-slate-200 rounded-xl p-3 sm:p-3.5">
            <div class="flex items-start gap-2 mb-2">
                <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500">{{ $i + 1 }}</span>
                <div class="min-w-0">
                    <div class="text-[13px] font-semibold text-slate-800 leading-snug">{{ $k->pertanyaan }}</div>
                    @if($k->keterangan)
                        <div class="text-[11.5px] text-slate-400 mt-0.5">{{ $k->keterangan }}</div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-5 gap-1.5">
                @for($n = 1; $n <= 5; $n++)
                    <label class="cursor-pointer">
                        <input type="radio" name="skor[{{ $k->id }}]" value="{{ $n }}" class="peer sr-only" @checked($terpilih === $n) required>
                        <span class="flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-bold text-slate-600 transition peer-checked:border-blue-900 peer-checked:bg-blue-900 peer-checked:text-white hover:border-blue-400">{{ $n }}</span>
                        <span class="sr-only">{{ $skala[$n] }}</span>
                    </label>
                @endfor
            </div>
        </div>
    @endforeach
</div>

<div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-400 mt-2.5">
    <span><strong class="text-slate-500">1</strong> Sangat kurang</span>
    <span><strong class="text-slate-500">2</strong> Kurang</span>
    <span><strong class="text-slate-500">3</strong> Cukup</span>
    <span><strong class="text-slate-500">4</strong> Baik</span>
    <span><strong class="text-slate-500">5</strong> Sangat baik</span>
</div>
