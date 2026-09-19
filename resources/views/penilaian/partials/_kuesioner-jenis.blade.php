{{--
    Partial kuesioner SATU bagian (dipakai formulir gabungan Adab + Keasramaan).
    Butuh: $jenis (adab|keasramaan), $judul, $kriteria (koleksi), $jawaban (array kriteria_id => skor),
           $terisi (jumlah pertanyaan yang sudah terisi), $terbuka (bool).
--}}
@php
    $skala = [1 => 'Sangat kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat baik'];
@endphp

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-3">
    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
        <h4 class="text-[13.5px] font-bold text-slate-800">{{ $judul }}</h4>
        @if($kriteria->isEmpty())
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">belum ada pertanyaan</span>
        @else
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $terisi >= $kriteria->count() ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                terisi {{ $terisi }} dari {{ $kriteria->count() }}
            </span>
        @endif
    </div>

    <div class="p-3 sm:p-4">
        @forelse($kriteria as $i => $k)
            @php $terpilih = (int) ($jawaban[$k->id] ?? 0); @endphp
            <div class="border border-slate-200 rounded-xl p-3 sm:p-3.5 {{ $loop->last ? '' : 'mb-2.5' }}">
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
                            <input type="radio" name="skor[{{ $jenis }}][{{ $k->id }}]" value="{{ $n }}" class="peer sr-only" @checked($terpilih === $n) @required($terbuka)>
                            <span class="flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-bold text-slate-600 transition peer-checked:border-blue-900 peer-checked:bg-blue-900 peer-checked:text-white hover:border-blue-400">{{ $n }}</span>
                            <span class="sr-only">{{ $skala[$n] }}</span>
                        </label>
                    @endfor
                </div>
            </div>
        @empty
            <div class="text-[13px] text-slate-400">Belum ada pertanyaan aktif untuk bagian ini.</div>
        @endforelse
    </div>
</div>
