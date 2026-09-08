<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6">
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Arsip Surat</h3>
                <p class="text-sm text-slate-500 mt-0.5">Perbarui data arsip surat masuk / keluar</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="p-5 sm:p-6">
                    <form action="{{ route('arsip.update', $arsip->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 gap-5">
                            <!-- Kategori Surat -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Kategori Surat</label>
                                <select name="jenis_surat" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                    <option value="Surat Masuk" {{ $arsip->jenis_surat == 'Surat Masuk' ? 'selected' : '' }}>📥 Surat Masuk</option>
                                    <option value="Surat Keluar" {{ $arsip->jenis_surat == 'Surat Keluar' ? 'selected' : '' }}>📤 Surat Keluar</option>
                                </select>
                            </div>

                            <!-- Nomor & Tanggal Surat -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Nomor Surat</label>
                                    <input type="text" name="nomor_surat" value="{{ $arsip->nomor_surat }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Tanggal Surat</label>
                                    <input type="date" name="tanggal_surat" value="{{ $arsip->tanggal_surat }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                                </div>
                            </div>

                            <!-- Instansi Tujuan / Pengirim -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Instansi Tujuan / Pengirim</label>
                                <input type="text" name="pihak_terkait" value="{{ $arsip->pihak_terkait }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>
                            </div>

                            <!-- Perihal -->
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Perihal</label>
                                <textarea name="perihal" rows="3" class="w-full py-2.5 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" required>{{ $arsip->perihal }}</textarea>
                            </div>

                            <!-- Upload Dokumen Baru & Link Cloud -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 space-y-4">
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">📂 Upload Dokumen Baru (Abaikan jika tidak ingin mengganti file lama)</label>
                                    <input type="file" name="file_surat" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-900 file:text-white hover:file:bg-blue-800 file:cursor-pointer transition" accept=".pdf,.doc,.docx">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Atau Tautkan Link Cloud Baru</label>
                                    <input type="url" name="link_drive" value="{{ $arsip->link_drive }}" class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="https://drive.google.com/...">
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                            <a href="{{ route('arsip.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">Batal</a>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">
                                Update Arsip
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
