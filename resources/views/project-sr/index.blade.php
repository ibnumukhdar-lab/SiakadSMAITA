<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-4">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📁 Project Student Root</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">
                        Penilaian project 5 tahap · jumlah project bebas per grup binaan
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($bolehTambah)
                        <span class="text-[11.5px] text-slate-400">Project dibuat &amp; dinilai oleh mentor grup</span>
                    @else
                        <span class="text-[11.5px] text-slate-400">Hanya bisa melihat · project dibuat &amp; dinilai oleh mentor masing-masing grup</span>
                    @endif
                    <a href="{{ route('project-sr.rekap') }}"
                       class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">📈 Rekap Project</a>
                    @if($bolehMaster)
                        <a href="{{ route('project-sr.master') }}"
                           class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12.5px] font-semibold hover:bg-slate-50 transition">⚙️ Tahap &amp; Bobot</a>
                    @endif
                </div>
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

            {{-- ===== CARA PENILAIAN ===== --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mb-3 rounded shadow-sm text-[12.5px] leading-relaxed">
                ℹ️ Setiap project dinilai dalam <strong>5 tahap</strong> (bobot total {{ $bobotTotal }}%):
                @foreach(\App\Models\SrProjectTahap::daftarAktif() as $t)
                    <strong>{{ $t->nama }}</strong> {{ $t->bobot }}%@if(!$loop->last),@endif
                @endforeach.
                Nilai diisi <strong>per siswa</strong> (0–100) pada tiap tahap, lalu dirata-ratakan berbobot menjadi nilai akhir anak
                (predikat A–D). Tahap yang tidak dipakai bisa ditandai "Tidak dipakai" — bobotnya otomatis dialihkan ke tahap lain.
            </div>

            {{-- ===== TAMBAH PROJECT ===== --}}
            @if($bolehTambah)
                <details class="mb-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer px-4 py-3 text-[13.5px] font-bold text-slate-800 select-none">➕ Tambah project baru</summary>
                    <form action="{{ route('project-sr.store') }}" method="POST" class="grid grid-cols-2 sm:grid-cols-12 gap-2.5 p-4 border-t border-slate-100">
                        @csrf
                        <div class="col-span-2 sm:col-span-12">
                            <p class="text-[11.5px] text-slate-500 mb-1">
                                Kamu bebas menentukan jumlah dan nama project untuk grup binaanmu — ketik langsung di bawah ini.
                            </p>
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Grup Student Root *</label>
                            <select name="grup_id" required class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">— pilih grup —</option>
                                @foreach($daftarGrup as $g)
                                    <option value="{{ $g->id }}" @selected(old('grup_id') === $g->id)>{{ $g->nama_grup }}@if($bolehSemua && $g->mentor) · {{ $g->mentor->name }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-8">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama project *</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" maxlength="120" required placeholder="Contoh: Bank Sampah Sekolah"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-12">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tema / tujuan project (opsional)</label>
                            <textarea name="deskripsi" rows="2" maxlength="2000" placeholder="Ringkasan singkat: masalah yang diangkat, target, dan bentuk kegiatannya"
                                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Selesai</label>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                                   class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                            <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                @foreach(\App\Models\SrProject::STATUS as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected(old('status', 'rencana') === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 sm:col-span-3 flex items-end">
                            <button type="submit" class="w-full h-10 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">Simpan project</button>
                        </div>
                    </form>
                </details>
            @endif

            {{-- ===== FILTER ===== --}}
            @if($bolehSemua || $daftarGrup->count() > 1)
                <form action="{{ route('project-sr.index') }}" method="GET"
                      class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 mb-3 grid grid-cols-2 sm:grid-cols-12 gap-2">
                    @if($bolehSemua)
                        <div class="col-span-2 sm:col-span-5">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Grup</label>
                            <select name="grup_id" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                <option value="">Semua grup</option>
                                @foreach($daftarGrup as $g)
                                    <option value="{{ $g->id }}" @selected($filter['grup_id'] === $g->id)>{{ $g->nama_grup }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="sm:col-span-3">
                        <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            <option value="">Semua</option>
                            @foreach(\App\Models\SrProject::STATUS as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($filter['status'] === $kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Cari project</label>
                        <input type="text" name="q" value="{{ $filter['q'] }}" placeholder="Nama project"
                               class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1 flex items-end">
                        <button type="submit" class="w-full h-9 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Filter</button>
                    </div>
                </form>
            @endif

            {{-- ===== DAFTAR PROJECT ===== --}}
            @forelse($projects as $p)
                @php $r = $ringkas[$p->id] ?? ['dinilai' => 0, 'anggota' => 0, 'rata' => null, 'progres' => '0/0']; @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-[14.5px] font-bold text-slate-900 truncate">{{ $p->nama }}</div>
                            <div class="text-[11.5px] text-slate-400 truncate">
                                {{ $p->grup->nama_grup ?? '-' }}@if($p->mentor) · mentor {{ $p->mentor->name }}@endif
                                @if($p->tanggal_mulai || $p->tanggal_selesai)
                                    · {{ $p->tanggal_mulai?->format('d/m/Y') ?? '—' }} s.d. {{ $p->tanggal_selesai?->format('d/m/Y') ?? '—' }}
                                @endif
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold
                            {{ $p->status === 'selesai' ? 'bg-green-50 text-green-700 border border-green-200' : ($p->status === 'berjalan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-50 text-slate-500 border border-slate-200') }}">
                            {{ strtoupper(\App\Models\SrProject::STATUS[$p->status] ?? $p->status) }}
                        </span>
                    </div>

                    @if($p->deskripsi)
                        <div class="text-[12px] text-slate-500 mt-1.5 line-clamp-2">{{ $p->deskripsi }}</div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3 mt-2.5 text-[11.5px] text-slate-500">
                        <span>Tahap selesai: <strong class="text-slate-700">{{ $r['progres'] }}</strong></span>
                        <span>Dinilai lengkap: <strong class="text-slate-700">{{ $r['dinilai'] }}/{{ $r['anggota'] }}</strong></span>
                        @if(($r['sebagian'] ?? 0) > 0)
                            <span class="text-amber-600 font-semibold">{{ $r['sebagian'] }} belum lengkap</span>
                        @endif
                        @if($r['rata'] !== null)
                            <span class="inline-flex items-center gap-1.5">
                                Nilai project: <strong class="text-slate-800">{{ $r['rata'] }}%</strong>
                                <span class="inline-flex items-center justify-center h-5 w-5 rounded-md border text-[10.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat(\App\Models\PenilaianPengaturan::predikat($r['rata'])) }}">
                                    {{ \App\Models\PenilaianPengaturan::predikat($r['rata']) }}
                                </span>
                            </span>
                        @else
                            <span class="text-slate-400">Belum ada nilai</span>
                        @endif
                        <a href="{{ route('project-sr.show', $p->id) }}"
                           class="ms-auto inline-flex items-center justify-center h-8 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12px] font-semibold transition whitespace-nowrap">Buka</a>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-10 text-center text-[13px] text-slate-400">
                    Belum ada project. {{ $bolehTambah ? 'Tambahkan project pertama grup Anda di atas.' : '' }}
                </div>
            @endforelse

            <div class="mt-3">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>
