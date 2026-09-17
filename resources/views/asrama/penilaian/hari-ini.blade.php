<x-app-layout>
    @php
        // Catatan: JANGAN pakai "use ... as ..." di dalam @php (Blade menaruhnya di tengah file
        // sehingga muncul "syntax error, unexpected token use") — pakai nama kelas lengkap.
        $bolehIsiUmum = auth()->user()->can('buka-menu-manajemen-kamar');
    @endphp

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📝 Inspeksi Kebersihan Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Dua sesi sehari · {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</p>
                </div>
                <a href="{{ route('asrama.penilaian.index') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">📚 Histori Inspeksi</a>
            </div>

            @if(session('success')) <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">✅ {{ session('success') }}</div> @endif
            @if(session('error')) <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">❌ {{ session('error') }}</div> @endif
            @if($errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <p class="font-bold mb-1">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- Jadwal sesi --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-5 py-4 mb-5">
                <div class="text-[13px] font-bold text-slate-700 mb-2">🕒 Jadwal penilaian</div>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Http\Controllers\AsramaPenilaianController::SESI as $kunci => $s)
                        @php $terbukaSekarang = \App\Http\Controllers\AsramaPenilaianController::dalamJendela($kunci); @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-bold border {{ $terbukaSekarang ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                            {{ $s['ikon'] }} {{ $s['label'] }} · {{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi($kunci) }}{{ $terbukaSekarang ? ' · dibuka sekarang' : '' }}
                        </span>
                    @endforeach
                </div>
                @if(! $sesiSekarang)
                    <p class="text-[12px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3 mb-0 font-medium">
                        Sekarang di luar jam sesi. Musyrif mengisi pada jam sesinya ({{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi('pagi') }} / {{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi('sore') }});
                        Kepala Diniyah & Super Admin tetap bisa mengisi kapan saja bila perlu perbaikan.
                    </p>
                @endif
            </div>

            @foreach(\App\Http\Controllers\AsramaPenilaianController::SESI as $sesi => $info)
                @php
                    $dataDivisi = ['putra' => $lembar[$sesi]['putra'], 'putri' => $lembar[$sesi]['putri']];
                    $terbuka = \App\Http\Controllers\AsramaPenilaianController::dalamJendela($sesi);
                    $bolehIsi = $bolehIsiUmum || $terbuka;
                    $selesaiSesi = $dataDivisi['putra']['draft']->status === 'final' && $dataDivisi['putri']['draft']->status === 'final';
                @endphp

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h4 class="text-[15px] font-extrabold text-slate-800">{{ $info['ikon'] }} {{ $info['label'] }}</h4>
                            <p class="text-[12px] text-slate-500 mt-0.5">Jam penilaian {{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi($sesi) }}</p>
                        </div>
                        @if($selesaiSesi)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ Selesai difinalisasi</span>
                        @elseif($terbuka)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">🟢 Terbuka sekarang</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">🔒 Di luar jam sesi</span>
                        @endif
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($dataDivisi as $divisi => $data)
                            @php
                                $jumlahKamar = $data['kamar']->count();
                                $sudahDinilai = count($data['dinilai']);
                                $persenProgres = $jumlahKamar > 0 ? (int) round($sudahDinilai / $jumlahKamar * 100) : 0;
                                $warnaLatar = $divisi === 'putra' ? 'bg-blue-600' : 'bg-pink-500';
                            @endphp
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                    <h5 class="text-[14px] font-bold text-slate-800 m-0">{{ $divisi === 'putra' ? '👦 Kamar Putra (Ikhwan)' : '👧 Kamar Putri (Akhwat)' }}</h5>

                                    @if($jumlahKamar > 0 && $data['draft']->status !== 'final' && $sudahDinilai > 0)
                                        <form action="{{ route('asrama.penilaian.reset') }}" method="POST" class="m-0" onsubmit="return confirm('Reset penilaian {{ $info['label'] }} divisi {{ ucfirst($divisi) }} hari ini? Skor yang sudah diisi akan dihapus.');">
                                            @csrf
                                            <input type="hidden" name="kategori" value="{{ $divisi }}">
                                            <input type="hidden" name="sesi" value="{{ $sesi }}">
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">🔄 Reset {{ $info['label'] }}</button>
                                        </form>
                                    @endif
                                </div>

                                @if($jumlahKamar === 0)
                                    <p class="text-sm text-slate-500 italic m-0">Belum ada data kamar {{ ucfirst($divisi) }}.</p>
                                @elseif($data['draft']->status === 'final')
                                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-[13px] font-bold rounded-xl px-4 py-3">
                                        ✅ {{ $info['label'] }} divisi {{ ucfirst($divisi) }} sudah difinalisasi.
                                    </div>
                                @else
                                    <div class="flex items-center justify-between text-[12px] font-bold text-slate-600 mb-2">
                                        <span>Progress penilaian</span>
                                        <span>{{ $sudahDinilai }} / {{ $jumlahKamar }} kamar</span>
                                    </div>
                                    <div class="h-2.5 bg-slate-100 rounded-full overflow-hidden mb-4">
                                        <div class="h-full {{ $warnaLatar }} rounded-full transition-all" style="width: {{ $persenProgres }}%;"></div>
                                    </div>

                                    @if($data['selesai'])
                                        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-center">
                                            <h6 class="text-[13px] font-extrabold text-emerald-800 mb-1">🪄 Seluruh kamar {{ ucfirst($divisi) }} sudah dinilai pada {{ $info['label'] }}</h6>
                                            <p class="text-[12px] font-semibold text-emerald-700 mb-3">Lanjut menentukan kamar terbersih &amp; terkotor untuk sesi ini.</p>
                                            <a href="{{ route('asrama.penilaian.konfirmasi', ['kategori' => $divisi, 'sesi' => $sesi]) }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                                Lanjut Penentuan Juara ➡️
                                            </a>
                                        </div>
                                    @endif

                                    @if(! $bolehIsi)
                                        <p class="text-[12px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3 font-medium">
                                            {{ \App\Http\Controllers\AsramaPenilaianController::labelSesi($sesi) }} hanya bisa diisi pada jam {{ \App\Http\Controllers\AsramaPenilaianController::jendelaSesi($sesi) }}.
                                        </p>
                                    @endif

                                    <div class="space-y-3">
                                        @foreach($data['kamar'] as $kamar)
                                            @php
                                                $sudah = in_array($kamar->id, $data['dinilai']);
                                                $nilai = $data['skor'][$kamar->id] ?? null;
                                            @endphp
                                            <div x-data="{ openModal: false }">
                                                <div class="rounded-xl border p-4 flex flex-wrap items-center justify-between gap-3 transition {{ $sudah ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-slate-50/60' }}">
                                                    <div>
                                                        <h6 class="text-[15px] font-extrabold text-slate-800 uppercase leading-tight">{{ $kamar->nama_kamar }}</h6>
                                                        <p class="text-xs font-semibold text-slate-500 mt-0.5">
                                                            Musyrif: {{ $kamar->musyrif->name ?? '-' }}
                                                            @if($nilai) · nilai {{ $nilai->total_skor }}/25 ({{ \App\Http\Controllers\AsramaPenilaianController::persenSkor($nilai->total_skor) }}%) @endif
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if($sudah)
                                                            <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">✅ Dinilai</span>
                                                        @endif
                                                        @if($bolehIsi)
                                                            <button type="button" @click="openModal = true" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg {{ $sudah ? 'border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-blue-900 hover:bg-blue-800 text-white' }} text-xs font-bold shadow-sm transition whitespace-nowrap">{{ $sudah ? '✏️ Koreksi' : 'Beri Nilai' }}</button>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($bolehIsi)
                                                    @include('asrama.penilaian._form_modal', ['sesi' => $sesi, 'nilai' => $nilai])
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
