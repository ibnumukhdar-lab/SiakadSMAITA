<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div class="min-w-0">
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">🗂️ Sesi &amp; Progres Rapor</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Buka / tutup sesi isi rapor Adab &amp; Keasramaan untuk seluruh musyrif, lalu pantau siapa yang belum selesai.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('penilaian.rekap', ['periode' => $periode->id ?? 0]) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📊 Rekap Nilai</a>
                    <a href="{{ route('penilaian.cetak', ['periode' => $periode->id ?? 0]) }}"
                       class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">🖨️ Cetak Rapor</a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            {{-- ===== PILIH PERIODE (semester) ===== --}}
            <form action="{{ route('penilaian.sesi-rapor') }}" method="GET"
                  class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-3">
                <div class="flex flex-col sm:flex-row sm:items-end gap-2.5">
                    <div class="flex-1">
                        <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Periode / semester</label>
                        <select name="periode" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            @foreach($periodeList as $p)
                                @php
                                    $tandaPeriode = ($p->aktif ? ' — periode berjalan' : '') . ($p->terbuka ? ' — sesi terbuka' : '');
                                @endphp
                                <option value="{{ $p->id }}" @selected(($periode->id ?? 0) === $p->id)>{{ $p->nama }}{{ $tandaPeriode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="h-10 px-4 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Tampilkan</button>

                    @can('kelola-master-penilaian')
                        <a href="{{ route('penilaian.master', ['tab' => 'periode']) }}"
                           class="h-10 px-3.5 inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 text-[12.5px] font-semibold transition whitespace-nowrap">
                            ➕ Tambah periode (semester baru)
                        </a>
                    @endcan
                </div>
                <p class="text-[11.5px] text-slate-400 mt-2">
                    “Semester 1 / Semester 2” adalah <strong>periode</strong> yang dibuat di menu
                    <span class="font-semibold">Pertanyaan &amp; Ambang → tab 📅 Periode</span> (nama bebas, mis. “Semester 2 2026/2027”).
                    Satu periode = satu sesi rapor; hanya satu sesi yang bisa terbuka, dan membuka periode baru otomatis menutup yang lama.
                </p>
            </form>

            @if(! $periode)
                <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 rounded-xl text-[13px]">
                    Belum ada periode penilaian. Tambahkan dulu di
                    <a href="{{ route('penilaian.master') }}" class="font-bold underline">Pertanyaan &amp; Ambang</a>.
                </div>
            @else
                {{-- ===== KARTU SESI ===== --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-3">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-[15px] font-extrabold text-slate-900">
                                {{ $periode->nama }}
                                <span class="ml-1 inline-flex items-center rounded-full px-2.5 py-1 text-[10.5px] font-bold align-middle {{ $periode->terbuka ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                    {{ $periode->terbuka ? 'TERBUKA' : 'DITUTUP' }}
                                </span>
                            </div>
                            <div class="text-[12.5px] text-slate-500 mt-1">
                                {{ $periode->tahun_ajaran ? 'TA ' . $periode->tahun_ajaran . ' · ' : '' }}
                                {{ $jumlahKriteria['adab'] }} pertanyaan Adab + {{ $jumlahKriteria['keasramaan'] }} pertanyaan Keasramaan
                                · {{ $kamarTotal }} kamar · {{ (int) ($penghuniDivisi['putra'] ?? 0) }} santri putra / {{ (int) ($penghuniDivisi['putri'] ?? 0) }} santri putri
                            </div>
                            <div class="text-[11.5px] text-slate-400 mt-1">
                                @if($periode->terbuka)
                                    Dibuka
                                    @if($periode->pembuka) oleh {{ $periode->pembuka->name }} @endif
                                    @if($periode->dibuka_pada) pada {{ $periode->dibuka_pada->format('d/m/Y H:i') }} @endif
                                @else
                                    Ditutup
                                    @if($periode->penutup) oleh {{ $periode->penutup->name }} @endif
                                    @if($periode->ditutup_pada) pada {{ $periode->ditutup_pada->format('d/m/Y H:i') }} @endif
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if($periode->terbuka)
                                <form action="{{ route('penilaian.sesi-rapor.tutup') }}" method="POST" class="m-0"
                                      onsubmit="return confirm('Tutup sesi {{ $periode->nama }}? Semua musyrif berhenti mengisi dan seluruh lembar dikunci. Rapor tetap bisa dicetak.');">
                                    @csrf
                                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                                    <button type="submit" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 text-[12.5px] font-semibold transition">
                                        🔒 Tutup sesi
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('penilaian.sesi-rapor.buka') }}" method="POST" class="m-0"
                                      onsubmit="return confirm('Buka sesi {{ $periode->nama }}? Seluruh musyrif/musyrifah bisa mulai mengisi rapor.');">
                                    @csrf
                                    <input type="hidden" name="periode_id" value="{{ $periode->id }}">
                                    <button type="submit" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">
                                        🔓 Buka sesi isi rapor
                                    </button>
                                </form>
                            @endif

                            @can('kelola-master-penilaian')
                                <a href="{{ route('penilaian.master') }}"
                                   class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">⚙️ Pertanyaan &amp; Ambang</a>
                            @endcan
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mt-4">
                        <div class="border border-slate-200 rounded-xl p-3">
                            <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Sudah dinilai</div>
                            <div class="text-[19px] font-extrabold text-slate-900 mt-0.5">{{ $totalDinilai }} <span class="text-[12.5px] font-semibold text-slate-400">/ {{ $totalTarget }} penilaian santri</span></div>
                        </div>
                        <div class="border border-slate-200 rounded-xl p-3">
                            <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Musyrif selesai</div>
                            <div class="text-[19px] font-extrabold text-slate-900 mt-0.5">{{ $musyrifSelesai }} <span class="text-[12.5px] font-semibold text-slate-400">/ {{ $musyrifTotal }}</span></div>
                        </div>
                        <div class="border border-slate-200 rounded-xl p-3">
                            <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Kamar sudah dinilai</div>
                            <div class="text-[19px] font-extrabold text-slate-900 mt-0.5">{{ $kamarLengkap }} <span class="text-[12.5px] font-semibold text-slate-400">/ {{ $kamarTotal }}</span></div>
                        </div>
                    </div>

                    <p class="text-[11.5px] text-slate-400 mt-3">
                        “Kamar sudah dinilai” = seluruh penghuninya telah dinilai lengkap oleh minimal satu musyrif/musyrifah divisinya.
                        Rapor memakai rata-rata semua pengisi, karena itu seluruh musyrif divisi diminta mengisi.
                    </p>
                </div>

                {{-- ===== TAB DIVISI ===== --}}
                <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                    <div class="inline-flex items-center gap-1 bg-slate-100 rounded-lg p-0.5">
                        @foreach(['semua' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri'] as $nilai => $label)
                            <a href="{{ route('penilaian.sesi-rapor', ['periode' => $periode->id, 'divisi' => $nilai]) }}"
                               class="px-3 py-1.5 rounded-md text-[12px] font-semibold {{ $divisi === $nilai ? 'bg-white text-blue-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <a href="{{ route('penilaian.kelengkapan', ['periode' => $periode->id]) }}"
                       class="text-[12px] font-semibold text-slate-500 hover:text-slate-800">Rincian per kamar &amp; santri →</a>
                </div>

                {{-- ===== PROGRES PER MUSYRIF ===== --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Progres pengisian per musyrif/musyrifah</h4>
                    </div>

                    @if($baris->isEmpty())
                        <div class="px-4 py-8 text-center text-[13px] text-slate-400">Tidak ada musyrif pada saringan ini.</div>
                    @else
                        {{-- Tabel (layar lebar) --}}
                        <table class="hidden lg:table w-full">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="text-left px-4 py-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Musyrif / Musyrifah</th>
                                    <th class="text-left px-4 py-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Divisi</th>
                                    <th class="text-left px-4 py-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Kamar binaan</th>
                                    <th class="text-left px-4 py-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400 w-[26%]">Terisi</th>
                                    <th class="text-left px-4 py-2.5 text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Status</th>
                                    <th class="px-4 py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($baris as $b)
                                    @php
                                        $persen = $b->target > 0 ? (int) round($b->dinilai / $b->target * 100) : 0;
                                        $selesai = $b->target > 0 && $b->dinilai >= $b->target;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-[13px] font-semibold text-slate-800">
                                            {{ $b->nama }}
                                            @if($b->nipa)
                                                <div class="text-[11px] font-normal text-slate-400">NIPA {{ $b->nipa }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[13px] text-slate-600">{{ $b->divisi ? ucfirst($b->divisi) : '—' }}</td>
                                        <td class="px-4 py-3 text-[13px] text-slate-600">
                                            {{ $b->kamar }} kamar
                                            @if($b->kamar > 0)
                                                <div class="text-[11px] text-slate-400">{{ implode(' · ', $b->nama_kamar) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-1.5 rounded-full {{ $selesai ? 'bg-green-500' : 'bg-blue-700' }}" style="width: {{ $persen }}%"></div>
                                                </div>
                                                <span class="text-[12.5px] font-bold text-slate-700 whitespace-nowrap">{{ $b->dinilai }}/{{ $b->target }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($b->target === 0)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-slate-100 text-slate-500 border border-slate-200">tanpa santri</span>
                                            @elseif($selesai)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-green-50 text-green-700 border border-green-200">selesai</span>
                                            @elseif($b->dinilai > 0)
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-amber-50 text-amber-700 border border-amber-200">jalan</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-rose-50 text-rose-700 border border-rose-200">belum mulai</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('penilaian.kelengkapan', ['periode' => $periode->id]) }}"
                                               class="inline-flex items-center justify-center h-8 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-slate-50 transition">Rincian</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Daftar kartu (HP) --}}
                        <div class="lg:hidden divide-y divide-slate-100">
                            @foreach($baris as $b)
                                @php
                                    $persen = $b->target > 0 ? (int) round($b->dinilai / $b->target * 100) : 0;
                                    $selesai = $b->target > 0 && $b->dinilai >= $b->target;
                                @endphp
                                <div class="px-4 py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <div class="text-[13.5px] font-semibold text-slate-800">{{ $b->nama }}</div>
                                            <div class="text-[11.5px] text-slate-400">
                                                {{ $b->divisi ? ucfirst($b->divisi) : 'divisi belum jelas' }} · {{ $b->kamar }} kamar binaan
                                            </div>
                                        </div>
                                        @if($b->target === 0)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-slate-100 text-slate-500 border border-slate-200">tanpa santri</span>
                                        @elseif($selesai)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-green-50 text-green-700 border border-green-200">selesai</span>
                                        @elseif($b->dinilai > 0)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-amber-50 text-amber-700 border border-amber-200">jalan</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold whitespace-nowrap bg-rose-50 text-rose-700 border border-rose-200">belum mulai</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2.5 mt-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $selesai ? 'bg-green-500' : 'bg-blue-700' }}" style="width: {{ $persen }}%"></div>
                                        </div>
                                        <span class="text-[12px] font-bold text-slate-700">{{ $b->dinilai }}/{{ $b->target }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if($musyrifTanpaKamar->isNotEmpty())
                    <div class="bg-amber-50 border border-amber-300 text-amber-900 p-3.5 rounded-xl text-[12.5px] leading-relaxed mt-3">
                        <strong>Perlu dipetakan Kepala Diniyah:</strong>
                        @foreach($musyrifTanpaKamar as $m)
                            {{ $m->nama }}@if(! $loop->last), @endif
                        @endforeach
                        — belum punya kamar binaan (atau kamarnya bercampur putra &amp; putri), jadi divisinya belum bisa ditentukan.
                        Pemetaan kamar dilakukan di menu <strong>Manajemen Kamar</strong>.
                    </div>
                @endif

                <p class="text-[11.5px] text-slate-400 mt-3">
                    Setelah sesi ditutup, musyrif tidak bisa mengisi lagi (tombol isi menjadi “terkunci”) dan seluruh lembar berstatus final.
                    Sesi bisa dibuka kembali bila masih ada yang perlu diperbaiki.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
