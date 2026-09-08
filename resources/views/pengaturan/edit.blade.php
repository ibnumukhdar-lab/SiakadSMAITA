<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Identitas Lembaga') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lembaga / Sekolah</label>
                            <input type="text" name="nama_sekolah" value="{{ $pengaturan->nama_sekolah }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Motto / Tagline</label>
                            <input type="text" name="motto" value="{{ $pengaturan->motto }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
                        <div class="mb-4">
                            <label class="block text-gray-800 text-sm font-bold mb-2">🖼️ Upload Logo Sekolah</label>
                            @if($pengaturan->logo_path)
                                <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                                <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" class="h-16 mb-3 rounded shadow-sm bg-white p-1">
                            @endif
                            <input type="file" name="logo_path" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-200 hover:file:bg-gray-300">
                        </div>
                        
                        <hr class="my-4 border-gray-300">

                        <div>
                            <label class="block text-gray-800 text-sm font-bold mb-2">🏔️ Upload Sampul Dashboard (Opsional)</label>
                            @if($pengaturan->sampul_path)
                                <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                                <img src="{{ url('berkas/' . $pengaturan->sampul_path) }}" class="h-24 w-auto mb-3 rounded shadow-sm">
                            @endif
                            <input type="file" name="sampul_path" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-200 hover:file:bg-gray-300">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>