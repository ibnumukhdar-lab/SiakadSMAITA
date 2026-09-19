<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📋 Isi Rapor Adab &amp; Keasramaan</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Satu santri diisi <strong>satu kali</strong> — bagian A (Adab) dan B (Keasramaan) dalam satu formulir.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('penilaian.cetak') }}"
                       class="self-start sm:self-auto inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">
                        🖨️ Cetak Rapor
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            {{-- ===== STATUS SESI ===== --}}
            @if($terbuka && $periode)
                <div class="bg-green-50 border border-green-200 text-green-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    <strong>Sesi TERBUKA</strong> — {{ $periode->nama }}@if($periode->tahun_ajaran) · TA {{ $periode->tahun_ajaran }}@endif.
                    @if($periode->pembuka)
                        Dibuka oleh {{ $periode->pembuka->name }}@if($periode->dibuka_pada) pada {{ $periode->dibuka_pada->format('d/m/Y H:i') }}@endif.
                    @endif
                    @if($divisi)
                        Anda menilai <strong>seluruh santri {{ $divisi }}</strong>; rapor memakai rata-rata semua musyrif/musyrifah divisi ini.
                    @else
                        Anda masuk sebagai <strong>Super Admin</strong> — boleh membuka kamar mana pun.
                    @endif
                </div>
            @else
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    <strong>Sesi isi rapor belum dibuka.</strong>
                    @if($periodeAktif && $periodeAktif->ditutup_pada)
                        Periode {{ $periodeAktif->nama }} sudah <strong>DITUTUP</strong>
                        @if($periodeAktif->penutup) oleh {{ $periodeAktif->penutup->name }}@endif
                        @if($periodeAktif->ditutup_pada) pada {{ $periodeAktif->ditutup_pada->format('d/m/Y H:i') }}@endif.
                    @endif
                    Tunggu <strong>Tata Usaha / Kepala Diniyah / Kepala Sekolah</strong> membuka sesinya. Setelah dibuka, daftar kamar di bawah langsung bisa diisi.
                </div>
            @endif

            @if($divisi === null && ! auth()->user()->hasRole('Super Admin'))
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    ⚠️ Divisi Anda belum terpetakan (kamar binaan belum diisi), jadi belum ada santri yang bisa dinilai.
                    Hubungi <strong>Kepala Diniyah</strong> untuk memetakan kamar Anda.
                </div>
            @endif

            @if($jumlahKriteria['adab'] === 0 && $jumlahKriteria['keasramaan'] === 0)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 mb-3 rounded-xl text-[13px] leading-relaxed">
                    ⚠️ Belum ada pertanyaan aktif. Minta admin menambahkannya di
                    <a href="{{ route('penilaian.master') }}" class="font-bold underline">Pertanyaan &amp; Ambang</a>.
                </div>
            @endif

            {{-- ===== PROGRES SAYA ===== --}}
            @php
                $persenSaya = $totalTarget > 0 ? (int) round($totalLengkap / $totalTarget * 100) : 0;
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-3">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <div>
                        <div class="text-[13.5px] font-bold text-slate-800">Progres saya</div>
                        <div class="text-[12px] text-slate-500 mt-0.5">
                            Terisi <strong class="text-slate-800">{{ $totalLengkap }}</strong> dari {{ $totalTarget }} santri
                            @if($divisi) {{ $divisi }} @endif
                            · {{ $kamarSelesai }}/{{ $jumlahKamar }} kamar lengkap
                        </div>
                    </div>
                    <div class="text-[15px] font-extrabold text-blue-900">{{ $persenSaya }}%</div>
                </div>
                <div class="h-2 w-full rounded-full bg-slate-100 overflow-hidden mt-2.5">
                    <div class="h-2 rounded-full {{ $totalTarget > 0 && $totalLengkap >= $totalTarget ? 'bg-green-500' : 'bg-blue-900' }}" style="width: {{ $persenSaya }}%"></div>
                </div>
                @if($jumlahKriteria['adab'] > 0 || $jumlahKriteria['keasramaan'] > 0)
                    <div class="text-[11.5px] text-slate-400 mt-2">
                        Tiap santri: {{ $jumlahKriteria['adab'] }} pertanyaan Adab + {{ $jumlahKriteria['keasramaan'] }} pertanyaan Keasramaan (skala 1–5).
                    </div>
                @endif
            </div>

            {{-- ===== KAMAR BINAAN SAYA ===== --}}
            @if($kamarBinaan->isNotEmpty())
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-3">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Kamar binaan saya</h4>
                        <span class="text-[11.5px] text-slate-400">{{ $kamarBinaan->count() }} kamar</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($kamarBinaan as $k)
                            @php
                                $persenKamar = $k->total > 0 ? (int) round($k->lengkap / $k->total * 100) : 0;
                                $selesaiKamar = $k->total > 0 && $k->lengkap >= $k->total;
                            @endphp
                            <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-[13.5px] font-bold text-slate-800 truncate">
                                        {{ $k->nama }}
                                        <span class="ml-1 inline-flex items-center rounded-md bg-blue-50 text-blue-800 border border-blue-200 px-1.5 py-0.5 text-[10px] font-bold align-middle">binaan saya</span>
                                    </div>
                                    <div class="text-[11.5px] text-slate-400">
                                        {{ $k->total }} penghuni · sudah lengkap {{ $k->lengkap }}
                                        @if($k->sebagian > 0) · belum lengkap {{ $k->sebagian }} @endif
                                    </div>
                                </div>
                                <div class="hidden sm:block w-24">
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $selesaiKamar ? 'bg-green-500' : 'bg-blue-700' }}" style="width: {{ $persenKamar }}%"></div>
                                    </div>
                                </div>
                                @if($terbuka)
                                    <a href="{{ route('penilaian.isi.kamar', $k->id) }}"
                                       class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg {{ $selesaiKamar ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-[12.5px] font-semibold transition whitespace-nowrap">
                                        {{ $selesaiKamar ? '✅ Lihat / perbaiki' : ($k->lengkap > 0 ? '➡️ Lanjutkan' : '➡️ Buka') }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">terkunci</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ===== KAMAR LAIN DI DIVISI ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                    <h4 class="text-[13.5px] font-bold text-slate-800">
                        {{ $divisi ? 'Kamar lain di divisi ' . ucfirst($divisi) : 'Semua kamar' }}
                    </h4>
                    <span class="text-[11.5px] text-slate-400">semuanya wajib dinilai</span>
                </div>

                @if($kamarLain->isEmpty())
                    <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada kamar lain pada divisi Anda.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($kamarLain as $k)
                            @php
                                $persenKamar = $k->total > 0 ? (int) round($k->lengkap / $k->total * 100) : 0;
                                $selesaiKamar = $k->total > 0 && $k->lengkap >= $k->total;
                            @endphp
                            <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="text-[13.5px] font-bold text-slate-800 truncate">{{ $k->nama }}</div>
                                    <div class="text-[11.5px] text-slate-400">
                                        {{ $k->total }} penghuni · sudah lengkap {{ $k->lengkap }}
                                        @if($k->musyrif) · binaan {{ $k->musyrif }} @endif
                                    </div>
                                </div>
                                <div class="hidden sm:block w-24">
                                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $selesaiKamar ? 'bg-green-500' : 'bg-blue-700' }}" style="width: {{ $persenKamar }}%"></div>
                                    </div>
                                </div>
                                @if($terbuka)
                                    <a href="{{ route('penilaian.isi.kamar', $k->id) }}"
                                       class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg {{ $selesaiKamar ? 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-[12.5px] font-semibold transition whitespace-nowrap">
                                        {{ $selesaiKamar ? '✅ Lihat / perbaiki' : ($k->lengkap > 0 ? '➡️ Lanjutkan' : '➡️ Buka') }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">terkunci</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <p class="text-[11.5px] text-slate-400 mt-3">
                Lembar penilaian saya dibuat otomatis — tidak perlu lagi memilih periode dan menekan “Buka lembar penilaian”.
            </p>
        </div>
    </div>
</x-app-layout>
