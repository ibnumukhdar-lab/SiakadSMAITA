<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('penilaian.sesi', $sesi->id) }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Kembali ke daftar siswa</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                    Penilaian {{ $sesi->label_jenis }}
                </h3>
                <p class="text-[13px] text-slate-500 mt-0.5">{{ $sesi->periode->nama ?? '-' }} · penilai: {{ $sesi->penilai->name ?? '-' }}</p>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Identitas siswa --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3 flex items-center gap-3">
                <x-avatar-siswa :siswa="$siswa" ukuran="besar" />
                <div class="min-w-0 flex-1">
                    <div class="text-[14.5px] font-bold text-slate-900 truncate">{{ $siswa->nama_lengkap }}</div>
                    <div class="text-[12px] text-slate-500 truncate">
                        NISN {{ $siswa->nisn ?: '-' }} · Kelas {{ $siswa->kelas ?: '-' }}@if($kamar) · Kamar {{ $kamar }}@endif
                    </div>
                </div>
                <span class="text-[10.5px] font-bold px-2 py-1 rounded-full {{ $sesi->status === 'final' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $sesi->status === 'final' ? 'FINAL' : 'DRAFT' }}
                </span>
            </div>

            @if($kriteria->isEmpty())
                <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 p-3.5 rounded shadow-sm text-[13px]">
                    Belum ada pertanyaan aktif untuk {{ $sesi->label_jenis }}. Tambahkan dulu di
                    <a href="{{ route('penilaian.master') }}" class="font-bold underline">Master Penilaian</a>.
                </div>
            @else
                <form action="{{ route('penilaian.sesi.simpan', [$sesi->id, $siswa->id]) }}" method="POST">
                    @csrf
                    @if($berikutnya)
                        <input type="hidden" name="berikutnya" value="{{ $berikutnya }}">
                    @endif

                    @include('penilaian.partials._kuesioner', ['kriteria' => $kriteria, 'jawaban' => $jawaban])

                    <div class="flex flex-col sm:flex-row gap-2 mt-4">
                        <button type="submit"
                                class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                            💾 Simpan Nilai
                        </button>
                        @if($berikutnya)
                            <button type="submit" name="lanjut" value="1"
                                    class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-blue-900 text-blue-900 bg-white hover:bg-blue-50 text-[13px] font-semibold transition">
                                ➡️ Simpan &amp; lanjut siswa berikutnya
                            </button>
                        @else
                            <span class="inline-flex items-center h-10 px-1 text-[12.5px] text-slate-400">Semua siswa sudah terisi 🎉</span>
                        @endif
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
