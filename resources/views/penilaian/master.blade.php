@php
    // Bisa dibuka langsung ke tab tertentu: ?tab=periode (dipakai tautan dari halaman Sesi & Progres)
    $tabAwal = in_array(request()->input('tab'), ['pertanyaan', 'periode', 'ambang'], true)
        ? request()->input('tab')
        : 'pertanyaan';
@endphp

<x-app-layout>
    <div class="py-5 sm:py-8" x-data="{ tab: '{{ $tabAwal }}' }">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">⚙️ Pertanyaan &amp; Ambang</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Pertanyaan kuesioner, <strong>periode/semester penilaian</strong> (tab 📅 Periode), dan ambang predikat.
                    </p>
                </div>
                <a href="{{ route('penilaian.rekap') }}"
                   class="self-start inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📊 Rekap Nilai</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-3 rounded shadow-sm text-sm">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach($errors->all() as $pesan)<li>{{ $pesan }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ===== TAB ===== --}}
            <div class="flex gap-1.5 mb-3 overflow-x-auto">
                @foreach(['pertanyaan' => '📋 Pertanyaan', 'periode' => '📅 Periode', 'ambang' => '🏅 Ambang Predikat'] as $kunci => $label)
                    <button type="button" @click="tab = '{{ $kunci }}'"
                            :class="tab === '{{ $kunci }}' ? 'bg-blue-900 text-white border-blue-900' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50'"
                            class="inline-flex items-center justify-center h-9 px-3.5 rounded-lg border text-[12.5px] font-semibold transition whitespace-nowrap">{{ $label }}</button>
                @endforeach
            </div>

            {{-- ===================== PERTANYAAN ===================== --}}
            <div x-show="tab === 'pertanyaan'" x-cloak>
                @foreach(\App\Models\PenilaianKriteria::JENIS as $jenis => $labelJenis)
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h4 class="text-[14px] font-bold text-slate-800">
                                {{ $jenis === 'adab' ? '🕌' : '🛏️' }} Pertanyaan {{ $labelJenis }}
                            </h4>
                            <span class="text-[11.5px] text-slate-400">{{ ($kriteria[$jenis] ?? collect())->where('aktif', true)->count() }} aktif</span>
                        </div>

                        <details class="mb-3 rounded-xl border border-slate-200 bg-slate-50/60">
                            <summary class="cursor-pointer px-3 py-2.5 text-[12.5px] font-semibold text-slate-700 select-none">➕ Tambah pertanyaan {{ $labelJenis }}</summary>
                            <form action="{{ route('penilaian.master.kriteriaStore') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5 p-3 border-t border-slate-200">
                                @csrf
                                <input type="hidden" name="jenis" value="{{ $jenis }}">
                                <div class="col-span-2 sm:col-span-8">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Pertanyaan *</label>
                                    <input type="text" name="pertanyaan" maxlength="255" required placeholder="Contoh: Adab kepada guru & ustadz"
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Urutan</label>
                                    <input type="number" name="urutan" min="0" max="999" value="{{ ($kriteria[$jenis] ?? collect())->max('urutan') + 1 }}"
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                </div>
                                <div class="sm:col-span-2 flex items-end gap-2">
                                    <input type="hidden" name="aktif" value="0">
                                    <label class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-slate-600 mb-2.5">
                                        <input type="checkbox" name="aktif" value="1" checked class="rounded border-slate-300 text-blue-900 focus:ring-blue-200"> Aktif
                                    </label>
                                </div>
                                <div class="col-span-2 sm:col-span-11">
                                    <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Keterangan (opsional)</label>
                                    <input type="text" name="keterangan" maxlength="255" placeholder="Penjelasan singkat untuk penilai"
                                           class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                </div>
                                <div class="col-span-2 sm:col-span-1 flex items-end">
                                    <button type="submit" class="w-full h-10 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan</button>
                                </div>
                            </form>
                        </details>

                        <div class="divide-y divide-slate-100">
                            @forelse($kriteria[$jenis] ?? [] as $k)
                                @php $dipakai = (int) ($jumlahJawaban[$k->id] ?? 0); @endphp
                                <div class="py-2">
                                    <div class="flex items-start gap-2.5">
                                        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-slate-100 text-[10.5px] font-bold text-slate-500">{{ $k->urutan }}</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[13px] font-semibold text-slate-800">{{ $k->pertanyaan }}</div>
                                            <div class="text-[11px] text-slate-400">
                                                @if($k->keterangan) {{ $k->keterangan }} · @endif
                                                {{ $dipakai }} jawaban tersimpan
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold {{ $k->aktif ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                            {{ $k->aktif ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </div>

                                    <details class="mt-1.5 ms-7">
                                        <summary class="cursor-pointer text-[11.5px] font-semibold text-slate-500 hover:text-slate-800 select-none">Ubah / hapus</summary>
                                        <div class="mt-2 grid grid-cols-2 sm:grid-cols-12 gap-2">
                                            <form action="{{ route('penilaian.master.kriteriaUpdate', $k->id) }}" method="POST" class="col-span-2 sm:col-span-10 grid grid-cols-2 sm:grid-cols-12 gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="jenis" value="{{ $jenis }}">
                                                <div class="col-span-2 sm:col-span-7">
                                                    <input type="text" name="pertanyaan" value="{{ $k->pertanyaan }}" maxlength="255" required
                                                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input type="number" name="urutan" value="{{ $k->urutan }}" min="0" max="999"
                                                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                                </div>
                                                <div class="sm:col-span-3 flex items-center gap-1.5">
                                                    <input type="hidden" name="aktif" value="0">
                                                    <label class="inline-flex items-center gap-1 text-[12px] font-semibold text-slate-600">
                                                        <input type="checkbox" name="aktif" value="1" @checked($k->aktif) class="rounded border-slate-300 text-blue-900 focus:ring-blue-200"> Aktif
                                                    </label>
                                                </div>
                                                <div class="col-span-2 sm:col-span-10">
                                                    <input type="text" name="keterangan" value="{{ $k->keterangan }}" maxlength="255" placeholder="Keterangan (opsional)"
                                                           class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                                </div>
                                                <div class="col-span-2 sm:col-span-2 flex items-end">
                                                    <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12px] font-semibold transition">Simpan</button>
                                                </div>
                                            </form>

                                            <div class="col-span-2 sm:col-span-2 flex items-end">
                                                @if($dipakai === 0)
                                                    <form action="{{ route('penilaian.master.kriteriaDestroy', $k->id) }}" method="POST" class="w-full"
                                                          onsubmit="return confirm('Hapus pertanyaan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full h-9 rounded-lg border border-red-200 bg-red-50 text-red-700 text-[12px] font-semibold hover:bg-red-100 transition">Hapus</button>
                                                    </form>
                                                @else
                                                    <span class="w-full h-9 inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-[11px] font-semibold text-slate-400 text-center leading-tight">Sudah dipakai</span>
                                                @endif
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            @empty
                                <div class="py-4 text-[13px] text-slate-400">Belum ada pertanyaan {{ $labelJenis }}.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===================== PERIODE ===================== --}}
            <div x-show="tab === 'periode'" x-cloak>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5 mb-3">
                    <h4 class="text-[14px] font-bold text-slate-800 mb-1">📅 Tambah periode penilaian</h4>
                    <p class="text-[12px] text-slate-500 mb-3">
                        Di sinilah <strong>semester</strong> dibuat: satu periode = satu semester (mis. “Semester 1 2026/2027”, “Semester 2 2026/2027”).
                        Periode yang dibuat akan muncul di halaman <strong>Sesi &amp; Progres</strong>, Isi Rapor, Cetak Rapor, dan Rekap Nilai.
                    </p>
                    <form action="{{ route('penilaian.master.periodeStore') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                        @csrf
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama periode *</label>
                            <input type="text" name="nama" maxlength="60" required placeholder="Contoh: Semester 2 2026/2027"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tahun ajaran</label>
                            <input type="text" name="tahun_ajaran" maxlength="20" placeholder="2026/2027"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mulai</label>
                            <input type="date" name="tanggal_awal" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Selesai</label>
                            <input type="date" name="tanggal_akhir" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-1 flex items-end">
                            <label class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-slate-600 mb-2.5">
                                <input type="hidden" name="aktif" value="0">
                                <input type="checkbox" name="aktif" value="1" class="rounded border-slate-300 text-blue-900 focus:ring-blue-200"> Aktif
                            </label>
                        </div>
                        <div class="col-span-2 sm:col-span-11">
                            <input type="text" name="keterangan" maxlength="150" placeholder="Keterangan (opsional)"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex items-end">
                            <button type="submit" class="w-full h-10 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan</button>
                        </div>
                    </form>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60">
                        <h4 class="text-[13.5px] font-bold text-slate-800">Daftar periode</h4>
                    </div>
                    @forelse($periode as $p)
                        <div class="px-4 py-3 border-b border-slate-100 last:border-0">
                            <div class="flex items-center gap-2.5">
                                <div class="min-w-0 flex-1">
                                    <div class="text-[13.5px] font-bold text-slate-800">{{ $p->nama }}</div>
                                    <div class="text-[11.5px] text-slate-400">
                                        @if($p->tahun_ajaran) TA {{ $p->tahun_ajaran }} · @endif
                                        @if($p->tanggal_awal || $p->tanggal_akhir)
                                            {{ $p->tanggal_awal?->format('d/m/Y') ?? '—' }} s.d. {{ $p->tanggal_akhir?->format('d/m/Y') ?? '—' }} ·
                                        @endif
                                        {{ $p->sesi()->count() }} lembar penilaian
                                    </div>
                                </div>
                                @if($p->aktif)
                                    <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-900 border border-blue-200">AKTIF</span>
                                @else
                                    <form action="{{ route('penilaian.master.periodeAktifkan', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center h-7 px-2.5 rounded-lg border border-slate-300 bg-white text-slate-600 text-[11.5px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">Jadikan aktif</button>
                                    </form>
                                @endif
                            </div>

                            <details class="mt-1.5">
                                <summary class="cursor-pointer text-[11.5px] font-semibold text-slate-500 hover:text-slate-800 select-none">Ubah / hapus</summary>
                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-12 gap-2">
                                    <form action="{{ route('penilaian.master.periodeUpdate', $p->id) }}" method="POST" class="col-span-2 sm:col-span-10 grid grid-cols-2 sm:grid-cols-12 gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-span-2 sm:col-span-4">
                                            <input type="text" name="nama" value="{{ $p->nama }}" maxlength="60" required
                                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <input type="text" name="tahun_ajaran" value="{{ $p->tahun_ajaran }}" maxlength="20" placeholder="Tahun ajaran"
                                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <input type="date" name="tanggal_awal" value="{{ $p->tanggal_awal?->format('Y-m-d') }}"
                                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="sm:col-span-2">
                                            <input type="date" name="tanggal_akhir" value="{{ $p->tanggal_akhir?->format('Y-m-d') }}"
                                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="sm:col-span-1 flex items-center">
                                            <label class="inline-flex items-center gap-1 text-[12px] font-semibold text-slate-600">
                                                <input type="hidden" name="aktif" value="0">
                                                <input type="checkbox" name="aktif" value="1" @checked($p->aktif) class="rounded border-slate-300 text-blue-900 focus:ring-blue-200">
                                            </label>
                                        </div>
                                        <div class="col-span-2 sm:col-span-11">
                                            <input type="text" name="keterangan" value="{{ $p->keterangan }}" maxlength="150" placeholder="Keterangan (opsional)"
                                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="col-span-2 sm:col-span-1 flex items-end">
                                            <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12px] font-semibold transition">Simpan</button>
                                        </div>
                                    </form>
                                    <div class="col-span-2 sm:col-span-2 flex items-end">
                                        <form action="{{ route('penilaian.master.periodeDestroy', $p->id) }}" method="POST" class="w-full"
                                              onsubmit="return confirm('Hapus periode {{ $p->nama }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full h-9 rounded-lg border border-red-200 bg-red-50 text-red-700 text-[12px] font-semibold hover:bg-red-100 transition">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </details>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-[13px] text-slate-400">Belum ada periode penilaian.</div>
                    @endforelse
                </div>
            </div>

            {{-- ===================== AMBANG PREDIKAT ===================== --}}
            <div x-show="tab === 'ambang'" x-cloak>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5">
                    <h4 class="text-[14px] font-bold text-slate-800 mb-1">🏅 Ambang predikat</h4>
                    <p class="text-[12.5px] text-slate-500 mb-3">
                        Predikat dihitung dari persentase nilai akhir (jumlah skor ÷ (jumlah pertanyaan × 5) × 100).
                        Contoh sekarang: A ≥ {{ $ambang['a'] }}%, B ≥ {{ $ambang['b'] }}%, C ≥ {{ $ambang['c'] }}%, sisanya D.
                    </p>
                    <form action="{{ route('penilaian.master.ambangStore') }}" method="POST" class="grid grid-cols-3 sm:grid-cols-12 gap-2.5">
                        @csrf
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Predikat A ≥</label>
                            <input type="number" name="predikat_a" step="0.1" min="1" max="100" value="{{ $ambang['a'] }}" required
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Predikat B ≥</label>
                            <input type="number" name="predikat_b" step="0.1" min="1" max="100" value="{{ $ambang['b'] }}" required
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Predikat C ≥</label>
                            <input type="number" name="predikat_c" step="0.1" min="1" max="100" value="{{ $ambang['c'] }}" required
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-3 sm:col-span-3 flex items-end">
                            <button type="submit" class="w-full h-10 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan ambang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
