<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

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
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            {{-- ===== PROGRES + AKSI ===== --}}
            @php
                $persenProgres = $totalSiswa > 0 ? round($terisi / $totalSiswa * 100) : 0;
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div class="text-[13px] font-semibold text-slate-700">
                        Terisi <strong class="text-blue-900">{{ $terisi }}</strong> dari {{ $totalSiswa }} siswa aktif
                    </div>
                    <div class="text-[12px] text-slate-400">{{ $kriteria->count() }} pertanyaan × skala 1–5</div>
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

            {{-- ===== ISI CEPAT PER KAMAR (keasramaan) ===== --}}
            @if($sesi->jenis === 'keasramaan' && $daftarKamar->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h4 class="text-[13.5px] font-bold text-slate-800">⚡ Isi cepat per kamar</h4>
                        <span class="text-[11.5px] text-slate-400">skor sama untuk semua penghuni</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($daftarKamar as $kamar)
                            @php $p = $progresKamar[$kamar->id] ?? ['total' => 0, 'sudah' => 0]; @endphp
                            <a href="{{ route('penilaian.sesi.kamar', [$sesi->id, $kamar->id]) }}"
                               class="group flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 hover:border-blue-300 hover:bg-blue-50/60 transition">
                                <span class="min-w-0">
                                    <span class="block text-[12.5px] font-semibold text-slate-700 truncate">{{ $kamar->nama_kamar }}</span>
                                    <span class="block text-[10.5px] text-slate-400">{{ $p['sudah'] }}/{{ $p['total'] }} terisi</span>
                                </span>
                                <span class="text-[13px] {{ ($p['total'] > 0 && $p['sudah'] >= $p['total']) ? 'text-green-600' : 'text-slate-300 group-hover:text-blue-600' }}">
                                    {{ ($p['total'] > 0 && $p['sudah'] >= $p['total']) ? '✓' : '→' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ===== FILTER ===== --}}
            <form action="{{ route('penilaian.sesi', $sesi->id) }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3 grid grid-cols-2 sm:grid-cols-12 gap-2">
                <div class="col-span-2 sm:col-span-4">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari</label>
                    <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Nama atau NISN"
                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kelas</label>
                    <select name="kelas" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        <option value="">Semua</option>
                        @foreach($daftarKelas as $namaKelas)
                            <option value="{{ $namaKelas }}" @selected($filter['kelas'] === $namaKelas)>{{ $namaKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                    <select name="status" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        <option value="">Semua</option>
                        <option value="belum" @selected($filter['status'] === 'belum')>Belum dinilai</option>
                        <option value="sudah" @selected($filter['status'] === 'sudah')>Sudah dinilai</option>
                    </select>
                </div>
                @if($daftarKamar->isNotEmpty())
                    <div class="sm:col-span-2">
                        <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Kamar</label>
                        <select name="kamar" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            <option value="0">Semua</option>
                            @foreach($daftarKamar as $kamar)
                                <option value="{{ $kamar->id }}" @selected($filter['kamar'] === $kamar->id)>{{ $kamar->nama_kamar }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-span-2 sm:col-span-1 flex items-end">
                    <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Filter</button>
                </div>
            </form>

            {{-- ===== DAFTAR SISWA ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                @forelse($siswa as $s)
                    @php $h = $hasil[$s->id] ?? null; @endphp
                    <div class="flex items-center gap-3 px-3 sm:px-4 py-2.5 border-b border-slate-100 last:border-0">
                        <x-avatar-siswa :siswa="$s" />
                        <div class="min-w-0 flex-1">
                            <div class="text-[13.5px] font-semibold text-slate-800 truncate">{{ $s->nama_lengkap }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                {{ $s->kelas ?: 'Kelas -' }}@if(isset($petaKamar[$s->id])) · {{ $petaKamar[$s->id] }}@endif
                                @if($h) · {{ $h['total'] }}/{{ $h['jumlah'] * 5 }} skor @endif
                            </div>
                        </div>

                        @if($h)
                            <span class="hidden sm:inline-flex items-center gap-1 text-[12px] font-bold text-slate-700">{{ $h['persentase'] }}%</span>
                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($h['predikat']) }}">
                                {{ $h['predikat'] }}
                            </span>
                            @unless($h['lengkap'])
                                <span class="hidden sm:inline text-[10.5px] font-bold text-amber-600" title="Belum semua pertanyaan dijawab">belum lengkap</span>
                            @endunless
                        @else
                            <span class="text-[11.5px] font-semibold text-slate-400">Belum dinilai</span>
                        @endif

                        @if($sesi->status === 'draft')
                            <a href="{{ route('penilaian.sesi.form', [$sesi->id, $s->id]) }}"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-blue-50 hover:border-blue-300 hover:text-blue-900 transition whitespace-nowrap">
                                {{ $h ? 'Perbaiki' : 'Isi' }}
                            </a>
                        @else
                            <a href="{{ route('penilaian.siswa', $s->id) }}"
                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 text-[12px] font-semibold hover:bg-slate-100 transition whitespace-nowrap">Lihat</a>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada siswa yang cocok dengan filter.</div>
                @endforelse
            </div>

            <div class="mt-3">{{ $siswa->links() }}</div>
        </div>
    </div>
</x-app-layout>
