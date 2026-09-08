<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Impor Data Induk Siswa Massal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('siswa.index') }}" style="background-color: #64748b; color: #ffffff;" class="inline-block mb-4 font-bold py-2 px-4 rounded shadow text-sm">
                ← Kembali ke Database
            </a>

            <div class="bg-white p-6 shadow-sm rounded-xl border border-gray-200">
                <h3 class="text-xl font-extrabold text-slate-800 mb-2">📥 Impor Data Siswa (CSV)</h3>
                <p class="text-slate-600 text-sm mb-5 leading-relaxed">
                    Gunakan fitur ini untuk memasukkan data siswa secara massal menggunakan file Excel berformat CSV. Sistem otomatis memvalidasi duplikasi NISN.
                </p>

                <a href="{{ route('siswa.downloadTemplate') }}" style="background-color: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1;" class="inline-block mb-6 font-bold py-2.5 px-4 rounded text-sm hover:bg-gray-100 transition">
                    📄 Unduh Template CSV Terbaru
                </a>

                <form action="{{ route('siswa.prosesImport') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1;" class="p-6 text-center rounded-lg mb-6">
                        <input type="file" name="file_csv" accept=".csv" required class="text-sm text-slate-600 font-medium">
                    </div>

                    <button type="submit" style="background-color: #10b981; color: #ffffff;" class="w-full font-bold py-3 px-4 rounded shadow hover:opacity-90 transition text-center">
                        🚀 Eksekusi Impor Data Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>