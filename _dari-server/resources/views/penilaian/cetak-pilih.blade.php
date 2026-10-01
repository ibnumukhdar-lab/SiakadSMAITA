<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🖨️ Cetak Rapor Adab &amp; Keasramaan</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Satu santri = satu halaman A4. Cetak sekaligus satu kamar, atau satu santri saja.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(! $semuaKamar)
                        <a href="{{ route('penilaian.isi.rapor') }}"
                           class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📋 Isi Rapor</a>
                    @else
                        <a href="{{ route('penilaian.sesi-rapor', ['periode' => $periodeId]) }}"
                           class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">🗂️ Sesi &amp; Progres</a>
                    @endif
                </div>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3 mb-3 rounded shadow-sm text-[13px]">{{ session('error') }}</div>
            @endif

            <div class="bg-blue-50 border border-blue-200 text-blue-900 p-3.5 mb-3 rounded-xl text-[12.5px] leading-relaxed">
                @if($semuaKamar)
                    Anda dapat mencetak rapor <strong>semua kamar</strong> (TU / Kepala Sekolah / Kepala Diniyah / Super Admin).
                @else
                    Anda mencetak rapor untuk <strong>kamar binaan Anda sendiri</strong>. Rapor kamar lain bisa dicetak oleh
                    <strong>TU / Kepala Sekolah / Kepala Diniyah</strong>.
                @endif
                Nilai di rapor = rata-rata semua musyrif/musyrifah divisi yang mengisi pada periode ini.
            </div>

            <form action="{{ route('penilaian.cetak') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-3 flex flex-col sm:flex-row sm:items-end gap-2.5">
                <div class="flex-1">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode / sesi</label>
                    <select name="periode" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        @foreach($periodeList as $p)
                            <option value="{{ $p->id }}" @selected($periodeId === $p->id)>{{ $p->nama }}@if($p->aktif) — aktif @endif</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="h-10 px-4 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>
            </form>

            @forelse($perKamar as $k)
                @php $selesaiKamar = $k->total > 0 && $k->dinilai >= $k->total; @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-3">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-[13.5px] font-bold text-slate-800">
                                Kamar {{ $k->nama }}
                                <span class="ml-1 inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-bold align-middle {{ $k->kategori === 'putri' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">{{ ucfirst($k->kategori) }}</span>
                            </div>
                            <div class="text-[11.5px] text-slate-400">
                                {{ $k->total }} santri · sudah dinilai {{ $k->dinilai }}
                                @if($k->musyrif) · binaan {{ $k->musyrif }} @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $selesaiKamar ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $selesaiKamar ? 'lengkap' : 'sebagian' }}
                            </span>
                            <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'kamar' => $k->id]) }}" target="_blank"
                               class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition whitespace-nowrap">
                                🖨️ Cetak kamar ini ({{ $k->total }})
                            </a>
                        </div>
                    </div>

                    @forelse($k->penghuni as $s)
                        <div class="flex flex-wrap items-center gap-3 px-4 py-2.5 border-b border-slate-100 last:border-0">
                            <div class="min-w-0 flex-1">
                                <div class="text-[13px] font-semibold text-slate-800 truncate">{{ $s->nama }}</div>
                                <div class="text-[11.5px] text-slate-400">
                                    Kelas {{ $s->kelas ?: '-' }}
                                    @if($s->adab !== null) · Adab {{ $s->adab }} ({{ $s->adab_predikat }}) @else · Adab - @endif
                                    @if($s->asrama !== null) · Keasramaan {{ $s->asrama }} ({{ $s->asrama_predikat }}) @else · Keasramaan - @endif
                                    @if($s->ada_catatan) · catatan terisi @endif
                                </div>
                            </div>

                            @unless($s->dinilai)
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">belum lengkap</span>
                            @endunless

                            <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'siswa' => $s->id]) }}" target="_blank"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900 transition whitespace-nowrap">
                                🖨️ Cetak
                            </a>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-[13px] text-slate-400">Belum ada santri aktif di kamar ini.</div>
                    @endforelse
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-8 text-center text-[13px] text-slate-400">
                    Belum ada kamar binaan yang bisa dicetak. Hubungi Kepala Diniyah bila kamar Anda belum dipetakan.
                </div>
            @endforelse

            @if($perKamar->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5">
                    <h4 class="text-[13px] font-bold text-slate-800 mb-1">Pencarian per santri &amp; isian catatan rapor</h4>
                    <p class="text-[12px] text-slate-500 mb-2.5">
                        Untuk mencari satu nama dari seluruh santri dan mengisi catatan rapor, buka halaman daftar rapor.
                    </p>
                    <a href="{{ route('penilaian.rapot', ['periode' => $periodeId]) }}"
                       class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">
                        Buka daftar rapor per santri →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
