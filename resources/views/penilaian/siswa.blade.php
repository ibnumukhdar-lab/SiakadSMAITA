<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('penilaian.rekap') }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Rekap Penilaian</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">Rincian Penilaian Karakter</h3>
            </div>

            {{-- ===== IDENTITAS ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3 flex items-center gap-3">
                <x-avatar-siswa :siswa="$siswa" ukuran="besar" />
                <div class="min-w-0 flex-1">
                    <div class="text-[15px] font-bold text-slate-900 truncate">{{ $siswa->nama_lengkap }}</div>
                    <div class="text-[12px] text-slate-500 truncate">
                        NISN {{ $siswa->nisn ?: '-' }} · Kelas {{ $siswa->kelas ?: '-' }}@if($kamar) · Kamar {{ $kamar }}@endif
                    </div>
                </div>
                <a href="{{ route('siswa.show', $siswa->id) }}"
                   class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">Profil</a>
            </div>

            {{-- ===== RINGKASAN PER PERIODE ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <h4 class="text-[13.5px] font-bold text-slate-800 mb-2.5">Nilai akhir per periode</h4>
                @forelse($perPeriode as $periodeId => $jenis)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-3 mb-2 last:mb-0">
                        <div class="text-[12.5px] font-bold text-slate-700 mb-2">📅 {{ $jenis['adab']['periode'] ?? $jenis['keasramaan']['periode'] ?? '-' }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['adab' => '🕌 Adab', 'keasramaan' => '🛏️ Keasramaan'] as $kunci => $label)
                                @php $n = $jenis[$kunci] ?? null; @endphp
                                <div class="rounded-lg border border-slate-200 bg-white px-2.5 py-2">
                                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                                    @if($n && $n['rata'] !== null)
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[15px] font-extrabold text-slate-900">{{ $n['rata'] }}%</span>
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($n['predikat']) }}">{{ $n['predikat'] }}</span>
                                        </div>
                                        <div class="text-[10.5px] text-slate-400">{{ $n['jumlah_penilai'] }} penilai</div>
                                    @else
                                        <div class="text-[13px] text-slate-300 mt-1">Belum ada nilai</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-[13px] text-slate-400">Belum ada nilai penilaian untuk siswa ini.</div>
                @endforelse
            </div>

            {{-- ===== RINCIAN PER LEMBAR PENILAIAN ===== --}}
            @foreach($rincian as $it)
                @php
                    $sesi = $it['sesi'];
                    $kriteria = \App\Models\PenilaianKriteria::daftarAktif($sesi->jenis);
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                        <div class="min-w-0">
                            <div class="text-[13.5px] font-bold text-slate-800">
                                {{ $sesi->jenis === 'adab' ? '🕌' : '🛏️' }} {{ $sesi->label_jenis }} · {{ $sesi->periode->nama ?? '-' }}
                            </div>
                            <div class="text-[11.5px] text-slate-400">
                                {{ $sesi->penilai->name ?? '-' }} ({{ \App\Models\PenilaianSesi::PERAN[$sesi->penilai_peran] ?? '-' }})
                                @if($sesi->difinalkan_pada) · difinalkan {{ $sesi->difinalkan_pada->format('d/m/Y') }}@endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold {{ $sesi->status === 'final' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $sesi->status === 'final' ? 'FINAL' : 'DRAFT' }}
                            </span>
                            @if($it['persentase'] !== null)
                                <span class="text-[14px] font-extrabold text-slate-900">{{ $it['persentase'] }}%</span>
                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($it['predikat']) }}">{{ $it['predikat'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($kriteria as $k)
                            @php $skor = isset($it['jawaban'][$k->id]) ? (int) $it['jawaban'][$k->id]->skor : null; @endphp
                            <div class="flex items-center gap-2 py-1.5">
                                <span class="min-w-0 flex-1 text-[12.5px] text-slate-600">{{ $k->pertanyaan }}</span>
                                <span class="flex items-center gap-1 shrink-0">
                                    @for($n = 1; $n <= 5; $n++)
                                        <span class="h-2.5 w-2.5 rounded-full {{ $skor !== null && $n <= $skor ? ($skor >= 4 ? 'bg-green-500' : ($skor === 3 ? 'bg-amber-400' : 'bg-red-400')) : 'bg-slate-200' }}"></span>
                                    @endfor
                                    <span class="w-5 text-right text-[12px] font-bold text-slate-700">{{ $skor ?? '—' }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @unless($it['lengkap'])
                        <div class="mt-2 text-[11.5px] font-semibold text-amber-600">⚠️ Belum semua pertanyaan dijawab pada lembar ini.</div>
                    @endunless

                    @if($sesi->catatan)
                        <div class="mt-2 rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-2 text-[12px] text-slate-600">
                            📝 {{ $sesi->catatan }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
