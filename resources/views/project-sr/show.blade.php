<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="mb-4">
                <a href="{{ route('project-sr.index') }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Daftar Project</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">{{ $project->nama }}</h3>
                <p class="text-[13px] text-slate-500 mt-0.5">
                    {{ $project->grup->nama_grup ?? '-' }}@if($project->mentor) · mentor {{ $project->mentor->name }}@endif
                    @if($project->tanggal_mulai || $project->tanggal_selesai)
                        · {{ $project->tanggal_mulai?->format('d/m/Y') ?? '—' }} s.d. {{ $project->tanggal_selesai?->format('d/m/Y') ?? '—' }}
                    @endif
                </p>
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

            {{-- ===== RINGKAS ===== --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Status project</div>
                    <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ \App\Models\SrProject::STATUS[$project->status] ?? $project->status }}</div>
                    <div class="text-[11px] text-slate-400">tahap selesai {{ $project->progresTahap() }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Anggota</div>
                    <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ $anggota->count() }} siswa</div>
                    <div class="text-[11px] text-slate-400">{{ $jumlahDinilai }} sudah dinilai</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Nilai project</div>
                    @if($rataProject !== null)
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-[17px] font-extrabold text-slate-900">{{ $rataProject }}%</span>
                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat(\App\Models\PenilaianPengaturan::predikat($rataProject)) }}">
                                {{ \App\Models\PenilaianPengaturan::predikat($rataProject) }}
                            </span>
                        </div>
                        <div class="text-[11px] text-slate-400">rata-rata anggota</div>
                    @else
                        <div class="text-[13px] text-slate-300 mt-1.5">Belum ada nilai</div>
                    @endif
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Predikat</div>
                    <div class="text-[11.5px] text-slate-500 mt-1">A ≥ {{ $ambang['a'] }} · B ≥ {{ $ambang['b'] }} · C ≥ {{ $ambang['c'] }}</div>
                    <div class="text-[10.5px] text-slate-400 mt-0.5">diatur di Master Penilaian</div>
                </div>
            </div>

            @if($project->deskripsi)
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 mb-3 text-[12.5px] text-slate-600 leading-relaxed">
                    <span class="font-bold text-slate-700">Tema/tujuan:</span> {{ $project->deskripsi }}
                </div>
            @endif

            {{-- ===== PROGRES 5 TAHAP ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <div class="flex items-center justify-between gap-2 mb-2.5">
                    <h4 class="text-[13.5px] font-bold text-slate-800">🧭 Progres tahap</h4>
                    <span class="text-[11.5px] text-slate-400">bobot total {{ array_sum($bobotTerpakai) }}%</span>
                </div>

                <div class="space-y-2">
                    @foreach($tahap as $t)
                        @php
                            $s = $status[$t->id] ?? null;
                            $statusKini = $s->status ?? 'belum';
                            $dipakai = isset($bobotTerpakai[$t->id]);
                        @endphp
                        <div class="rounded-xl border {{ $statusKini === 'selesai' ? 'border-green-200 bg-green-50/50' : ($statusKini === 'berjalan' ? 'border-amber-200 bg-amber-50/40' : ($statusKini === 'tidak_dipakai' ? 'border-slate-200 bg-slate-50 opacity-70' : 'border-slate-200 bg-white')) }} p-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-[11px] font-bold text-slate-500">{{ $t->urutan }}</span>
                                <span class="text-[13px] font-bold text-slate-800">{{ $t->nama }}</span>
                                <span class="text-[11px] text-slate-400">bobot {{ $t->bobot }}%@unless($dipakai) · tidak dihitung @endunless</span>
                                <span class="ms-auto inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold
                                    {{ $statusKini === 'selesai' ? 'bg-green-100 text-green-700' : ($statusKini === 'berjalan' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ strtoupper(\App\Models\SrProjectTahap::STATUS_TAHAP[$statusKini] ?? $statusKini) }}
                                </span>
                            </div>

                            @if($t->panduan)
                                <div class="text-[11.5px] text-slate-500 mt-1 leading-snug">{{ $t->panduan }}</div>
                            @endif
                            @if($s && $s->catatan)
                                <div class="text-[11.5px] text-slate-600 mt-1">📝 {{ $s->catatan }}@if($s->tanggal) <span class="text-slate-400">({{ $s->tanggal->format('d/m/Y') }})</span>@endif</div>
                            @endif

                            @if($bolehNilai)
                                <form action="{{ route('project-sr.tahap', $project->id) }}" method="POST" class="mt-2 grid grid-cols-2 sm:grid-cols-12 gap-2">
                                    @csrf
                                    <input type="hidden" name="tahap_id" value="{{ $t->id }}">
                                    <div class="sm:col-span-3">
                                        <select name="status" class="w-full h-8 rounded-lg border border-slate-300 bg-white px-2 text-[12px] text-slate-700 focus:border-blue-500 outline-none">
                                            @foreach(\App\Models\SrProjectTahap::STATUS_TAHAP as $kunci => $label)
                                                <option value="{{ $kunci }}" @selected($statusKini === $kunci)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="sm:col-span-3">
                                        <input type="date" name="tanggal" value="{{ $s?->tanggal?->format('Y-m-d') }}"
                                               class="w-full h-8 rounded-lg border border-slate-300 bg-white px-2 text-[12px] text-slate-700 focus:border-blue-500 outline-none">
                                    </div>
                                    <div class="col-span-2 sm:col-span-4">
                                        <input type="text" name="catatan" value="{{ $s->catatan ?? '' }}" maxlength="255" placeholder="Catatan tahap (opsional)"
                                               class="w-full h-8 rounded-lg border border-slate-300 bg-white px-2.5 text-[12px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                    </div>
                                    <div class="col-span-2 sm:col-span-2 flex items-end">
                                        <button type="submit" class="w-full h-8 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[11.5px] font-semibold transition">Simpan</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== PENILAIAN PER SISWA ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <h4 class="text-[13.5px] font-bold text-slate-800">📝 Nilai per siswa (0–100)</h4>
                    <span class="text-[11.5px] text-slate-400">angka 1–{{ $tahap->count() }} = urutan tahap</span>
                </div>

                {{-- Legenda tahap --}}
                <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-500 mb-2.5">
                    @foreach($tahap as $t)
                        <span><strong class="text-slate-600">{{ $t->urutan }}.</strong> {{ $t->nama }} <span class="text-slate-400">{{ $t->bobot }}%</span></span>
                    @endforeach
                </div>

                @if($anggota->isEmpty())
                    <div class="text-[13px] text-slate-400 py-4 text-center">Grup ini belum punya anggota aktif.</div>
                @elseif(! $bolehNilai)
                    <div class="bg-slate-50 border border-slate-200 text-slate-600 p-3 rounded-xl text-[12.5px]">
                        Anda hanya bisa melihat. Project dibuat dan dinilai oleh mentor grup{{ $project->mentor ? ' (' . $project->mentor->name . ')' : '' }}.
                    </div>
                    <div class="mt-3 divide-y divide-slate-100">
                        @foreach($anggota as $a)
                            @php $n = $nilaiSiswa[$a->id] ?? null; @endphp
                            <div class="py-2 flex items-center justify-between gap-2">
                                <span class="text-[12.5px] text-slate-700 truncate">{{ $a->nama_lengkap }}</span>
                                @if($n && $n['rata'] !== null)
                                    <span class="inline-flex items-center gap-2 shrink-0">
                                        <span class="text-[13px] font-bold text-slate-800">{{ $n['rata'] }}%</span>
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-lg border text-[11.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($n['predikat']) }}">{{ $n['predikat'] }}</span>
                                    </span>
                                @else
                                    <span class="text-[12px] text-slate-400">Belum dinilai</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Bantuan: isi satu tahap untuk semua anggota --}}
                    <form action="{{ route('project-sr.nilaiRata', $project->id) }}" method="POST" class="flex flex-wrap items-end gap-2 mb-3 rounded-xl border border-slate-200 bg-slate-50/60 p-2.5">
                        @csrf
                        <div class="min-w-[140px] flex-1">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Isi cepat satu tahap</label>
                            <select name="tahap_id" class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                @foreach($tahap as $t)
                                    <option value="{{ $t->id }}">{{ $t->urutan }}. {{ $t->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nilai</label>
                            <input type="number" name="skor" min="0" max="100" value="80" required
                                   class="w-full h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                        </div>
                        <button type="submit" class="h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 text-[12px] font-semibold hover:bg-slate-100 transition whitespace-nowrap">Isi semua anggota</button>
                    </form>

                    <form action="{{ route('project-sr.simpanNilai', $project->id) }}" method="POST">
                        @csrf
                        {{-- Kepala kolom --}}
                        <div class="hidden sm:flex items-center gap-2 px-1 mb-1">
                            <span class="flex-1 text-[10.5px] font-black uppercase tracking-wider text-slate-400">Siswa</span>
                            @foreach($tahap as $t)
                                <span class="w-16 text-center text-[10.5px] font-black text-slate-400" title="{{ $t->nama }}">{{ $t->urutan }}</span>
                            @endforeach
                            <span class="w-20 text-right text-[10.5px] font-black uppercase tracking-wider text-slate-400">Hasil</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($anggota as $a)
                                @php $n = $nilaiSiswa[$a->id] ?? null; @endphp
                                <div class="py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-[12.5px] font-semibold text-slate-800 truncate">{{ $a->nama_lengkap }}</div>
                                            <div class="text-[10.5px] text-slate-400">{{ $a->kelas ?: '-' }}@if($n && ! ($n['lengkap'] ?? true)) · belum lengkap @endif</div>
                                        </div>
                                        @foreach($tahap as $t)
                                            <input type="number" min="0" max="100" inputmode="numeric"
                                                   name="skor[{{ $a->id }}][{{ $t->id }}]"
                                                   value="{{ $grid[$a->id][$t->id]['skor'] ?? '' }}"
                                                   class="w-16 h-9 rounded-lg border border-slate-300 bg-white px-1.5 text-center text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                                        @endforeach
                                        <div class="w-20 text-right shrink-0">
                                            @if($n && $n['rata'] !== null)
                                                <div class="inline-flex items-center gap-1.5">
                                                    <span class="text-[12.5px] font-bold text-slate-800">{{ $n['rata'] }}%</span>
                                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-md border text-[10.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($n['predikat']) }}">{{ $n['predikat'] }}</span>
                                                </div>
                                            @else
                                                <span class="text-[12px] text-slate-300">—</span>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="text" name="catatan[{{ $a->id }}]" maxlength="255"
                                           value="{{ collect($grid[$a->id] ?? [])->first()['catatan'] ?? '' }}"
                                           placeholder="Catatan untuk siswa ini (opsional)"
                                           class="mt-1.5 w-full h-8 rounded-lg border border-slate-200 bg-slate-50/60 px-2.5 text-[11.5px] text-slate-600 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="mt-3 inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                            💾 Simpan nilai project
                        </button>
                    </form>
                @endif
            </div>

            {{-- ===== UBAH / HAPUS PROJECT ===== --}}
            @if($bolehUbah)
                <details class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer px-4 py-3 text-[13px] font-bold text-slate-700 select-none">✏️ Ubah / hapus project</summary>
                    <div class="p-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                        <form action="{{ route('project-sr.update', $project->id) }}" method="POST" class="col-span-2 sm:col-span-10 grid grid-cols-2 sm:grid-cols-12 gap-2.5">
                            @csrf
                            @method('PUT')
                            <div class="col-span-2 sm:col-span-12">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Nama project *</label>
                                <input type="text" name="nama" value="{{ $project->nama }}" maxlength="120" required
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            </div>
                            <div class="col-span-2 sm:col-span-12">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tema / tujuan</label>
                                <textarea name="deskripsi" rows="2" maxlength="2000"
                                          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[13px] text-slate-700 focus:border-blue-500 outline-none">{{ $project->deskripsi }}</textarea>
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Grup</label>
                                <select name="grup_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                    @foreach(\App\Models\SrGroup::orderBy('nama_grup')->get() as $g)
                                        <option value="{{ $g->id }}" @selected($project->grup_id === $g->id)>{{ $g->nama_grup }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Mulai</label>
                                <input type="date" name="tanggal_mulai" value="{{ $project->tanggal_mulai?->format('Y-m-d') }}"
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Selesai</label>
                                <input type="date" name="tanggal_selesai" value="{{ $project->tanggal_selesai?->format('Y-m-d') }}"
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                                <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 focus:border-blue-500 outline-none">
                                    @foreach(\App\Models\SrProject::STATUS as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($project->status === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2 sm:col-span-2 flex items-end">
                                <button type="submit" class="w-full h-10 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-[12.5px] font-semibold transition">Simpan</button>
                            </div>
                        </form>

                        <form action="{{ route('project-sr.destroy', $project->id) }}" method="POST" class="col-span-2 sm:col-span-2 flex items-end"
                              onsubmit="return confirm('Hapus project {{ $project->nama }}? Project yang sudah punya nilai tidak bisa dihapus.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full h-10 rounded-lg border border-red-200 bg-red-50 text-red-700 text-[12.5px] font-semibold hover:bg-red-100 transition">Hapus</button>
                        </form>
                    </div>
                </details>
            @endif
        </div>
    </div>
</x-app-layout>
