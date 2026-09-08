<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Arsip Surat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                
                <form action="{{ route('arsip.update', $arsip->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kategori Surat</label>
                        <select name="jenis_surat" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            <option value="Surat Masuk" {{ $arsip->jenis_surat == 'Surat Masuk' ? 'selected' : '' }}>📥 Surat Masuk</option>
                            <option value="Surat Keluar" {{ $arsip->jenis_surat == 'Surat Keluar' ? 'selected' : '' }}>📤 Surat Keluar</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Surat</label>
                            <input type="text" name="nomor_surat" value="{{ $arsip->nomor_surat }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Surat</label>
                            <input type="date" name="tanggal_surat" value="{{ $arsip->tanggal_surat }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Instansi Tujuan / Pengirim</label>
                        <input type="text" name="pihak_terkait" value="{{ $arsip->pihak_terkait }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Perihal</label>
                        <textarea name="perihal" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ $arsip->perihal }}</textarea>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-6">
                        <label class="block text-blue-800 text-sm font-bold mb-2">📂 Upload Dokumen Baru (Abaikan jika tidak ingin mengganti file lama)</label>
                        <input type="file" name="file_surat" class="mb-4 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" accept=".pdf,.doc,.docx">
                        
                        <label class="block text-blue-800 text-sm font-bold mb-2">Atau Tautkan Link Cloud Baru</label>
                        <input type="url" name="link_drive" value="{{ $arsip->link_drive }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="https://drive.google.com/...">
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('arsip.index') }}" class="text-gray-500 hover:text-gray-800 mr-6 font-bold">Batal</a>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-300">
                            Update Arsip
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>