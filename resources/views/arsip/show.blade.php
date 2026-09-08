<x-guest-layout>
    <div class="py-10 bg-slate-50 min-h-screen flex flex-col justify-center">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-slate-200 p-5 sm:p-8">

                <div class="text-center border-b border-slate-100 pb-6 mb-6">
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">Verifikasi Arsip Digital</h3>
                    <p class="text-sm text-slate-500 mt-1">Lembar verifikasi keaslian arsip surat digital</p>

                    <div class="mt-4">
                        @if($arsip->file_surat || $arsip->link_drive)
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">✓ DOKUMEN TERDAFTAR ASLI</span>
                        @else
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">⏳ DALAM TAHAP VERIFIKASI</span>
                        @endif
                    </div>
                </div>

                <dl class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 mb-6">
                    <div class="px-5 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Kategori</dt>
                        <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $arsip->jenis_surat }}</dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">No. Surat</dt>
                        <dd class="col-span-2 text-sm text-slate-800 font-medium break-all">{{ $arsip->nomor_surat }}</dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Tanggal</dt>
                        <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('d F Y') }}</dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Instansi</dt>
                        <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $arsip->pihak_terkait }}</dd>
                    </div>
                    <div class="px-5 py-3 grid grid-cols-3 gap-4">
                        <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Perihal</dt>
                        <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $arsip->perihal }}</dd>
                    </div>
                </dl>

                <div class="w-full space-y-6">
                    @if($arsip->file_surat && pathinfo($arsip->file_surat, PATHINFO_EXTENSION) == 'pdf')
                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50 p-3 sm:p-4">
                        <div class="text-sm font-bold text-slate-600 mb-3">📄 Pratonton Dokumen:</div>

                        <div class="w-full rounded-xl overflow-hidden border border-slate-300 bg-white" style="height: 70vh; min-height: 450px; max-height: 850px;">
                            <!-- JALUR DIUBAH KE /BERKAS/ AGAR GOOGLE BISA MEMBACA PDFNYA -->
                            <iframe
                                src="https://docs.google.com/viewer?url={{ urlencode(url('berkas/' . $arsip->file_surat)) }}&embedded=true"
                                style="width: 100%; height: 100%; border: none;"
                                frameborder="0">
                            </iframe>
                        </div>
                    </div>
                    @endif

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @if($arsip->file_surat)
                            <a href="{{ url('berkas/' . $arsip->file_surat) }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:flex-1 sm:max-w-md">
                                📂 Unduh Dokumen Fisik
                            </a>
                        @endif

                        @if($arsip->link_drive)
                            <a href="{{ $arsip->link_drive }}" target="_blank" class="inline-flex items-center justify-center gap-1.5 h-10 px-5 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap w-full sm:flex-1 sm:max-w-md">
                                🌐 Buka Tautan Google Drive
                            </a>
                        @endif
                    </div>

                    @if(!$arsip->file_surat && !$arsip->link_drive)
                        <div class="text-center p-4 bg-amber-50 text-amber-700 rounded-xl text-sm font-semibold border border-amber-200 max-w-2xl mx-auto">
                            ⚠️ File dokumen fisik atau tautan Cloud belum dilampirkan.
                        </div>
                    @endif
                </div>

                <div class="mt-10 text-center">
                    <a href="/" class="text-blue-900 hover:text-blue-700 text-sm font-bold inline-flex items-center gap-1 hover:underline transition">
                        ← Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
