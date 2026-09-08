<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📜 Histori Poin Siswa
            </h2>
            <!-- Tombol Kembali -->
            <a href="{{ url()->previous() }}" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg transition">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Profil Singkat Siswa -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-2xl shadow-inner">
                        👤
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-gray-800">{{ $siswa->nama_lengkap }}</h3>
                        <p class="text-sm text-gray-500 font-bold">NISN: {{ $siswa->nisn }} | Kelas: {{ $siswa->kelas }}</p>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Total Akumulasi</p>
                    @if($total_poin > 0)
                        <span class="inline-block font-black text-2xl text-green-700 bg-green-100 px-4 py-1 rounded-lg">+{{ $total_poin }}</span>
                    @elseif($total_poin < 0)
                        <span class="inline-block font-black text-2xl text-red-700 bg-red-100 px-4 py-1 rounded-lg">{{ $total_poin }}</span>
                    @else
                        <span class="inline-block font-black text-2xl text-gray-600 bg-gray-100 px-4 py-1 rounded-lg">0</span>
                    @endif
                </div>
            </div>

            <!-- Tabel Histori Detail -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h4 class="text-lg font-extrabold text-gray-800 mb-4 border-b pb-2">Daftar Kejadian</h4>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-200">
                                <th class="p-4 text-sm font-bold text-gray-700">Tanggal</th>
                                <th class="p-4 text-sm font-bold text-gray-700">Kriteria Perilaku</th>
                                <th class="p-4 text-sm font-bold text-gray-700">Catatan</th>
                                <th class="p-4 text-sm font-bold text-center text-gray-700">Poin</th>
                                <th class="p-4 text-sm font-bold text-gray-700">Dilaporkan Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $histori)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="p-4 text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($histori->tanggal_kejadian)->translatedFormat('d M Y') }}</td>
                                <td class="p-4 text-sm font-bold text-gray-800">
                                    {{ $histori->criteria->nama_perilaku ?? 'Kriteria Dihapus' }}
                                </td>
                                <td class="p-4 text-sm text-gray-600 italic">
                                    {{ $histori->catatan ?: '-' }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($histori->poin > 0)
                                        <span class="inline-block font-extrabold text-green-700 bg-green-100 px-2 py-1 rounded text-xs">+{{ $histori->poin }}</span>
                                    @else
                                        <span class="inline-block font-extrabold text-red-700 bg-red-100 px-2 py-1 rounded text-xs">{{ $histori->poin }}</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-gray-600">
                                    {{ $histori->nama_guru ?? 'Sistem' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500 italic">Belum ada riwayat poin untuk siswa ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $histories->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>