<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">🏠 Kamar Binaan Saya</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Assalamu'alaikum, {{ Auth::user()->name }} — daftar kamar asrama di bawah tanggung jawab Anda</p>
                </div>
            </div>

            <!-- Tabel Daftar Kamar Binaan -->
            <div class="bg-white overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nama Kamar</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Kapasitas</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Penghuni Saat Ini</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kamars as $kamar)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 text-sm font-bold text-slate-900 uppercase">{{ $kamar->nama_kamar }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($kamar->kategori == 'putra')
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">👦 Putra</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-pink-50 text-pink-700 border border-pink-200">👧 Putri</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700 text-center font-semibold">{{ $kamar->kapasitas }}</td>
                                <td class="px-4 py-3 text-sm text-center font-bold {{ $kamar->members_count >= $kamar->kapasitas ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $kamar->members_count }} Anak
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-center">
                                        <a href="{{ route('asrama.kamar.show', $kamar->id) }}" title="Kelola Anggota" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-blue-700 hover:bg-blue-50 transition">👥</a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-500 font-medium">
                                    <span class="text-4xl block mb-3">🏠</span>
                                    Anda belum ditugaskan sebagai Musyrif di kamar manapun.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
