<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Kriteria Poin</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola daftar perilaku &amp; poin Student Root</p>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Tambah Kriteria -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h4 class="text-[15px] font-bold text-slate-800">➕ Tambah Kriteria Baru</h4>
                </div>
                <form action="{{ route('sr.kriteria.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 p-5 sm:p-6">
                    @csrf
                    <select name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                        <option value="positif">✅ Positif</option>
                        <option value="negatif">❌ Negatif</option>
                    </select>
                    <input type="text" name="nama_perilaku" placeholder="Nama Perilaku (mis: Terlambat)" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                    <input type="number" name="poin" placeholder="Poin (mis: 5 atau 2)" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">Simpan Kriteria</button>
                </form>
            </div>

            <!-- Tabel Kriteria -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[760px]">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200">
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Kategori</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400">Perilaku</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Poin</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Status</th>
                                <th class="px-4 py-3.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criterias as $c)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/70 transition">
                                <form action="{{ route('sr.kriteria.update', $c->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <td class="px-4 py-3 align-top">
                                        <select name="kategori" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition {{ $c->kategori == 'positif' ? 'text-green-600' : 'text-red-600' }}">
                                            <option value="positif" {{ $c->kategori == 'positif' ? 'selected' : '' }}>Positif</option>
                                            <option value="negatif" {{ $c->kategori == 'negatif' ? 'selected' : '' }}>Negatif</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input type="text" name="nama_perilaku" value="{{ $c->nama_perilaku }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input type="number" name="poin" value="{{ $c->poin }}" class="w-24 h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 text-center focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <select name="status" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                                            <option value="aktif" {{ $c->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                            <option value="nonaktif" {{ $c->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 align-top text-center">
                                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-9 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-xs font-bold shadow-sm transition whitespace-nowrap">Update</button>
                                    </td>
                                </form>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
