@if(!$sudah)
<div x-show="openModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto" @click.away="openModal = false">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="text-[15px] font-bold text-slate-800">⭐ Nilai: {{ $kamar->nama_kamar }}</h3>
            <button type="button" @click="openModal = false" class="h-8 w-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition text-xl leading-none">&times;</button>
        </div>

        <form action="{{ route('asrama.penilaian.simpanSkor', $kamar->id) }}" method="POST" class="p-5 sm:p-6 space-y-5">
            @csrf
            <!-- 5 Kriteria -->
            @php
                $kriterias = [
                    '1' => 'Kerapian Tempat Tidur 🛏️', '2' => 'Kebersihan Lantai 🧹',
                    '3' => 'Kerapian Lemari 🚪', '4' => 'Barang & Sepatu 👞', '5' => 'Aroma & Sirkulasi 🌬️'
                ];
            @endphp

            @foreach($kriterias as $key => $judul)
            <div>
                <div class="text-[13px] font-bold text-slate-700 mb-2">{{ $key }}. {{ $judul }}</div>
                <div class="grid grid-cols-5 gap-2">
                    @for($i=1; $i<=5; $i++)
                    <label for="k{{$key}}_{{$kamar->id}}_{{$i}}" class="cursor-pointer">
                        <input type="radio" id="k{{$key}}_{{$kamar->id}}_{{$i}}" name="skor_{{$key}}" value="{{$i}}" class="peer sr-only" required>
                        <span class="flex h-9 items-center justify-center rounded-lg border border-slate-300 bg-white text-sm font-bold text-slate-600 transition peer-checked:border-blue-900 peer-checked:bg-blue-900 peer-checked:text-white hover:border-blue-400">{{$i}}</span>
                    </label>
                    @endfor
                </div>
            </div>
            @endforeach

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Catatan</label>
                <textarea name="catatan" rows="2" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Catatan opsional..."></textarea>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                💾 SIMPAN NILAI
            </button>
        </form>
    </div>
</div>
@endif
