<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div class="min-w-0">
                    <a href="{{ route('penilaian.isi', $sesi->jenis) }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Penilaian {{ $sesi->label_jenis }}</a>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">
                        Lembar Penilaian {{ $sesi->label_jenis }}
                    </h3>
                    <p class="text-[13px] text-slate-500 mt-0.5 truncate">
                        {{ $sesi->periode->nama ?? '-' }} · penilai: {{ $sesi->penilai->name ?? '-' }}
                        @if($sesi->penilai_peran) ({{ \App\Models\PenilaianSesi::PERAN[$sesi->penilai_peran] ?? $sesi->penilai_peran }})@endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center h-9 px-3 rounded-lg text-[11.5px] font-bold {{ $sesi->status === 'final' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $sesi->status === 'final' ? '✅ FINAL' : '📝 DRAFT' }}
                    </span>
                    <a href="{{ route('penilaian.rekap', ['periode' => $sesi->periode_id]) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📊 Rekap</a>

                    @php
                        $jawabanSesi = \App\Models\PenilaianJawaban::where('sesi_id', $sesi->id)->count();
                        $bolehHapus = (int) $sesi->penilai_id === (int) auth()->id() || auth()->user()->hasRole('Super Admin');
                        $blokirHapus = $sesi->status === 'final' && ! auth()->user()->hasRole('Super Admin');
                    @endphp
                    @if($bolehHapus)
                        <form action="{{ route('penilaian.sesi.hapus', $sesi->id) }}" method="POST" class="m-0"
                              onsubmit="return confirm('Hapus lembar penilaian ini?{{ $jawabanSesi > 0 ? ' ' . $jawabanSesi . ' jawaban yang sudah tersimpan ikut terhapus.' : '' }}');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center h-9 px-3 rounded-lg border text-[12.5px] font-semibold transition {{ $blokirHapus ? 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed' : 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100' }}"
                                    title="Hapus lembar penilaian">
                                🗑️ Hapus lembar
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            @if($tanpaKamar)
                <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 p-3.5 rounded shadow-sm text-[13px] leading-relaxed">
                    ⚠️ Divisi Anda belum terpetakan (kamar binaan belum diisi), jadi belum ada santri yang bisa dinilai.
                    Hubungi <strong>Kepala Diniyah</strong> untuk memetakan kamar Anda.
                </div>
            @else
                {{-- ===== ATURAN + PROGRES ===== --}}
                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                    Anda menilai <strong>seluruh santri divisi {{ ucfirst($divisi) }}</strong> (putra hanya menilai putra, putri hanya menilai putri).
                    Penilaian diisi <strong>per anak</strong>: pilih kamar mana pun di divisi Anda, lalu nilai penghuninya <strong>satu per satu</strong>
                    ({{ $jumlahKriteria }} pertanyaan, skala 1–5). Bukan penilaian serentak seluruh kamar.
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                    @php
                        $persenProgres = $totalSiswa > 0 ? round($terisi / $totalSiswa * 100) : 0;
                    @endphp
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div class="text-[13px] font-semibold text-slate-700">
                            Terisi <strong class="text-blue-900">{{ $terisi }}</strong> dari {{ $totalSiswa }} santri divisi (wajib lengkap)
                        </div>
                        <div class="text-[12px] text-slate-400">{{ $kamarSelesai }}/{{ $kamarList->count() }} kamar lengkap</div>
                    </div>
                    <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-2 rounded-full bg-blue-900 transition-all" style="width: {{ $persenProgres }}%"></div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        @if($sesi->status === 'draft')
                            <form action="{{ route('penilaian.sesi.finalkan', $sesi->id) }}" method="POST"
                                  onsubmit="return confirm('Finalkan penilaian ini? Setelah final, nilainya terkunci dan tidak bisa diubah lagi kecuali dibuka kembali oleh Super Admin.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">
                                    ✅ Finalkan penilaian
                                </button>
                            </form>
                        @elseif(auth()->user()->hasRole('Super Admin'))
                            <form action="{{ route('penilaian.sesi.buka', $sesi->id) }}" method="POST"
                                  onsubmit="return confirm('Buka kembali penilaian yang sudah final?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-[12.5px] font-semibold hover:bg-amber-100 transition">
                                    🔓 Buka kembali (Super Admin)
                                </button>
                            </form>
                        @else
                            <span class="text-[12px] text-slate-400">Penilaian sudah final. Hubungi Super Admin bila perlu diperbaiki.</span>
                        @endif
                    </div>
                </div>

                {{-- ===== DAFTAR KAMAR BINAAN ===== --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                        <h4 class="text-[13.5px] font-bold text-slate-800">🏠 Kamar divisi {{ ucfirst($divisi) }} (semuanya wajib dinilai)</h4>
                        <span class="text-[11.5px] text-slate-400">{{ $kamarList->count() }} kamar · {{ $jumlahKamarBinaan }} kamar binaan Anda</span>
                    </div>

                    @if($kamarList->isEmpty())
                        <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada kamar aktif yang bisa dinilai.</div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($kamarList as $k)
                                @php
                                    $persenKamar = $k->total > 0 ? (int) round($k->lengkap / $k->total * 100) : 0;
                                    $lengkapSemua = $k->total > 0 && $k->lengkap >= $k->total;
                                @endphp
                                <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-[13.5px] font-bold text-slate-800 truncate">
                                            {{ $k->nama }}
                                            <div class="text-[10.5px] font-black uppercase tracking-wide {{ $k->kategori === 'putri' ? 'text-rose-500' : 'text-blue-600' }}">{{ $k->kategori }}</span>
                                            @if($k->binaan_saya)
                                                <span class="ml-1 inline-flex items-center rounded-md bg-blue-50 text-blue-800 border border-blue-200 px-1.5 py-0.5 text-[10px] font-bold align-middle">binaan saya</span>
                                            @endif
                                            </div>
                                        <div class="text-[11.5px] text-slate-400">
                                            {{ $k->total }} penghuni · sudah dinilai {{ $k->sudah }} · lengkap {{ $k->lengkap }}
                                            @if(! $k->binaan_saya && $k->musyrif) · binaan {{ $k->musyrif }} @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="w-24 hidden sm:block">
                                            <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-1.5 rounded-full {{ $lengkapSemua ? 'bg-green-500' : 'bg-blue-700' }}" style="width: {{ $persenKamar }}%"></div>
                                            </div>
                                        </div>
                                        @if($sesi->status === 'draft')
                                            <a href="{{ route('penilaian.sesi.kamar', [$sesi->id, $k->id]) }}"
                                               class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg {{ $lengkapSemua ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-[12.5px] font-semibold transition whitespace-nowrap">
                                                {{ $lengkapSemua ? '✅ Lihat / perbaiki' : ($k->sudah > 0 ? '➡️ Lanjutkan' : '➡️ Buka kamar') }}
                                            </a>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">terkunci</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
