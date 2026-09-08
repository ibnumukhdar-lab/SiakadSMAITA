<x-guest-layout>
    <div class="py-12 bg-slate-50 min-h-screen flex flex-col justify-center">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="bg-white overflow-hidden shadow-xl rounded-2xl p-4 sm:p-8 border border-slate-200 relative">

                <div class="text-center mb-8 border-b pb-6">
                    <h3 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-wide">VERIFIKASI ARSIP DIGITAL</h3>
                    
                    @if($arsip->file_surat || $arsip->link_drive)
                        <span class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-xs font-bold mt-3 border border-green-300 shadow-sm">
                            ✓ DOKUMEN TERDAFTAR ASLI
                        </span>
                    @else
                        <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-xs font-bold mt-3 border border-yellow-300 shadow-sm">
                            ⏳ DALAM TAHAP VERIFIKASI
                        </span>
                    @endif
                </div>

                <!-- Bagian QR Code dihapus, tabel dibuat memenuhi lebar (full width) -->
                <div class="mb-10 w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[280px]">
                        <tr class="border-b border-slate-100">
                            <th class="py-3 text-sm font-semibold text-slate-500 w-1/3 sm:w-1/4 uppercase tracking-wider">Kategori</th>
                            <td class="py-3 text-sm font-bold text-slate-800">: {{ $arsip->jenis_surat }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <th class="py-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">No. Surat</th>
                            <td class="py-3 text-sm font-bold text-slate-800 break-all">: {{ $arsip->nomor_surat }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <th class="py-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                            <td class="py-3 text-sm font-bold text-slate-800">: {{ \Carbon\Carbon::parse($arsip->tanggal_surat)->format('d F Y') }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <th class="py-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">Instansi</th>
                            <td class="py-3 text-sm font-bold text-slate-800">: {{ $arsip->pihak_terkait }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <th class="py-3 text-sm font-semibold text-slate-500 uppercase tracking-wider">Perihal</th>
                            <td class="py-3 text-sm font-bold text-slate-800">: {{ $arsip->perihal }}</td>
                        </tr>
                    </table>
                </div>

                <div class="w-full border-t border-slate-200 pt-8 space-y-6">
                    
                  @if($arsip->file_surat && pathinfo($arsip->file_surat, PATHINFO_EXTENSION) == 'pdf')
                    <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-sm bg-slate-50 p-3 sm:p-4">
                        <div class="text-sm font-bold text-slate-700 mb-3 flex items-center justify-between">
                            <span>📄 Pratonton Dokumen:</span>
                        </div>
                        
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
                            <!-- JALUR DIUBAH KE /BERKAS/ AGAR BISA DIUNDUH OLEH PUBLIK -->
                            <a href="{{ url('berkas/' . $arsip->file_surat) }}" target="_blank" style="background-color: #000000; color: #ffffff;" class="flex-1 max-w-md text-center font-bold py-3 px-4 rounded-lg shadow hover:opacity-80 transition duration-300">
                                📂 Unduh Dokumen Fisik
                            </a>
                        @endif

                        @if($arsip->link_drive)
                            <a href="{{ $arsip->link_drive }}" target="_blank" class="flex-1 max-w-md text-center bg-blue-50 hover:bg-blue-100 text-blue-800 font-bold py-3 px-4 rounded-lg shadow-sm border border-blue-200 transition duration-300">
                                🌐 Buka Tautan Google Drive
                            </a>
                        @endif
                    </div>

                    @if(!$arsip->file_surat && !$arsip->link_drive)
                        <div class="text-center p-4 bg-yellow-50 text-yellow-800 rounded-xl text-sm font-semibold border border-yellow-200 max-w-2xl mx-auto">
                            ⚠️ File dokumen fisik atau tautan Cloud belum dilampirkan.
                        </div>
                    @endif

                </div>

                <div class="mt-12 text-center">
                    <a href="/" class="text-blue-600 hover:text-blue-800 text-sm font-bold inline-flex items-center gap-1 hover:underline transition">
                        ← Kembali ke Beranda
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>