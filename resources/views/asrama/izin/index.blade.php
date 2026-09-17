<x-app-layout>
    @php $bolehSetujui = auth()->user()->can('buka-menu-manajemen-kamar'); @endphp
    <div class="py-8">
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🚪 Izin Pulang / Keluar Santri</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Pengajuan izin, persetujuan Kepala Diniyah, dan catatan kepulangan</p>
                </div>
                <a href="{{ route('asrama.izin.create') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold shadow-sm transition whitespace-nowrap">➕ Ajukan Izin</a>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <p class="font-bold mb-1">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">⏳ Menunggu</div>
                    <div class="text-xl font-extrabold text-amber-600 mt-0.5">{{ $ringkasan['diajukan'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">🚪 Di Luar</div>
                    <div class="text-xl font-extrabold text-sky-700 mt-0.5">{{ $ringkasan['disetujui'] }}</div>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">⚠️ Belum Kembali</div>
                    <div class="text-xl font-extrabold {{ $ringkasan['terlambat'] > 0 ? 'text-rose-600' : 'text-slate-400' }} mt-0.5">{{ $ringkasan['terlambat'] }}</div>
                </div>
            </div>

            @if($terlambat->isNotEmpty())
                <div class="mb-5 bg-rose-50 border border-rose-200 rounded-2xl p-4">
                    <h4 class="text-[14px] font-bold text-rose-800 mb-2">⚠️ Sudah lewat tanggal kembali, belum dicatat pulang</h4>
                    <div class="space-y-2">
                        @foreach($terlambat as $t)
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-white/70 rounded-xl px-3 py-2">
                                <div class="text-[13px] font-semibold text-slate-700">
                                    {{ $t->student->nama_lengkap ?? 'Siswa' }} · {{ $t->kamar->nama_kamar ?? '-' }}
                                    <span class="text-slate-500 font-medium">· wajib kembali {{ \Carbon\Carbon::parse($t->sampai)->translatedFormat('d M Y') }} pukul {{ $t->jamWajibKembaliText() }}</span>
                                    <span class="ms-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200">terlambat {{ $t->menitTerlambat() }} menit</span>
                                </div>
                                @can('buka-menu-manajemen-kamar')
                                    <form action="{{ route('asrama.izin.kembali', $t->id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="datetime-local" name="kembali_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="h-8 rounded-lg border border-slate-300 px-2 text-[12px] text-slate-700">
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">Catat pulang</button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($diLuar->isNotEmpty())
                <div class="mb-5 bg-white border border-sky-200 rounded-2xl p-4">
                    <h4 class="text-[14px] font-bold text-sky-800 mb-2">🚪 Sedang di luar asrama hari ini</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($diLuar as $d)
                            <div class="bg-sky-50/60 border border-sky-200 rounded-xl px-3 py-2">
                                <div class="text-[13px] font-bold text-slate-800">{{ $d->student->nama_lengkap ?? 'Siswa' }}</div>
                                <div class="text-[12px] text-slate-500">{{ $d->kamar->nama_kamar ?? '-' }} · {{ $d->labelJenis() }}</div>
                                <div class="text-[12px] text-sky-800 font-semibold">🕒 {{ $d->labelWaktu() }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Saring --}}
            <form method="GET" action="{{ route('asrama.izin.index') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Cari Nama</label>
                        <input type="text" name="q" value="{{ $q }}" placeholder="nama siswa" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                        <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua status</option>
                            @foreach(\App\Models\AsramaIzin::STATUS as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($status === $kunci)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Saring</button>
                </div>
            </form>

            @if($daftar->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum ada pengajuan izin</h3>
                    <p class="text-sm text-slate-400">Tekan “Ajukan Izin” untuk mencatat santri yang pulang / keluar.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($daftar as $izin)
                        @php
                            $warnaStatus = match ($izin->status) {
                                'diajukan'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                'disetujui' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'selesai'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                default     => 'bg-rose-50 text-rose-700 border-rose-200',
                            };
                        @endphp
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-extrabold text-slate-900">{{ $izin->student->nama_lengkap ?? 'Siswa' }}
                                        <span class="text-slate-400 font-medium text-[12px]">· {{ $izin->student->kelas ?? '-' }} · {{ $izin->kamar->nama_kamar ?? '-' }}</span>
                                    </div>
                                    <div class="text-[13px] text-slate-600 font-semibold mt-1">
                                        {{ $izin->labelJenis() }} · {{ \Carbon\Carbon::parse($izin->mulai)->translatedFormat('d M Y') }} → {{ \Carbon\Carbon::parse($izin->sampai)->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[12px] text-blue-900 font-bold mt-0.5">🕒 {{ $izin->labelWaktu() }}</div>
                                    <div class="text-[13px] text-slate-500 mt-1">{{ $izin->alasan }}</div>
                                    @if($izin->tujuan)
                                        <div class="text-[12px] text-slate-400 mt-0.5">Tujuan: {{ $izin->tujuan }}</div>
                                    @endif
                                    @if($izin->penanggung_jawab)
                                        <div class="text-[12px] text-slate-400 mt-0.5">Penanggung jawab: {{ $izin->penanggung_jawab }}</div>
                                    @endif
                                    @if($izin->catatan_penolakan)
                                        <div class="text-[12px] text-rose-600 mt-1 font-semibold">Alasan ditolak: {{ $izin->catatan_penolakan }}</div>
                                    @endif
                                    @if($izin->kembali_at || $izin->kembali_pada)
                                        <div class="text-[12px] mt-1 font-semibold">
                                            <span class="text-emerald-700">Kembali {{ \Carbon\Carbon::parse($izin->kembali_at ?? $izin->kembali_pada)->translatedFormat('d M Y') }}
                                                pukul {{ \Carbon\Carbon::parse($izin->kembali_at ?? $izin->kembali_pada)->format('H:i') }}</span>
                                            @if((int) $izin->terlambat_menit > 0)
                                                <span class="ms-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">⚠️ terlambat {{ (int) $izin->terlambat_menit }} menit</span>
                                            @else
                                                <span class="ms-1 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">tepat waktu</span>
                                            @endif
                                            @if($izin->catatan_kembali) <span class="text-slate-400 font-medium">· {{ $izin->catatan_kembali }}</span> @endif
                                        </div>
                                    @endif
                                    <div class="text-[11px] text-slate-400 mt-1.5">
                                        Diajukan: {{ $izin->pengaju->name ?? '-' }} · Disetujui: {{ $izin->penyetuju->name ?? '-' }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold border {{ $warnaStatus }}">{{ $izin->labelStatus() }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                                <a href="{{ route('asrama.izin.cetak', $izin->id) }}" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-[13px] font-semibold transition">🖨️ Surat Izin</a>

                                @if($izin->status === 'diajukan' && $bolehSetujui)
                                    <form action="{{ route('asrama.izin.setujui', $izin->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold transition">✅ Setujui</button>
                                    </form>

                                    <details class="m-0">
                                        <summary class="cursor-pointer inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 text-[13px] font-semibold transition list-none">❌ Tolak</summary>
                                        <form action="{{ route('asrama.izin.tolak', $izin->id) }}" method="POST" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="text" name="catatan_penolakan" required maxlength="500" placeholder="alasan penolakan" class="w-64 h-9 rounded-lg border border-slate-300 px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-rose-400 outline-none">
                                            <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-[13px] font-semibold transition">Kirim penolakan</button>
                                        </form>
                                    </details>
                                @endif

                                @if($izin->status === 'disetujui')
                                    <details class="m-0">
                                        <summary class="cursor-pointer inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-blue-900 text-white text-[13px] font-semibold transition list-none">🏠 Catat Kembali</summary>
                                        <form action="{{ route('asrama.izin.kembali', $izin->id) }}" method="POST" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="datetime-local" name="kembali_at" value="{{ now()->format('Y-m-d\TH:i') }}" required class="h-9 rounded-lg border border-slate-300 px-2.5 text-[13px] text-slate-700">
                                            <input type="text" name="catatan_kembali" maxlength="500" placeholder="catatan (opsional)" class="w-52 h-9 rounded-lg border border-slate-300 px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400">
                                            <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-[13px] font-semibold transition">Simpan</button>
                                        </form>
                                    </details>
                                @endif

                                @if(in_array($izin->status, ['diajukan', 'ditolak'], true))
                                    <form action="{{ route('asrama.izin.destroy', $izin->id) }}" method="POST" class="m-0" onsubmit="return confirm('Batalkan / hapus pengajuan izin ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-500 hover:bg-slate-50 text-[13px] font-semibold transition">🗑️ Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5">
                    {{ $daftar->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
