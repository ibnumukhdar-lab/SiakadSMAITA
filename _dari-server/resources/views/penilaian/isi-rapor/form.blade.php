<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            @php
                $bolehCatatan = \App\Models\CatatanRaport::bolehTulisAdab(auth()->user(), $siswa);
                $namaBerikutnya = $berikutnya ? optional(\App\Models\Siswa::find($berikutnya))->nama_lengkap : null;
                $terisiPerJenis = [];
                foreach ($kriteria as $jenisK => $daftar) {
                    $terisiPerJenis[$jenisK] = $daftar->filter(fn ($k) => isset($jawaban[$jenisK][$k->id]))->count();
                }
            @endphp

            <div class="mb-3">
                <a href="{{ $kamarId ? route('penilaian.isi.kamar', $kamarId) : route('penilaian.isi.rapor') }}"
                   class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">
                    ← Kembali ke {{ $kamarId ? 'kamar ' . $kamar : 'daftar kamar' }}
                </a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">Isi Rapor — satu santri</h3>
                <p class="text-[13px] text-slate-500 mt-0.5">
                    @if($periode) {{ $periode->nama }} @endif
                    @if($kamar && $jumlahAnggota > 0) · anak {{ (int) $urutan + 1 }} dari {{ $jumlahAnggota }} di kamar {{ $kamar }} @endif
                </p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <div class="font-bold mb-1">Ada pertanyaan yang belum dijawab:</div>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Identitas santri --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3 flex items-center gap-3">
                <x-avatar-siswa :siswa="$siswa" ukuran="besar" />
                <div class="min-w-0 flex-1">
                    <div class="text-[14.5px] font-bold text-slate-900 truncate">{{ $siswa->nama_lengkap }}</div>
                    <div class="text-[12px] text-slate-500 truncate">
                        NISN {{ $siswa->nisn ?: '-' }} · Kelas {{ $siswa->kelas ?: '-' }}@if($kamar) · Kamar {{ $kamar }}@endif
                    </div>
                </div>
                @php $sudahLengkap = ($terisiPerJenis['adab'] ?? 0) >= $kriteria['adab']->count() && ($terisiPerJenis['keasramaan'] ?? 0) >= $kriteria['keasramaan']->count() && $kriteria['adab']->count() > 0; @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10.5px] font-bold {{ $sudahLengkap ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $sudahLengkap ? 'lengkap' : 'belum lengkap' }}
                </span>
            </div>

            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 mb-3 text-[12px] text-slate-600 flex flex-wrap items-center justify-between gap-2">
                <span><strong>Skala 1–5</strong> — 1 Sangat kurang · 2 Kurang · 3 Cukup · 4 Baik · 5 Sangat baik</span>
                <span class="text-slate-400">{{ $kriteria['adab']->count() + $kriteria['keasramaan']->count() }} pertanyaan</span>
            </div>

            @unless($terbuka)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    Sesi isi rapor sedang <strong>tertutup</strong> — formulir ini hanya bisa dilihat, tidak bisa disimpan.
                </div>
            @endunless

            <form action="{{ route('penilaian.isi.simpan', $siswa->id) }}" method="POST">
                @csrf
                @if($berikutnya)
                    <input type="hidden" name="berikutnya" value="{{ $berikutnya }}">
                @endif

                @include('penilaian.partials._kuesioner-jenis', [
                    'jenis' => 'adab',
                    'judul' => 'A. Penilaian Adab',
                    'kriteria' => $kriteria['adab'],
                    'jawaban' => $jawaban['adab'] ?? [],
                    'terisi' => $terisiPerJenis['adab'] ?? 0,
                ])

                @include('penilaian.partials._kuesioner-jenis', [
                    'jenis' => 'keasramaan',
                    'judul' => 'B. Penilaian Keasramaan',
                    'kriteria' => $kriteria['keasramaan'],
                    'jawaban' => $jawaban['keasramaan'] ?? [],
                    'terisi' => $terisiPerJenis['keasramaan'] ?? 0,
                ])

                {{-- Catatan musyrif untuk rapor --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Catatan musyrif untuk rapor</h4>
                        <span class="text-[11px] text-slate-400">opsional · maks {{ \App\Models\CatatanRaport::MAKS }} huruf</span>
                    </div>
                    @if($bolehCatatan)
                        <textarea name="catatan" rows="3" maxlength="{{ \App\Models\CatatanRaport::MAKS }}"
                                  placeholder="Contoh: sudah rajin sholat berjamaah; perlu didorong kerapian lemari."
                                  class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">{{ old('catatan', $catatan->isi ?? '') }}</textarea>
                        <p class="text-[11.5px] text-slate-400 mt-1.5">Tampil di bagian bawah rapor santri ini. Dikosongkan = rapor kembali menyediakan ruang tulis tangan.</p>
                    @else
                        <p class="text-[12.5px] text-slate-500 mt-1.5">
                            @if($catatan)
                                {{ $catatan->isi }}
                            @else
                                Belum ada catatan. Catatan diisi oleh musyrif divisi santri ini, Kepala Diniyah, atau Super Admin.
                            @endif
                        </p>
                    @endif
                </div>

                @if($terbuka)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4">
                        @if($berikutnya)
                            <button type="submit" name="lanjut" value="1"
                                    class="w-full inline-flex items-center justify-center h-11 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                                💾 Simpan &amp; lanjut ke {{ $namaBerikutnya ?: 'anak berikutnya' }}
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center h-11 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                                💾 Simpan nilai santri ini
                            </button>
                        @endif

                        <div class="flex flex-col sm:flex-row gap-2 mt-2">
                            <button type="submit" name="kembali" value="kamar"
                                    class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-[12.5px] font-semibold transition">
                                💾 Simpan &amp; kembali ke kamar
                            </button>
                            <a href="{{ route('penilaian.isi.rapor') }}"
                               class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 text-[12.5px] font-semibold transition">
                                Batal
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-app-layout>
