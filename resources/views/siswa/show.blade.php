<x-app-layout>
    <style>
        @media print {
            /* 1. Sihir Penghilang Elemen Web (Menu, Tombol, dll) */
            nav, header, .min-h-screen > nav, .min-h-screen > header, .sembunyikan-saat-print {
                display: none !important;
            }
            
            /* 2. Bersihkan Background & Margin agar full kertas */
            body, main, .min-h-screen, .bg-gray-100 {
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .py-12, .py-8 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
            .max-w-4xl {
                max-width: 100% !important;
                padding: 0 !important;
            }
            
            /* 3. Rapikan Kotak dan Garis */
            .shadow, .shadow-sm {
                box-shadow: none !important;
            }
            .border {
                border-color: #cbd5e1 !important; 
            }
            
            /* 4. ANTI-TERPOTONG: Jaga agar blok tidak terbelah dua halaman */
            .jangan-terpotong {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                margin-bottom: 24px !important; /* Jarak antar blok saat di-print */
            }
        }
    </style>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            @auth
            <!-- Judul halaman + tombol aksi (disembunyikan saat print) -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5 sembunyikan-saat-print">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Profil Lembar Induk Siswa</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Lembar induk &amp; keabsahan siswa SMA IT Arafah</p>
                </div>
                <div class="flex flex-wrap gap-2.5">
                    <a href="{{ route('siswa.index') }}" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 text-sm font-semibold shadow-sm transition whitespace-nowrap">← Kembali</a>
                    <button onclick="window.print()" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">🖨️ Cetak Buku Induk</button>
                </div>
            </div>
            @endauth

            <div class="bg-white p-6 sm:p-8 border border-slate-200 rounded-2xl shadow-sm print:border-none print:p-0">

                <!-- Blok Header Profil Pokok (Jangan terpotong) -->
                <div class="jangan-terpotong flex flex-col sm:flex-row gap-6 items-center border-b border-slate-200 pb-6 mb-6 print:border-b-2 print:border-slate-800">
                    <div class="w-32 h-40 bg-slate-100 rounded-lg border flex items-center justify-center overflow-hidden shadow-inner print:border-slate-400">
                        @if($siswa->foto)
                            <!-- JALUR DIUBAH KE /BERKAS/ AGAR MENEMBUS BLOKIR CPANEL -->
                            <img src="{{ url('berkas/' . $siswa->foto) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl">👤</span>
                        @endif
                    </div>
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-2xl font-black text-slate-800">{{ $siswa->nama_lengkap }}</h3>
                        <p class="text-slate-600 font-bold text-sm">NISN: {{ $siswa->nisn }} | NIS: {{ $siswa->nis ?? '-' }}</p>
                        <div class="mt-2 flex gap-2 justify-center sm:justify-start">
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full border border-blue-200 print:border-blue-500">Kelas {{ $siswa->kelas ?? '-' }}</span>
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-200 print:border-green-500">Angkatan {{ $siswa->thn_masuk ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="text-center print:block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('siswa.public', $siswa->nisn)) }}" class="border p-1 bg-white rounded shadow-sm mx-auto">
                        <p class="text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wider">Keabsahan Siswa</p>
                    </div>
                </div>

                <!-- JIKA SESEORANG BELUM LOGIN (AKSES TERBATAS) -->
                @guest
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6 sembunyikan-saat-print">
                    <p class="text-sm text-blue-700 font-medium">
                        📌 Data keabsahan siswa ditemukan di e-Arsip SIAKAD SMA IT Arafah. Detail keluarga dan domisili ditutup untuk melindungi privasi data pribadi siswa.
                    </p>
                </div>
                <div class="jangan-terpotong bg-slate-50 p-4 rounded-xl print:bg-transparent print:p-0">
                    <dl class="divide-y divide-slate-200/70">
                        <div class="py-2.5 grid grid-cols-3 gap-4">
                            <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Jenis Kelamin</dt>
                            <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->jk }}</dd>
                        </div>
                        <div class="py-2.5 grid grid-cols-3 gap-4">
                            <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Tempat, Tgl Lahir</dt>
                            <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->ttl ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 grid grid-cols-3 gap-4">
                            <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Tahun Ajaran Berlangsung</dt>
                            <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->tahun_ajaran ?? '-' }}</dd>
                        </div>
                        <div class="py-2.5 grid grid-cols-3 gap-4">
                            <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Status Administrasi</dt>
                            <dd class="col-span-2 text-sm font-medium text-green-600">{{ $siswa->status }}</dd>
                        </div>
                    </dl>
                </div>
                @endguest

                <!-- JIKA ADMIN SUDAH LOGIN (AKSES DATA PENUH) -->
                @auth
                <div class="space-y-8 print:space-y-0">

                    <!-- Blok Domisili (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="text-[12px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-1">📍 Domisili &amp; Standar Dapodik</h4>
                        <dl class="divide-y divide-slate-100">
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Nomor HP Utama Ortu</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->hp_ortu ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Status Tinggal</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->tinggal_bersama ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap Domisili</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->alamat ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Jarak ke Sekolah</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->jarak ? $siswa->jarak . ' Km' : '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Transportasi</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->transportasi ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Kesejahteraan (KIP/PKH)</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->kesejahteraan ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Asal Sekolah (SMP/MTs)</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->asal_sekolah ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Riwayat Penyakit</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->penyakit ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Blok Orang Tua (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="text-[12px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-1">👨‍👩‍👦 Data Orang Tua &amp; Wali</h4>
                        <dl class="divide-y divide-slate-100">
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Nama Ayah (Status)</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->nama_ayah ?? '-' }} ({{ $siswa->status_ayah ?? '-' }})</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Pekerjaan Ayah</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->pekerjaan_ayah ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Nama Ibu (Status)</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->nama_ibu ?? '-' }} ({{ $siswa->status_ibu ?? '-' }})</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Pekerjaan Ibu</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->pekerjaan_ibu ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">Nama Wali</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->nama_wali ?? '-' }}</dd>
                            </div>
                            <div class="py-3 grid grid-cols-3 gap-4">
                                <dt class="text-[12px] font-bold uppercase tracking-wider text-slate-400">No. HP Wali</dt>
                                <dd class="col-span-2 text-sm text-slate-800 font-medium">{{ $siswa->hp_wali ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Blok Prestasi (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="text-[12px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-3">🏆 Logbook Prestasi</h4>
                        @if(!empty($siswa->prestasi) && count($siswa->prestasi) > 0)
                            <div class="space-y-2">
                                @foreach($siswa->prestasi as $p)
                                    <div class="bg-green-50 p-2.5 border rounded border-green-200 text-sm flex gap-3 print:bg-transparent print:border-slate-300">
                                        <span class="font-mono font-bold text-green-700 print:text-slate-800">[{{ $p['tgl'] ?? '-' }}]</span>
                                        <span class="bg-green-200 text-green-900 text-[10px] font-black px-2 py-0.5 rounded h-5 print:border print:border-slate-800">{{ $p['tingkat'] ?? 'Sekolah' }}</span>
                                        <span class="text-slate-700 font-medium">{{ $p['nama'] ?? '-' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400 text-xs italic">Belum ada catatan logbook prestasi.</p>
                        @endif
                    </div>

                    <!-- Blok Pelanggaran (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="text-[12px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-2 mb-3">⚠️ Catatan Kedisiplinan</h4>
                        @if(!empty($siswa->pelanggaran) && count($siswa->pelanggaran) > 0)
                            <div class="space-y-2">
                                @foreach($siswa->pelanggaran as $pl)
                                    <div class="bg-red-50 p-2.5 border rounded border-red-200 text-sm flex flex-col sm:flex-row sm:gap-3 print:bg-transparent print:border-slate-300">
                                        <div class="flex gap-2">
                                            <span class="font-mono font-bold text-red-700 print:text-slate-800">[{{ $pl['tgl'] ?? '-' }}]</span>
                                            <span class="bg-red-200 text-red-900 text-[10px] font-black px-2 py-0.5 rounded h-5 print:border print:border-slate-800">{{ $pl['kategori'] ?? 'Ringan' }}</span>
                                        </div>
                                        <span class="text-slate-700 font-medium mt-1 sm:mt-0">Kasus: <strong>{{ $pl['kasus'] ?? '-' }}</strong> | Sanksi: <span class="text-red-700 font-bold print:text-slate-800">{{ $pl['tindakan'] ?? '-' }}</span></span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400 text-xs italic">Bersih. Belum ada catatan pelanggaran disiplin.</p>
                        @endif
                    </div>

                </div>
                @endauth

            </div>
        </div>
    </div>
</x-app-layout>
