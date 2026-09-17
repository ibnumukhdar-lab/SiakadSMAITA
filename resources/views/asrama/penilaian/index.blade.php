<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📚 Histori Inspeksi Asrama</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Rekap sidak harian kamar terbersih &amp; perhatian ekstra</p>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-semibold">
                    ❌ {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3.5 rounded-xl text-sm">
                    <p class="font-bold mb-1.5">Ada yang perlu diperbaiki:</p>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($histori->isEmpty())
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum Ada Histori</h3>
                    <p class="text-sm text-slate-400">Data histori akan muncul setelah ada inspeksi kamar yang difinalisasi.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($histori as $h)
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                                <div class="text-sm font-bold text-slate-800">{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d M Y') }}</div>
                                @if($h->kategori == 'putra')
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Divisi Putra</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Divisi Putri</span>
                                @endif
                            </div>

                            <div class="p-5 space-y-4 flex-1">

                                <!-- Box Terbersih -->
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 mb-0.5">👑 Terbersih (+1)</div>
                                            <div class="text-sm font-bold text-emerald-800">{{ $h->kamarTerbersih->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                        </div>
                                        @if($h->foto_terbersih)
                                            <a href="{{ url('berkas/' . $h->foto_terbersih) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">📷 Lihat Foto</a>
                                        @endif
                                    </div>

                                    <div class="@if($h->foto_terbersih) border-t border-dashed border-emerald-200 pt-3 mt-3 @endif">
                                        <form action="{{ route('asrama.penilaian.update-foto', $h->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center justify-between gap-2">
                                            @csrf
                                            <input type="hidden" name="jenis" value="terbersih">
                                            <input type="file" name="foto" required accept="image/*" data-kompres class="text-xs text-emerald-800 file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-600 file:text-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:cursor-pointer cursor-pointer">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg {{ $h->foto_terbersih ? 'border border-emerald-300 bg-white text-emerald-700 hover:bg-emerald-50' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }} px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">
                                                {{ $h->foto_terbersih ? 'Ganti Foto' : 'Upload' }}
                                            </button>
                                            <span data-info-kompres class="basis-full text-[11px] font-medium text-emerald-700"></span>
                                        </form>
                                    </div>
                                </div>

                                <!-- Box Terkotor -->
                                <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-[11px] font-bold uppercase tracking-wider text-rose-700 mb-0.5">⚠️ Terkotor (-1)</div>
                                            <div class="text-sm font-bold text-rose-800">{{ $h->kamarTerkotor->nama_kamar ?? 'Kamar Dihapus' }}</div>
                                        </div>
                                        @if($h->foto_terkotor)
                                            <a href="{{ url('berkas/' . $h->foto_terkotor) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 px-2.5 py-1.5 text-xs font-bold transition whitespace-nowrap">📷 Lihat Foto</a>
                                        @endif
                                    </div>

                                    <div class="@if($h->foto_terkotor) border-t border-dashed border-rose-200 pt-3 mt-3 @endif">
                                        <form action="{{ route('asrama.penilaian.update-foto', $h->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center justify-between gap-2">
                                            @csrf
                                            <input type="hidden" name="jenis" value="terkotor">
                                            <input type="file" name="foto" required accept="image/*" data-kompres class="text-xs text-rose-800 file:mr-2 file:rounded-lg file:border-0 file:bg-rose-500 file:text-white file:px-3 file:py-1.5 file:text-xs file:font-bold file:cursor-pointer cursor-pointer">
                                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg {{ $h->foto_terkotor ? 'border border-rose-300 bg-white text-rose-700 hover:bg-rose-50' : 'bg-rose-500 hover:bg-rose-600 text-white' }} px-3 py-1.5 text-xs font-bold transition whitespace-nowrap">
                                                {{ $h->foto_terkotor ? 'Ganti Foto' : 'Upload' }}
                                            </button>
                                            <span data-info-kompres class="basis-full text-[11px] font-medium text-rose-700"></span>
                                        </form>
                                    </div>
                                </div>

                            </div>

                            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60 text-xs font-semibold text-slate-500 flex items-center justify-between">
                                <span>Petugas Inspeksi:</span>
                                <span class="text-blue-900 font-bold">{{ $h->musyrif->name ?? 'Admin' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    @include('partials.kompres-foto')
</x-app-layout>
