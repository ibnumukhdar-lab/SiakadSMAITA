@php
    /**
     * Nilai akhir satu jenis penilaian (Adab / Keasramaan).
     * @var array  $nilai      ['rata' => ?, 'predikat' => ?, 'jumlah_penilai' => int, 'pengisi' => [nama], 'ada_draft' => bool]
     * @var bool   $ringkas    tampilan kartu HP (lebih besar)
     * @var bool   $tanpaNama  sembunyikan nama pengisi (mis. di kartu sempit)
     */
    $ringkas = $ringkas ?? false;
    $tanpaNama = $tanpaNama ?? false;
    $pengisi = $nilai['pengisi'] ?? [];
@endphp

@if($nilai['rata'] === null)
    <span class="text-[13px] text-slate-300">—</span>
@else
    <div class="{{ $ringkas ? 'mt-1' : 'inline-flex flex-col items-center' }}">
        <div class="flex items-center gap-2 {{ $ringkas ? '' : 'justify-center' }}">
            <span class="text-[{{ $ringkas ? '15' : '13' }}px] font-bold text-slate-800">{{ $nilai['rata'] }}%</span>
            <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($nilai['predikat']) }}">
                {{ $nilai['predikat'] }}
            </span>
            {{-- Sejak 19 Sep 2026 status “draft/final” adalah status SESI (periode), bukan per lembar,
                 jadi keterangan draft per baris dihapus: statusnya tampil di halaman Sesi & Progres. --}}
        </div>
        @unless($tanpaNama)
            <div class="text-[10px] text-slate-400 {{ $ringkas ? '' : 'text-center' }} mt-0.5">
                {{ $nilai['jumlah_penilai'] }} penilai
                @if(count($pengisi) === 1) · {{ $pengisi[0] }} @endif
            </div>
        @endunless
    </div>
@endif
