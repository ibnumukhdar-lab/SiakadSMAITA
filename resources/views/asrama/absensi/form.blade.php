<x-app-layout>
    <div class="py-8" x-data="{ semuaHadir() { document.querySelectorAll('select[name^=status]').forEach(s => s.value = 'hadir'); } }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📋 Absensi Kamar {{ $kamar->nama_kamar }}</h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        🌙 Absensi Malam (jam tidur) · {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
                        · {{ ucfirst($kamar->kategori) }} · Musyrif: {{ $kamar->musyrif->name ?? '-' }}
                    </p>
                </div>
                <a href="{{ route('asrama.absensi.index', ['tanggal' => $tanggal, 'kategori' => $kamar->kategori]) }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">⬅️ Daftar Kamar</a>
            </div>

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

            @if($anggota->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">🛏️</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Kamar ini belum punya penghuni aktif</h3>
                    <p class="text-sm text-slate-400">Tambahkan penghuni lewat menu Manajemen Kamar terlebih dahulu.</p>
                </div>
            @else
                <form action="{{ route('asrama.absensi.simpan') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kamar_id" value="{{ $kamar->id }}">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="sesi" value="{{ $sesi }}">

                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-[13px] font-semibold text-slate-500">
                                {{ $anggota->count() }} siswa
                                @if($sudahAda) · <span class="text-emerald-600">sudah pernah diisi, tinggal diperbarui</span> @endif
                            </div>
                            <button type="button" @click="semuaHadir()" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-white border border-slate-300 text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition">✅ Semua Hadir</button>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($anggota as $a)
                                @php
                                    $sid = $a->student_id;
                                    $status = old('status.' . $sid, $statusAwal[$sid] ?? 'hadir');
                                    $ket = old('keterangan.' . $sid, $keteranganAwal[$sid] ?? '');
                                    $adaIzin = isset($izin[$sid]);
                                @endphp
                                <div class="px-4 sm:px-5 py-3.5">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="min-w-[180px]">
                                            <div class="text-sm font-bold text-slate-800">{{ $a->student->nama_lengkap ?? 'Siswa Dihapus' }}</div>
                                            <div class="text-[12px] text-slate-500 mt-0.5">
                                                Kelas {{ $a->student->kelas ?? '-' }}
                                                @if($adaIzin)
                                                    · <span class="text-sky-700 font-semibold">izin {{ $izin[$sid]->labelJenis() }} s/d {{ \Carbon\Carbon::parse($izin[$sid]->sampai)->translatedFormat('d M Y') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <select name="status[{{ $sid }}]" class="h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                                @foreach(\App\Models\AsramaAbsensi::STATUS as $kunci => $label)
                                                    <option value="{{ $kunci }}" @selected($status === $kunci)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="keterangan[{{ $sid }}]" value="{{ $ket }}" maxlength="200" placeholder="keterangan (opsional)" class="w-52 h-9 rounded-lg border border-slate-300 bg-white px-2.5 text-[13px] text-slate-700 placeholder:text-slate-400 focus:border-blue-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[12px] text-slate-500 m-0">Bawaan setiap siswa adalah <strong>Hadir</strong> — cukup ubah yang tidak hadir.</p>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">💾 Simpan Absensi</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
