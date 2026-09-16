<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('penilaian.sesi', $sesi->id) }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Kembali ke daftar siswa</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">Isi cepat kamar {{ $kamar->nama_kamar }}</h3>
                <p class="text-[13px] text-slate-500 mt-0.5">Penilaian {{ $sesi->label_jenis }} · {{ $sesi->periode->nama ?? '-' }}</p>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Skor di halaman ini berlaku untuk <strong>semua {{ $anggota->count() }} penghuni kamar {{ $kamar->nama_kamar }}</strong> sekaligus.
                Sesudah tersimpan, kamu masih bisa membuka satu per satu siswa untuk mengoreksi yang berbeda.
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Penghuni kamar</div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($anggota as $a)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600">
                            {{ $a->nama_lengkap }}
                            @if($a->kelas)<span class="rounded-md bg-white border border-slate-200 px-1.5 py-px text-[10px] font-bold text-slate-500">{{ $a->kelas }}</span>@endif
                        </span>
                    @empty
                        <span class="text-[12.5px] text-slate-400">Tidak ada siswa aktif di kamar ini.</span>
                    @endforelse
                </div>
            </div>

            @if($kriteria->isEmpty())
                <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 p-3.5 rounded shadow-sm text-[13px]">
                    Belum ada pertanyaan aktif untuk {{ $sesi->label_jenis }}.
                </div>
            @else
                <form action="{{ route('penilaian.sesi.kamarSimpan', [$sesi->id, $kamar->id]) }}" method="POST">
                    @csrf
                    @include('penilaian.partials._kuesioner', ['kriteria' => $kriteria, 'jawaban' => $jawaban])

                    <button type="submit"
                            class="mt-4 inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                        💾 Simpan untuk seluruh kamar
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
