<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📋 Absensi Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Isi kehadiran santri per kamar dan per sesi</p>
                </div>
                <a href="{{ route('asrama.absensi.rekap', ['bulan' => substr($tanggal, 0, 7), 'kategori' => $kategori]) }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📊 Rekap Bulanan</a>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div>
            @endif

            {{-- Saring: tanggal, sesi, divisi --}}
            <form method="GET" action="{{ route('asrama.absensi.index') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->toDateString() }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Sesi</label>
                        <select name="sesi" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            @foreach(\App\Models\AsramaAbsensi::SESI as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($sesi === $kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Divisi</label>
                        <select name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua divisi</option>
                            <option value="putra" @selected($kategori === 'putra')>Putra</option>
                            <option value="putri" @selected($kategori === 'putri')>Putri</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Tampilkan</button>
                </div>
            </form>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-5 py-4 mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="text-[13px] font-semibold text-slate-600">
                    Sesi <span class="text-blue-900 font-bold">{{ \App\Models\AsramaAbsensi::labelSesi($sesi) }}</span>
                    · {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                </div>
                <div class="text-[13px] font-semibold text-slate-600">
                    Terisi <span class="text-blue-900 font-bold">{{ $kamarSelesai }}/{{ $jumlahKamar }}</span> kamar
                </div>
            </div>

            @if($kamar->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">🛏️</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Tidak ada kamar yang bisa Anda isi</h3>
                    <p class="text-sm text-slate-400">Pastikan Anda sudah ditugaskan sebagai musyrif kamar asrama.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($kamar as $k)
                        @php
                            $ada = $tersimpan[$k->id] ?? null;
                            $jml = $ada ? (int) $ada->jml : 0;
                            $nonhadir = $ada ? (int) $ada->nonhadir : 0;
                            $lengkap = (int) $k->penghuni_count > 0 && $jml >= (int) $k->penghuni_count;
                            $rincian = $bermasalah[$k->id] ?? collect();
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 flex flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900 uppercase">{{ $k->nama_kamar }}</div>
                                    <div class="text-[12px] text-slate-500 mt-0.5">{{ ucfirst($k->kategori) }} · {{ (int) $k->penghuni_count }} penghuni</div>
                                </div>
                                @if($lengkap)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Terisi</span>
                                @elseif($jml > 0)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Sebagian</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Belum diisi</span>
                                @endif
                            </div>

                            @if($rincian->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach($rincian as $r)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold border {{ $r->status === 'alpa' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                            {{ \App\Models\AsramaAbsensi::labelStatus($r->status) }} {{ (int) $r->jml }}
                                        </span>
                                    @endforeach
                                </div>
                            @elseif($lengkap && $nonhadir === 0)
                                <div class="text-[12px] font-semibold text-emerald-600 mt-3">Semua hadir 👍</div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <a href="{{ route('asrama.absensi.form', [$k->id, 'tanggal' => $tanggal, 'sesi' => $sesi]) }}"
                                   class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg {{ $jml > 0 ? 'bg-white border border-slate-300 text-slate-700' : 'bg-blue-900 text-white' }} text-[13px] font-semibold">
                                    {{ $jml > 0 ? '✏️ Ubah Absensi' : '📋 Isi Absensi' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
