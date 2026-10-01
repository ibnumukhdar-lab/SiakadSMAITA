{{--
    Penanda + isian catatan rapor untuk SATU BARIS daftar santri (18 Sep 2026).
    Dipakai di halaman daftar rapor (Student Root & Adab/Keasramaan) tepat di sebelah tombol cetak.

    Penanda: "● Belum ada catatan · tulis" (kuning) atau "✓ Catatan terisi · ubah" (hijau) — diklik
    membuka isian (pakai <details> bawaan HTML, tanpa JS, aman di HP).

    Parameter:
      $jenis   : 'adab' | 'student_root'
      $siswaId : int
      $catatan : model CatatanRaport|null
      $kunci   : array field kunci periode, mis. ['penilaian_periode_id' => 1]
      $boleh   : bool boleh menulis?
      $keterangan : teks periode singkat, mis. "Semester 1 2026/2027"
--}}
@php
    $nota = $catatan ?? null;
    $bolehTulis = $boleh ?? false;
    $infoPeriode = $keterangan ?? '';
@endphp

<details class="w-full">
    <summary class="list-none cursor-pointer select-none inline-flex items-center gap-1.5 rounded-lg border px-2 py-1 text-[11.5px] font-bold transition
                    {{ $nota ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
        @if($nota)
            ✓ Catatan terisi
            <span class="font-normal opacity-70">· ubah</span>
        @else
            ● Belum ada catatan
            @if($bolehTulis)<span class="font-normal opacity-70">· tulis</span>@endif
        @endif
    </summary>

    <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50/70 p-2.5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                {{ $jenis === 'adab' ? 'Catatan Musyrif / Pembina' : 'Catatan Mentor' }}
            </span>
            <span class="text-[11px] text-slate-400">
                {{ $infoPeriode }}@if($bolehTulis) · maks. {{ \App\Models\CatatanRaport::MAKS }} huruf @endif
            </span>
        </div>

        @if($nota)
            <div class="text-[12.5px] leading-relaxed text-slate-700 whitespace-pre-line">{{ $nota->isi }}</div>
            <div class="text-[11px] text-slate-400 mt-1">
                — {{ $nota->penulis->name ?? 'Musyrif' }}@if($nota->updated_at) · {{ $nota->updated_at->translatedFormat('d M Y') }}@endif
            </div>
        @else
            <div class="text-[12.5px] text-slate-400">Belum diisi. Kalau dibiarkan kosong, rapor dicetak bergaris untuk ditulis tangan.</div>
        @endif

        @if($bolehTulis)
            <form action="{{ route('catatan.simpan') }}" method="POST" class="mt-2">
                @csrf
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <input type="hidden" name="siswa_id" value="{{ $siswaId }}">
                @foreach(($kunci ?? []) as $namaKunci => $nilaiKunci)
                    <input type="hidden" name="{{ $namaKunci }}" value="{{ $nilaiKunci }}">
                @endforeach

                <textarea name="isi" rows="2" maxlength="{{ \App\Models\CatatanRaport::MAKS }}"
                          placeholder="Tulis catatan untuk {{ $infoPeriode }}…"
                          class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] leading-relaxed text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">{{ $nota->isi ?? '' }}</textarea>

                <div class="flex flex-wrap items-center gap-2 mt-1.5">
                    <button type="submit" class="h-8 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12px] font-semibold transition">Simpan catatan</button>
                    @if(! $nota)
                        <span class="text-[11px] text-slate-400">isi dikosongkan = tidak ada catatan</span>
                    @endif
                </div>
            </form>

            @if($nota)
                <form action="{{ route('catatan.hapus', $nota->id) }}" method="POST" class="mt-1.5" onsubmit="return confirm('Hapus catatan rapor ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="h-7 px-2.5 rounded-lg border border-slate-300 bg-white text-slate-600 text-[11.5px] font-semibold hover:bg-slate-50 transition">Hapus catatan</button>
                </form>
            @endif
        @elseif(! $nota)
            <div class="text-[11px] text-slate-400 mt-1">Hanya musyrif divisi / mentor grup santri ini yang bisa mengisi.</div>
        @endif
    </div>
</details>
