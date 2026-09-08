<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ⚙️ Pengaturan Master Kriteria Poin
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Form Tambah Kriteria -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-extrabold mb-4 text-gray-800">➕ Tambah Kriteria Baru</h3>
                <form action="{{ route('sr.kriteria.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @csrf
                    <select name="kategori" class="border-gray-300 rounded-lg text-sm p-2.5">
                        <option value="positif">✅ Positif</option>
                        <option value="negatif">❌ Negatif</option>
                    </select>
                    <input type="text" name="nama_perilaku" placeholder="Nama Perilaku (mis: Terlambat)" class="border-gray-300 rounded-lg text-sm p-2.5" required>
                    <input type="number" name="poin" placeholder="Poin (mis: 5 atau 2)" class="border-gray-300 rounded-lg text-sm p-2.5" required>
                    <button type="submit" style="background-color: #1e293b; color: white;" class="px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm">Simpan Kriteria</button>
                </form>
            </div>

            <!-- Tabel Kriteria -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b-2 border-gray-200">
                            <th class="p-4 text-sm font-bold">Kategori</th>
                            <th class="p-4 text-sm font-bold">Perilaku</th>
                            <th class="p-4 text-sm font-bold text-center">Poin</th>
                            <th class="p-4 text-sm font-bold text-center">Status</th>
                            <th class="p-4 text-sm font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criterias as $c)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <form action="{{ route('sr.kriteria.update', $c->id) }}" method="POST">
                                @csrf @method('PUT')
                                <td class="p-4">
                                    <select name="kategori" class="border-gray-300 rounded text-sm w-full font-bold {{ $c->kategori == 'positif' ? 'text-green-600' : 'text-red-600' }}">
                                        <option value="positif" {{ $c->kategori == 'positif' ? 'selected' : '' }}>Positif</option>
                                        <option value="negatif" {{ $c->kategori == 'negatif' ? 'selected' : '' }}>Negatif</option>
                                    </select>
                                </td>
                                <td class="p-4">
                                    <input type="text" name="nama_perilaku" value="{{ $c->nama_perilaku }}" class="border-gray-300 rounded text-sm w-full">
                                </td>
                                <td class="p-4">
                                    <input type="number" name="poin" value="{{ $c->poin }}" class="border-gray-300 rounded text-sm w-20 text-center">
                                </td>
                                <td class="p-4">
                                    <select name="status" class="border-gray-300 rounded text-sm w-full text-center">
                                        <option value="aktif" {{ $c->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="nonaktif" {{ $c->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </td>
                                <td class="p-4 text-center">
                                    <button type="submit" style="background-color: #4f46e5; color: white;" class="px-4 py-2 rounded text-xs font-bold shadow-sm hover:opacity-90">Update</button>
                                </td>
                            </form>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>