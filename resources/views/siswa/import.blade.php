<x-app-layout>
    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Judul halaman --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Impor Data Induk Siswa</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Impor massal data induk siswa dari file CSV</p>
                </div>
                <a href="{{ route('siswa.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">← Kembali ke Database</a>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 sm:p-6">
                <div class="mb-5">
                    <h4 class="text-[15px] font-bold text-slate-800">📥 Impor Data Siswa (CSV)</h4>
                    <p class="text-slate-500 text-sm mt-1 leading-relaxed">
                        Gunakan fitur ini untuk memasukkan data siswa secara massal menggunakan file Excel berformat CSV. Sistem otomatis memvalidasi duplikasi NISN.
                    </p>
                </div>

                <a href="{{ route('siswa.downloadTemplate') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap mb-6">
                    📄 Unduh Template CSV Terbaru
                </a>

                <form action="{{ route('siswa.prosesImport') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-6 text-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/50 mb-6">
                        <input type="file" name="file_csv" accept=".csv" required class="text-sm text-slate-600 font-medium w-full cursor-pointer">
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap w-full">
                        🚀 Eksekusi Impor Data Siswa
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
