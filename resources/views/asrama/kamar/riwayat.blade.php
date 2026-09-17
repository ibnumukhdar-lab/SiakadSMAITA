<x-app-layout>
    <div class="py-8">
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">📜 Riwayat Mutasi Penghuni</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Catatan masuk, keluar, dan pindah kamar — bukti serah terima antar musyrif</p>
                </div>
                <a href="{{ route('asrama.kamar.index') }}" class="inline-flex items-center justify-center gap-1.5 h-9 px-3 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-[13px] font-semibold shadow-sm transition whitespace-nowrap">⬅️ Daftar Kamar</a>
            </div>

            <form method="GET" action="{{ route('asrama.kamar.riwayat') }}" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 mb-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Bulan</label>
                        <input type="month" name="bulan" value="{{ $bulan }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kamar</label>
                        <select name="kamar_id" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="">Semua kamar</option>
                            @foreach($kamarList as $k)
                                <option value="{{ $k->id }}" @selected($kamarId === (int) $k->id)>{{ $k->nama_kamar }} ({{ ucfirst($k->kategori) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Peristiwa</label>
                        <select name="jenis" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                            <option value="semua" @selected($jenis === 'semua')>Masuk & keluar</option>
                            <option value="masuk" @selected($jenis === 'masuk')>Hanya masuk</option>
                            <option value="keluar" @selected($jenis === 'keluar')>Hanya keluar/pindah</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Saring</button>
                        <a href="{{ route('asrama.kamar.riwayat') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-3 rounded-lg bg-white text-slate-600 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Reset</a>
                    </div>
                </div>
            </form>

            @if(empty($peristiwa))
                <div class="text-center p-12 bg-white border border-dashed border-slate-300 rounded-2xl">
                    <span class="text-5xl block mb-3">📭</span>
                    <h3 class="text-lg font-extrabold text-slate-600 mb-1">Belum ada catatan mutasi</h3>
                    <p class="text-sm text-slate-400">Catatan akan muncul setelah ada siswa dimasukkan atau dikeluarkan dari kamar.</p>
                </div>
            @else
                {{-- Tabel di layar lebar --}}
                <div class="hidden md:block bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[820px]">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200">
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Peristiwa</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Siswa</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kamar</th>
                                    <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peristiwa as $ev)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($ev['tanggal'])->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if($ev['jenis'] === 'masuk')
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Masuk</span>
                                            @elseif($ev['jenis'] === 'pindah')
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Pindah</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Keluar</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-slate-800">{{ $ev['siswa'] }} <span class="text-slate-400 font-medium">· {{ $ev['kelas'] ?? '-' }}</span></td>
                                        <td class="px-4 py-3 text-sm text-slate-600 font-medium">{{ $ev['kamar'] }}</td>
                                        <td class="px-4 py-3 text-[13px] text-slate-500">{{ $ev['catatan'] ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Kartu di HP --}}
                <div class="md:hidden space-y-2.5">
                    @foreach($peristiwa as $ev)
                        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[12px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($ev['tanggal'])->translatedFormat('d M Y') }}</span>
                                @if($ev['jenis'] === 'masuk')
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Masuk</span>
                                @elseif($ev['jenis'] === 'pindah')
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Pindah</span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Keluar</span>
                                @endif
                            </div>
                            <div class="text-sm font-bold text-slate-800 mt-1.5">{{ $ev['siswa'] }}</div>
                            <div class="text-[12px] text-slate-500 mt-0.5">{{ $ev['kelas'] ?? '-' }} · Kamar {{ $ev['kamar'] }}</div>
                            @if($ev['catatan'])
                                <div class="text-[12px] text-slate-500 mt-1.5">{{ $ev['catatan'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
