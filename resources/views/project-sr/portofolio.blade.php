<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">

            {{-- ===== KEPALA ===== --}}
            <div class="mb-4">
                <a href="{{ route('project-sr.portofolio.index') }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-slate-500 hover:text-slate-800 transition">← Penyusunan Portofolio</a>
                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight mt-1.5">🧾 Portofolio: {{ $project->nama }}</h3>
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

            {{-- ===== STATUS & AKSI CETAK ===== --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Tahap</div>
                    <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ $project->progresTahap() }} selesai</div>
                    <div class="text-[11px] text-slate-400">{{ $project->tuntas() ? 'project tuntas' : 'belum tuntas' }}</div>
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
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Foto/dokumen</div>
                    <div class="text-[15px] font-extrabold text-slate-900 mt-0.5">{{ $dokumen->count() }} berkas</div>
                    <div class="text-[11px] text-slate-400">minimal 1 untuk cetak</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3 flex flex-col justify-between">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400">Cetak</div>
                    @if($dokumen->count() > 0)
                        <a href="{{ route('project-sr.portofolio.cetak', $project->id) }}" target="_blank"
                           class="mt-1 inline-flex items-center justify-center h-9 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[12.5px] font-semibold transition">🖨️ Cetak Portofolio</a>
                    @else
                        <div class="mt-1">
                            <span class="inline-flex items-center justify-center h-9 px-3 rounded-lg bg-slate-100 text-slate-400 text-[12px] font-semibold w-full cursor-not-allowed">🖨️ Cetak Portofolio</span>
                            <div class="text-[10.5px] text-amber-600 mt-1">unggah foto dulu</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== REKAP 5 TAHAP ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <h4 class="text-[13.5px] font-bold text-slate-800 mb-2.5">🧭 Rekap 5 tahap</h4>

                <div class="space-y-2">
                    @foreach($tahap as $t)
                        @php
                            $s = $status[$t->id] ?? null;
                            $statusKini = $s->status ?? 'belum';
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-2.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-white border border-slate-200 text-[11px] font-bold text-slate-500">{{ $t->urutan }}</span>
                                <span class="text-[13px] font-bold text-slate-800">{{ $t->nama }}</span>
                                <span class="text-[11px] text-slate-400">bobot {{ $t->bobot }}%</span>
                                <span class="ms-auto inline-flex items-center gap-2">
                                    @if($tahapRata[$t->id] !== null)
                                        <span class="text-[12px] font-bold text-slate-700">rata {{ $tahapRata[$t->id] }}%</span>
                                    @endif
                                    <span class="inline-flex items-center h-6 px-2 rounded-full text-[10.5px] font-bold
                                        {{ $statusKini === 'selesai' ? 'bg-green-100 text-green-700' : ($statusKini === 'berjalan' ? 'bg-amber-100 text-amber-700' : ($statusKini === 'tidak_dipakai' ? 'bg-slate-200 text-slate-500' : 'bg-slate-100 text-slate-500')) }}">
                                        {{ strtoupper(\App\Models\SrProjectTahap::STATUS_TAHAP[$statusKini] ?? $statusKini) }}
                                    </span>
                                </span>
                            </div>
                            <div class="text-[11.5px] text-slate-500 mt-1">
                                @if($s?->tanggal) 📅 {{ $s->tanggal->format('d/m/Y') }} · @endif
                                {{ $s->catatan ?? 'Tanpa catatan' }}
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($portofolio->tempat || $portofolio->tanggal_presentasi)
                    <div class="text-[12px] text-slate-600 mt-2">
                        📍 Presentasi publik: {{ $portofolio->tempat ?: '-' }}@if($portofolio->tanggal_presentasi) · {{ $portofolio->tanggal_presentasi->format('d/m/Y') }}@endif
                    </div>
                @endif
            </div>

            {{-- ===== NILAI PER SISWA ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <h4 class="text-[13.5px] font-bold text-slate-800 mb-2.5">👥 Nilai anggota ({{ $anggota->count() }} siswa)</h4>

                <div class="hidden sm:flex items-center gap-2 px-1 mb-1">
                    <span class="flex-1 text-[10.5px] font-black uppercase tracking-wider text-slate-400">Siswa</span>
                    @foreach($tahap as $t)
                        <span class="w-12 text-center text-[10.5px] font-black text-slate-400" title="{{ $t->nama }}">{{ $t->urutan }}</span>
                    @endforeach
                    <span class="w-20 text-right text-[10.5px] font-black uppercase tracking-wider text-slate-400">Nilai</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($anggota as $a)
                        @php $n = $nilaiSiswa[$a->id] ?? null; @endphp
                        <div class="py-1.5 flex items-center gap-2">
                            <span class="min-w-0 flex-1 text-[12.5px] text-slate-700 truncate">{{ $a->nama_lengkap }}</span>
                            @foreach($tahap as $t)
                                @php $skor = $n['per_tahap'][$t->id] ?? null; @endphp
                                <span class="w-12 text-center text-[12px] font-semibold {{ $skor === null ? 'text-slate-300' : 'text-slate-700' }}">{{ $skor ?? '—' }}</span>
                            @endforeach
                            <span class="w-20 text-right">
                                @if($n && $n['rata'] !== null)
                                    <span class="inline-flex items-center gap-1.5 justify-end">
                                        <span class="text-[12.5px] font-bold text-slate-800">{{ $n['rata'] }}%</span>
                                        <span class="inline-flex items-center justify-center h-5 w-5 rounded-md border text-[10.5px] font-black {{ \App\Models\PenilaianPengaturan::warnaPredikat($n['predikat']) }}">{{ $n['predikat'] }}</span>
                                    </span>
                                @else
                                    <span class="text-[12px] text-slate-300">—</span>
                                @endif
                            </span>
                        </div>
                    @empty
                        <div class="py-4 text-center text-[13px] text-slate-400">Grup ini belum punya anggota aktif.</div>
                    @endforelse
                </div>
            </div>

            {{-- ===== FOTO / DOKUMENTASI ===== --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4 mb-3">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2.5">
                    <h4 class="text-[13.5px] font-bold text-slate-800">📸 Foto &amp; dokumentasi</h4>
                    <span class="text-[11.5px] text-slate-400">{{ $dokumen->count() }} berkas · jpg/png/webp/gif/pdf, maks 5 MB</span>
                </div>

                @if($dokumen->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 px-4 py-6 text-center text-[12.5px] text-slate-400">
                        Belum ada foto. Unggah lewat formulir di bawah — setelah ada minimal satu foto, opsi <strong>Cetak Portofolio</strong> aktif.
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($dokumen as $d)
                            <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                                @if(Str::endsWith(strtolower((string) $d->nama_asli), '.pdf'))
                                    <a href="{{ $d->url }}" target="_blank" class="flex items-center justify-center h-28 text-[12.5px] font-semibold text-slate-600 hover:bg-slate-100 transition">📄 Lihat PDF</a>
                                @else
                                    <a href="{{ $d->url }}" target="_blank">
                                        <img src="{{ $d->url }}" alt="{{ $d->keterangan ?: $project->nama }}" loading="lazy" class="w-full h-28 object-cover">
                                    </a>
                                @endif
                                <div class="p-2">
                                    <div class="text-[11px] text-slate-600 leading-snug line-clamp-2">{{ $d->keterangan ?: ($d->nama_asli ?: 'Tanpa keterangan') }}</div>
                                    @if($bolehSusun)
                                        <form action="{{ route('project-sr.portofolio.dokumenHapus', [$project->id, $d->id]) }}" method="POST" class="mt-1.5"
                                              onsubmit="return confirm('Hapus berkas ini dari portofolio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] font-semibold text-red-600 hover:underline">Hapus berkas</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ===== FORM PENYUSUNAN ===== --}}
            @if($bolehSusun)
                <form action="{{ route('project-sr.portofolio.simpan', $project->id) }}" method="POST" enctype="multipart/form-data"
                      class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-4">
                    @csrf

                    <h4 class="text-[13.5px] font-bold text-slate-800 mb-1">✍️ Narasi portofolio</h4>
                    <p class="text-[11.5px] text-slate-500 mb-3">
                        Tulis ringkas dan jelas — teks ini yang tercetak di lembar portofolio
                        (terisi {{ $portofolio->bagianTerisi() }}/6 bagian{{ $portofolio->diselesaikan_pada ? ', ditandai selesai ' . $portofolio->diselesaikan_pada->format('d/m/Y') : '' }}).
                    </p>

                    <div class="space-y-2.5">
                        @foreach([
                            'ringkasan' => 'Ringkasan project',
                            'latar_belakang' => 'Latar belakang (hasil observasi)',
                            'tujuan' => 'Tujuan & sasaran',
                            'pelaksanaan' => 'Pelaksanaan (perencanaan, perancangan, validasi ahli)',
                            'hasil' => 'Hasil / karya yang dihasilkan',
                            'refleksi' => 'Refleksi & tindak lanjut',
                        ] as $kolom => $label)
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">{{ $label }}</label>
                                <textarea name="{{ $kolom }}" rows="2" maxlength="3000" placeholder="Tulis di sini..."
                                          class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">{{ old($kolom, $portofolio->{$kolom}) }}</textarea>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tempat presentasi publik</label>
                                <input type="text" name="tempat" value="{{ old('tempat', $portofolio->tempat) }}" maxlength="100" placeholder="Contoh: Aula sekolah"
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10.5px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal presentasi</label>
                                <input type="date" name="tanggal_presentasi" value="{{ old('tanggal_presentasi', $portofolio->tanggal_presentasi?->format('Y-m-d')) }}"
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[12.5px] text-slate-700 focus:border-blue-500 outline-none">
                            </div>
                        </div>
                    </div>

                    <h4 class="text-[13.5px] font-bold text-slate-800 mt-4 mb-1">📎 Unggah foto / dokumentasi</h4>
                    <p class="text-[11.5px] text-slate-500 mb-2">Bisa pilih beberapa berkas sekaligus. Tambahkan keterangan bila perlu (mis. “proses perancangan, 12 Sep”).</p>

                    <div class="space-y-2">
                        @for($i = 0; $i < 5; $i++)
                            <div class="grid grid-cols-2 gap-2">
                                <input type="file" name="foto[]" accept="image/*,application/pdf"
                                       class="w-full text-[12px] text-slate-600 file:me-2 file:h-9 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-[12px] file:font-semibold">
                                <input type="text" name="keterangan_foto[]" maxlength="255" placeholder="Keterangan berkas (opsional)"
                                       class="w-full h-10 rounded-lg border border-slate-300 bg-white px-2.5 text-[12px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                            </div>
                        @endfor
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-4">
                        <button type="submit" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">
                            💾 Simpan portofolio
                        </button>
                        @if($dokumen->count() > 0)
                            <a href="{{ route('project-sr.portofolio.cetak', $project->id) }}" target="_blank"
                               class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">🖨️ Cetak portofolio</a>
                        @endif
                    </div>
                </form>
            @else
                <div class="bg-slate-50 border border-slate-200 text-slate-600 p-3.5 rounded-2xl text-[12.5px]">
                    Anda hanya bisa melihat. Portofolio disusun oleh mentor grup{{ $project->mentor ? ' (' . $project->mentor->name . ')' : '' }}.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
