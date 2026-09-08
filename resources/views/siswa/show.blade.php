<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight sembunyikan-saat-print">
            {{ __('Profil Lembar Induk Siswa') }}
        </h2>
    </x-slot>

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
            .py-12 {
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

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @auth
            <!-- Tombol sekarang menggunakan class khusus: sembunyikan-saat-print -->
            <div class="mb-4 flex justify-between sembunyikan-saat-print">
                <a href="{{ route('siswa.index') }}" style="background-color: #64748b; color: #ffffff;" class="font-bold py-2 px-4 rounded shadow text-sm hover:opacity-80 transition">← Kembali</a>
                <button onclick="window.print()" style="background-color: #1e293b; color: #ffffff;" class="font-bold py-2 px-4 rounded shadow text-sm hover:opacity-80 transition">🖨️ Cetak Buku Induk</button>
            </div>
            @endauth

            <div class="bg-white p-6 sm:p-8 shadow sm:rounded-xl border border-gray-200 print:border-none print:p-0">
                
                <!-- Blok Header Profil Pokok (Jangan terpotong) -->
                <div class="jangan-terpotong flex flex-col sm:flex-row gap-6 items-center border-b pb-6 mb-6 print:border-b-2 print:border-slate-800">
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
                        <p class="text-sky-600 font-bold text-sm">NISN: {{ $siswa->nisn }} | NIS: {{ $siswa->nis ?? '-' }}</p>
                        <div class="mt-2 flex gap-2 justify-center sm:justify-start">
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full print:border print:border-blue-500">Kelas {{ $siswa->kelas ?? '-' }}</span>
                            <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full print:border print:border-green-500">Angkatan {{ $siswa->thn_masuk ?? '-' }}</span>
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
                <div class="jangan-terpotong grid grid-cols-2 gap-4 text-sm bg-slate-50 p-4 rounded-xl print:bg-transparent print:p-0">
                    <div><span class="text-slate-400 font-semibold block">Jenis Kelamin</span><strong>{{ $siswa->jk }}</strong></div>
                    <div><span class="text-slate-400 font-semibold block">Tempat, Tgl Lahir</span><strong>{{ $siswa->ttl ?? '-' }}</strong></div>
                    <div><span class="text-slate-400 font-semibold block">Tahun Ajaran Berlangsung</span><strong>{{ $siswa->tahun_ajaran ?? '-' }}</strong></div>
                    <div><span class="text-slate-400 font-semibold block">Status Administrasi</span><strong class="text-green-600">{{ $siswa->status }}</strong></div>
                </div>
                @endguest

                <!-- JIKA ADMIN SUDAH LOGIN (AKSES DATA PENUH) -->
                @auth
                <div class="space-y-6 print:space-y-0">
                    
                    <!-- Blok Domisili (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="font-bold text-blue-800 border-b pb-1 mb-2">📍 Domisili & Standar Dapodik</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div><span class="text-slate-400 font-medium block">Nomor HP Utama Ortu</span><strong>{{ $siswa->hp_ortu ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Status Tinggal</span><strong>{{ $siswa->tinggal_bersama ?? '-' }}</strong></div>
                            <div class="sm:col-span-2"><span class="text-slate-400 font-medium block">Alamat Lengkap Domisili</span><strong>{{ $siswa->alamat ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Jarak ke Sekolah</span><strong>{{ $siswa->jarak ? $siswa->jarak . ' Km' : '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Transportasi</span><strong>{{ $siswa->transportasi ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Kesejahteraan (KIP/PKH)</span><strong>{{ $siswa->kesejahteraan ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Asal Sekolah (SMP/MTs)</span><strong>{{ $siswa->asal_sekolah ?? '-' }}</strong></div>
                            <div class="sm:col-span-2"><span class="text-slate-400 font-medium block">Riwayat Penyakit</span><strong>{{ $siswa->penyakit ?? '-' }}</strong></div>
                        </div>
                    </div>

                    <!-- Blok Orang Tua (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="font-bold text-blue-800 border-b pb-1 mb-2">👨‍👩‍👦 Data Orang Tua & Wali</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div><span class="text-slate-400 font-medium block">Nama Ayah (Status)</span><strong>{{ $siswa->nama_ayah ?? '-' }} ({{ $siswa->status_ayah ?? '-' }})</strong></div>
                            <div><span class="text-slate-400 font-medium block">Pekerjaan Ayah</span><strong>{{ $siswa->pekerjaan_ayah ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Nama Ibu (Status)</span><strong>{{ $siswa->nama_ibu ?? '-' }} ({{ $siswa->status_ibu ?? '-' }})</strong></div>
                            <div><span class="text-slate-400 font-medium block">Pekerjaan Ibu</span><strong>{{ $siswa->pekerjaan_ibu ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">Nama Wali</span><strong>{{ $siswa->nama_wali ?? '-' }}</strong></div>
                            <div><span class="text-slate-400 font-medium block">No. HP Wali</span><strong>{{ $siswa->hp_wali ?? '-' }}</strong></div>
                        </div>
                    </div>

                    <!-- Blok Prestasi (Jangan terpotong) -->
                    <div class="jangan-terpotong">
                        <h4 class="font-bold text-green-700 border-b pb-1 mb-2">🏆 Logbook Prestasi</h4>
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
                        <h4 class="font-bold text-red-700 border-b pb-1 mb-2">⚠️ Catatan Kedisiplinan</h4>
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