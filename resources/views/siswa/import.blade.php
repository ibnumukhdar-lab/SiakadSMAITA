<x-app-layout>
    <div class="py-6 sm:py-8">
        <div class="max-w-2xl mx-auto px-3 sm:px-6 lg:px-8">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">📥 Impor Data Siswa</h3>
                    <p class="text-[13px] text-slate-500 mt-0.5">Unggah → periksa pratinjau → baru disimpan</p>
                </div>
                <a href="{{ route('siswa.index') }}" class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition whitespace-nowrap">← Data Induk</a>
            </div>

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-3.5 mb-4 rounded shadow-sm text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
                <h4 class="text-[15px] font-bold text-slate-800 mb-1">Unggah berkas CSV</h4>
                <p class="text-[13px] text-slate-500 leading-relaxed mb-4">
                    Berkas dari Excel (Simpan sebagai CSV). Judul kolom dibaca otomatis
                    (Nama Lengkap, NISN, NIS, Kelas, dan seterusnya), jadi urutan kolom bebas.
                    Setelah diunggah, sistem menampilkan <strong>pratinjau</strong> — belum ada data yang masuk sampai kamu klik tombol impor.
                </p>

                <a href="{{ route('siswa.downloadTemplate') }}"
                   class="inline-flex items-center justify-center h-10 px-3.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-[13px] font-semibold hover:bg-slate-50 transition mb-5">
                    📄 Unduh template CSV (26 kolom)
                </a>

                <form action="{{ route('siswa.prosesImport') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block rounded-xl border-2 border-dashed border-slate-300 bg-slate-50/60 p-6 text-center mb-4 cursor-pointer hover:border-blue-400 transition">
                        <span class="text-2xl block mb-1.5">📄</span>
                        <span class="text-[13px] font-semibold text-slate-600 block">Pilih berkas CSV</span>
                        <span class="text-[11px] text-slate-400 block mt-1">Berkas .csv — maksimal beberapa ribu baris</span>
                        <input type="file" name="file_csv" accept=".csv,text/csv" required class="mt-3 text-[13px] text-slate-600 w-full">
                    </label>

                    <button type="submit"
                            class="w-full h-11 rounded-lg bg-blue-900 text-white text-sm font-bold hover:bg-blue-800 transition">
                        🔍 Baca berkas &amp; tampilkan pratinjau
                    </button>
                </form>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-900 p-3.5 mt-4 rounded shadow-sm text-[13px] leading-relaxed">
                <strong>Aturan validasi:</strong> NISN wajib 10 digit angka (spasi / karakter tak terlihat otomatis dibuang).
                Baris dengan NISN kosong, bukan 10 digit, ganda di berkas, atau sudah terdaftar akan <strong>dilewati</strong>
                dan dirinci di pratinjau — bisa diunduh sebagai CSV untuk dibetulkan di Excel.
            </div>
        </div>
    </div>
</x-app-layout>
