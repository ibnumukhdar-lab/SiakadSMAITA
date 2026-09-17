<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Histori Poin Siswa</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Rekap &amp; riwayat catatan perilaku</p>
                </div>
                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">
                    ⬅️ Kembali
                </a>
            </div>

            <!-- Profil Singkat Siswa -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-2xl shrink-0">
                        👤
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ $siswa->nama_lengkap }}</h3>
                        <p class="text-sm text-slate-500 font-medium mt-0.5">NISN: {{ $siswa->nisn }} · Kelas: {{ $siswa->kelas }}</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Total Akumulasi</p>
                    @if($total_poin > 0)
                        <span class="inline-block font-black text-2xl text-green-700 bg-green-50 border border-green-200 px-4 py-1.5 rounded-full">+{{ $total_poin }}</span>
                    @elseif($total_poin < 0)
                        <span class="inline-block font-black text-2xl text-red-700 bg-red-50 border border-red-200 px-4 py-1.5 rounded-full">{{ $total_poin }}</span>
                    @else
                        <span class="inline-block font-black text-2xl text-slate-600 bg-slate-100 border border-slate-200 px-4 py-1.5 rounded-full">0</span>
                    @endif
                </div>
            </div>

            <!-- Tabel Histori Detail -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="text-[15px] font-bold text-slate-800">Daftar Kejadian</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kriteria Perilaku</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Catatan</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Poin</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Dilaporkan Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $histori)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-sm text-slate-500 font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($histori->tanggal_kejadian)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-slate-800">
                                    {{ $histori->criteria->nama_perilaku ?? 'Kriteria Dihapus' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 italic">
                                    {{ $histori->catatan ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($histori->poin > 0)
                                        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 whitespace-nowrap">+{{ $histori->poin }}</span>
                                    @else
                                        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200 whitespace-nowrap">{{ $histori->poin }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    {{ $histori->nama_guru ?? 'Sistem' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500 italic">
                                    <span class="text-4xl block mb-3">📭</span>
                                    Belum ada riwayat poin untuk siswa ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $histories->links() }}
                </div>
            </div>

            @include('partials._kotak-catatan-rapot', [
                'jenis' => 'student_root',
                'siswa' => $siswa,
                'catatan' => $catatanSr ?? null,
                'kunci' => ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester],
                'boleh' => $bolehCatatanSr ?? false,
                'judul' => 'Catatan Mentor — Rapor Student Root (' . \App\Models\CatatanRaport::labelSr($tahunAjaran, $semester) . ')',
            ])

        </div>
    </div>
</x-app-layout>
