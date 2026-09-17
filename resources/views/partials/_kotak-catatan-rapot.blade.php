{{--
    Kotak isian catatan rapor (Adab & Keasramaan / Student Root) — 18 Sep 2026.
    Dipakai di halaman Penilaian → santri (per periode) dan Student Root → riwayat poin (per semester).

    Parameter:
      $jenis   : 'adab' | 'student_root'
      $siswa   : model Siswa
      $catatan : model CatatanRaport|null (sudah tersimpan)
      $kunci   : array field kunci periode, mis. ['penilaian_periode_id' => 1]
      $boleh   : bool — pengguna boleh menulis? (kalau tidak, hanya ditampilkan)
      $judul   : judul kotak (opsional)
--}}
@php
    $catatanIsi = $catatan ?? null;
    $bolehTulis = $boleh ?? false;
    $maksCatatan = \App\Models\CatatanRaport::MAKS;
@endphp

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
        <h4 class="text-[13.5px] font-bold text-slate-800">{{ $judul ?? 'Catatan Musyrif / Pembina' }}</h4>
        <span class="text-[11px] text-slate-400">tercetak di rapor · maks. {{ $maksCatatan }} huruf</span>
    </div>

    @if($bolehTulis)
        <form action="{{ route('catatan.simpan') }}" method="POST">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
            @foreach(($kunci ?? []) as $namaKunci => $nilaiKunci)
                <input type="hidden" name="{{ $namaKunci }}" value="{{ $nilaiKunci }}">
            @endforeach

            <textarea name="isi" rows="3" maxlength="{{ $maksCatatan }}"
                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-[13px] leading-relaxed text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition"
                      placeholder="Tulis catatan untuk santri ini pada periode tersebut…">{{ old('isi', $catatanIsi->isi ?? '') }}</textarea>

            <div class="flex flex-wrap items-center gap-2 mt-2">
                <button type="submit" class="h-9 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan catatan</button>
                @if($catatanIsi)
                    <span class="text-[11.5px] text-slate-400">
                        tersimpan {{ optional($catatanIsi->updated_at)->translatedFormat('d M Y H:i') }}@if($catatanIsi->penulis) · {{ $catatanIsi->penulis->name }}@endif
                    </span>
                @else
                    <span class="text-[11.5px] text-slate-400">belum ada catatan — kalau dikosongkan, rapor kembali bergaris untuk tulis tangan</span>
                @endif
            </div>
        </form>

        @if($catatanIsi)
            <form action="{{ route('catatan.hapus', $catatanIsi->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Hapus catatan rapor ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-600 text-[12px] font-semibold hover:bg-slate-50 transition">Hapus catatan</button>
            </form>
        @endif
    @else
        @if($catatanIsi)
            <div class="text-[13px] leading-relaxed text-slate-700 whitespace-pre-line">{{ $catatanIsi->isi }}</div>
            <div class="text-[11.5px] text-slate-400 mt-1.5">
                — {{ $catatanIsi->penulis->name ?? 'Musyrif' }}@if($catatanIsi->updated_at) · {{ $catatanIsi->updated_at->translatedFormat('d M Y') }}@endif
            </div>
        @else
            <div class="text-[13px] text-slate-400">Belum ada catatan untuk periode ini.</div>
        @endif
    @endif
</div>
