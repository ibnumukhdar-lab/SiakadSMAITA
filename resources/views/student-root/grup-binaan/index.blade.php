<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👥 Grup Binaan Saya
        </h2>
    </x-slot>

    <!-- =========================================================
         CUSTOM CSS MURNI (TANPA TAILWIND UTILITIES)
         ========================================================= -->
    <style>
        .grup-wrapper {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 40px 0;
            max-width: 1280px;
            margin: 0 auto;
        }

        /* --- 1. BANNER SUMMARY --- */
        .g-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            border-radius: 20px;
            padding: 35px 40px;
            color: #ffffff; 
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25); margin-bottom: 45px;
            position: relative; overflow: hidden; transition: all 0.3s ease;
        }
        .g-banner::after {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;
        }
        .g-banner-left { display: flex; align-items: center; gap: 25px; z-index: 2; }
        .g-banner-avatar {
            width: 100px; height: 100px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3);
            object-fit: cover; background: #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .g-banner-avatar-fallback {
            width: 100px; height: 100px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); 
            background: #ffffff; color: #1e3a8a; display: flex; align-items: center; justify-content: center;
            font-size: 36px; font-weight: 900; box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .g-banner-title { font-size: 32px; font-weight: 900; margin: 0 0 5px 0; letter-spacing: -0.5px; }
        .g-banner-subtitle { font-size: 15px; color: #93c5fd; margin: 0 0 12px 0; font-weight: 600; }
        .g-banner-badge {
            display: inline-block; background: rgba(0,0,0,0.3); padding: 6px 15px; border-radius: 50px; 
            font-size: 13px; font-weight: 800; border: 1px solid rgba(255,255,255,0.1);
        }
        .g-banner-right {
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);
            padding: 20px 30px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2);
            text-align: right; z-index: 2;
        }
        .g-trend-label { font-size: 12px; font-weight: 800; color: #bfdbfe; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px; }
        .g-trend-number { font-size: 48px; font-weight: 900; line-height: 1; margin: 0 0 10px 0; }
        .g-trend-indicator {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 16px; border-radius: 50px; font-size: 13px; font-weight: 800;
        }
        .g-trend-up { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
        .g-trend-down { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
        .g-trend-flat { background: rgba(255, 255, 255, 0.1); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.2); }

        /* --- KELAS DINAMIS JIKA WARNA GRUP TERANG --- */
        .g-banner.tema-terang { color: #0f172a; }
        .g-banner.tema-terang::after { background: rgba(0,0,0,0.05); }
        .g-banner.tema-terang .g-banner-subtitle { color: #475569; }
        .g-banner.tema-terang .g-banner-avatar, 
        .g-banner.tema-terang .g-banner-avatar-fallback { border-color: rgba(0,0,0,0.15); }
        .g-banner.tema-terang .g-banner-avatar-fallback { background: #0f172a; color: #ffffff; }
        .g-banner.tema-terang .g-banner-badge { background: rgba(255,255,255,0.7); color: #0f172a; border-color: rgba(0,0,0,0.2); }
        .g-banner.tema-terang .g-banner-right { background: rgba(255, 255, 255, 0.6); border-color: rgba(0,0,0,0.15); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .g-banner.tema-terang .g-trend-label { color: #475569; }
        .g-banner.tema-terang .g-trend-flat { background: rgba(0,0,0,0.05); color: #334155; border-color: rgba(0,0,0,0.1); }
        .g-banner.tema-terang .g-trend-up { background: rgba(16, 185, 129, 0.15); color: #065f46; border-color: rgba(16, 185, 129, 0.3); }
        .g-banner.tema-terang .g-trend-down { background: rgba(239, 68, 68, 0.15); color: #991b1b; border-color: rgba(239, 68, 68, 0.3); }

        /* --- 2. GRID CARDS SISWA (5 KOLOM) --- */
        .g-section-title { font-size: 22px; font-weight: 900; color: #1e293b; margin: 0 0 20px 0; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0; display: flex; align-items: center; gap: 10px; }
        .g-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 25px; margin-bottom: 50px; }
        .g-card { background: #ffffff; border-radius: 16px; padding: 30px 20px 25px 20px; text-align: center; border-top: 10px solid #ccc; box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08); display: flex; flex-direction: column; align-items: center; transition: all 0.3s ease; text-decoration: none; }
        .g-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px rgba(0, 0, 0, 0.15); }
        .g-card.hijau { border-top-color: #16a34a; }
        .g-card.merah { border-top-color: #dc2626; }
        .g-card.abu { border-top-color: #64748b; }
        .g-card-ava-wrap { position: relative; margin-bottom: 25px; }
        .g-card-ava { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 5px solid #fff; box-shadow: 0 6px 15px rgba(0,0,0,0.15); transition: transform 0.3s ease; }
        .g-card:hover .g-card-ava { transform: scale(1.05); }
        .g-card-ava-fallback { width: 90px; height: 90px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: #fff; border: 5px solid #fff; box-shadow: 0 6px 15px rgba(0,0,0,0.15); transition: transform 0.3s ease; }
        .g-card:hover .g-card-ava-fallback { transform: scale(1.05); }
        .g-card-badge { position: absolute; bottom: -12px; left: 50%; transform: translateX(-50%); background: #fff; color: #0f172a; font-size: 12px; font-weight: 900; padding: 5px 15px; border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; white-space: nowrap; }
        .g-card-name { font-size: 16px; font-weight: 900; color: #0f172a; margin: 0 0 5px 0; text-transform: uppercase; line-height: 1.2; transition: color 0.2s; }
        .g-card:hover .g-card-name { color: #3b82f6; }
        .g-card-nisn { font-size: 12px; font-weight: 700; color: #64748b; margin: 0 0 25px 0; }
        .g-card-pointbox { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; width: 100%; padding: 18px 10px; margin-top: auto; box-shadow: inset 0 3px 6px rgba(0,0,0,0.02); }
        .g-card-point { font-size: 36px; font-weight: 900; line-height: 1; margin: 0 0 6px 0; }
        .g-card-point-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; margin: 0; }
        .g-card-hint { font-size: 11px; font-weight: bold; color: #94a3b8; margin-top: 15px; opacity: 0; transition: opacity 0.3s ease; }
        .g-card:hover .g-card-hint { opacity: 1; }

        /* --- 3. TABEL RIWAYAT --- */
        .g-table-wrap { background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 12px 25px rgba(0,0,0,0.08); border: 1px solid #f1f5f9; }
        .g-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .g-table th { background: #0f172a; color: #ffffff; padding: 16px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; text-align: left; }
        .g-table th.center { text-align: center; }
        .g-table td { padding: 18px 16px; border-bottom: 1px solid #e2e8f0; font-size: 15px; color: #334155; vertical-align: middle; }
        .g-table tr:hover td { background: #f8fafc; }
        .g-table-date { font-weight: 700; color: #64748b; }
        .g-table-name { font-weight: 900; color: #0f172a; }
        .g-table-note { display: inline-block; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; font-size: 13px; color: #475569; font-style: italic; border-left: 3px solid #cbd5e1; }
        .g-pill { display: inline-block; padding: 8px 20px; border-radius: 50px; font-size: 15px; font-weight: 900; text-align: center; min-width: 80px; }
        .g-pill-plus { background: #dcfce7; color: #16a34a; }
        .g-pill-minus { background: #fee2e2; color: #dc2626; }

        /* --- 4. CSS POP-UP (MODAL) --- */
        .g-modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);
            z-index: 9999; display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .g-modal-overlay.active { display: flex; opacity: 1; }
        .g-modal-box {
            background: #ffffff; border-radius: 16px; width: 100%; max-width: 450px;
            padding: 30px; box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            transform: translateY(20px) scale(0.95); transition: all 0.3s ease;
        }
        .g-modal-overlay.active .g-modal-box { transform: translateY(0) scale(1); }
        .g-form-label { display: block; font-size: 13px; font-weight: 800; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
        .g-form-input { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; margin-bottom: 20px; transition: border-color 0.2s; font-family: inherit; }
        .g-form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .g-btn { padding: 10px 20px; border-radius: 10px; font-weight: 800; font-size: 14px; cursor: pointer; border: none; transition: all 0.2s; }
        .g-btn-cancel { background: #f1f5f9; color: #475569; }
        .g-btn-cancel:hover { background: #e2e8f0; }
        .g-btn-save { background: #3b82f6; color: #ffffff; }
        .g-btn-save:hover { background: #2563eb; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); }

        /* Responsif */
        @media (max-width: 1200px) { .g-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 992px) { .g-grid { grid-template-columns: repeat(3, 1fr); } .g-banner { flex-direction: column; text-align: center; } .g-banner-left { flex-direction: column; } .g-banner-right { text-align: center; align-items: center; width: 100%; } }
        @media (max-width: 768px) { .g-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .g-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="grup-wrapper">
            
            @if(!$group)
                <!-- KOSONG -->
                <div style="background: #fff; padding: 60px 20px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                    <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
                    <h3 style="font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">Belum Ada Grup Binaan</h3>
                    <p style="color: #64748b; font-size: 16px; max-width: 600px; margin: 0 auto;">Anda belum ditugaskan sebagai guru mentor. Silakan hubungi Administrator atau Kepala Sekolah untuk pengaturan lebih lanjut.</p>
                </div>
            @else
                
                <!-- BAGIAN 1: BANNER SUMMARY -->
                @php
                    $warnaGrup = $group->warna_grup ?? $group->warna ?? $group->color ?? null;
                    $isLight = false;
                    
                    if ($warnaGrup) {
                        $hex = ltrim($warnaGrup, '#');
                        if (ctype_xdigit($hex) && (strlen($hex) == 6 || strlen($hex) == 3)) {
                            if (strlen($hex) == 3) {
                                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                            }
                            $r = hexdec(substr($hex, 0, 2));
                            $g = hexdec(substr($hex, 2, 2));
                            $b = hexdec(substr($hex, 4, 2));
                            
                            $luma = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                            if ($luma > 150) { $isLight = true; }
                        }
                    }
                @endphp
                
                <div class="g-banner {{ $isLight ? 'tema-terang' : '' }}" style="{{ $warnaGrup ? 'background: ' . $warnaGrup . ';' : '' }}">
                    <div class="g-banner-left">
                        @php
                            $fotoGuru = Auth::user()->foto ?? Auth::user()->avatar ?? Auth::user()->foto_profil ?? null;
                        @endphp

                        @if($fotoGuru)
                            <!-- JALUR DIUBAH KE /BERKAS/ -->
                            <img src="{{ url('berkas/' . $fotoGuru) }}" class="g-banner-avatar" alt="Foto Mentor">
                        @else
                            <div class="g-banner-avatar-fallback">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        @endif
                        
                        <div>
                            <h3 class="g-banner-title">Kelompok {{ $group->nama_grup }}</h3>
                            <p class="g-banner-subtitle">Mentor Utama: {{ Auth::user()->name }}</p>
                            <span class="g-banner-badge">{{ count($members) }} Anggota Aktif</span>
                        </div>
                    </div>

                    <div class="g-banner-right">
                        <span class="g-trend-label">Total Poin Grup</span>
                        <h2 class="g-trend-number">{{ number_format($totalPoinGrup ?? 0, 0, ',', '.') }}</h2>
                        
                        @if(isset($trendPoin) && $trendPoin > 0)
                            <div class="g-trend-indicator g-trend-up">▲ Naik {{ $trendPoin }} Poin</div>
                        @elseif(isset($trendPoin) && $trendPoin < 0)
                            <div class="g-trend-indicator g-trend-down">▼ Turun {{ abs($trendPoin) }} Poin</div>
                        @else
                            <div class="g-trend-indicator g-trend-flat">▬ Poin Stabil</div>
                        @endif
                    </div>
                </div>

                <!-- BAGIAN 2: GRID CARDS (5 KOLOM) -->
                <div>
                    <h4 class="g-section-title">🎓 Anak Didik Anda</h4>
                    <div class="g-grid">
                        @forelse($members as $siswa)
                            @php
                                $warnaHex = '#64748b'; $warnaClass = 'abu'; $tanda = '';
                                if ($siswa->total_poin > 0) {
                                    $warnaHex = '#16a34a'; $warnaClass = 'hijau'; $tanda = '+';
                                } elseif ($siswa->total_poin < 0) {
                                    $warnaHex = '#dc2626'; $warnaClass = 'merah';
                                }
                            @endphp
                            
                            <!-- PERUBAHAN: Tag <div> diganti jadi <a> agar seluruh kartu bisa di-klik -->
                            <a href="{{ route('sr.poin.history', $siswa->id) }}" class="g-card {{ $warnaClass }}" title="Klik untuk melihat detail histori poin {{ $siswa->nama_lengkap }}">
                                <div class="g-card-ava-wrap">
                                    @if(isset($siswa->foto) && $siswa->foto)
                                        <!-- JALUR DIUBAH KE /BERKAS/ -->
                                        <img src="{{ url('berkas/'.$siswa->foto) }}" class="g-card-ava" style="border-color: {{ $warnaHex }};">
                                    @else
                                        <div class="g-card-ava-fallback" style="background-color: {{ $warnaHex }}; border-color: {{ $warnaHex }};">
                                            {{ substr($siswa->nama_lengkap, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="g-card-badge">Kelas {{ $siswa->kelas }}</div>
                                </div>
                                
                                <h5 class="g-card-name">{{ $siswa->nama_lengkap }}</h5>
                                <p class="g-card-nisn">NISN: {{ $siswa->nisn }}</p>
                                
                                <div class="g-card-pointbox">
                                    <h3 class="g-card-point" style="color: {{ $warnaHex }};">
                                        {{ $tanda }}{{ number_format($siswa->total_poin, 0, ',', '.') }}
                                    </h3>
                                    <p class="g-card-point-label">Total Poin</p>
                                </div>
                                
                                <!-- Tooltip halus saat di-hover -->
                                <div class="g-card-hint">Tampilkan Histori ↗</div>
                            </a>
                        @empty
                            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #fff; border-radius: 16px; color: #64748b;">
                                Belum ada siswa yang ditambahkan ke kelompok ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- BAGIAN 3: TABEL RIWAYAT -->
                <div class="g-table-wrap">
                    <h4 class="g-section-title" style="border: none; padding: 0;">⚡ Riwayat Aktivitas Grup Terbaru</h4>
                    
                    <!-- ALERT NOTIFIKASI -->
                    @if(session('success'))
                        <div style="background: #dcfce7; color: #166534; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold; border-left: 4px solid #16a34a;">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div style="background: #fee2e2; color: #991b1b; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: bold; border-left: 4px solid #dc2626;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div style="overflow-x: auto;">
                        <table class="g-table">
                            <thead>
                                <tr>
                                    <th style="border-top-left-radius: 10px; width: 130px;">Tanggal</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Perilaku</th>
                                    <th style="width: 250px;">Catatan Penilai</th>
                                    <th class="center" style="width: 100px;">Poin</th>
                                    <th class="center" style="border-top-right-radius: 10px; width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayatTerbaru ?? [] as $riwayat)
                                    @php
                                        $catatan = $riwayat->catatan ?? $riwayat->keterangan ?? '';
                                    @endphp
                                <tr>
                                    <td class="g-table-date">
                                        {{ \Carbon\Carbon::parse($riwayat->created_at)->translatedFormat('d M Y') }}
                                        <div style="font-size: 11px; font-weight: normal; margin-top: 2px;">{{ \Carbon\Carbon::parse($riwayat->created_at)->format('H:i') }}</div>
                                    </td>
                                    <td class="g-table-name">{{ $riwayat->student->nama_lengkap ?? '-' }}</td>
                                    <td>
                                        <div style="font-weight: 700;">{{ $riwayat->criteria->nama_perilaku ?? 'Perilaku Dihapus' }}</div>
                                    </td>
                                    <td>
                                        @if($catatan)
                                            <span class="g-table-note">"{{ $catatan }}"</span>
                                        @else
                                            <span style="color: #cbd5e1; font-style: italic; font-size: 13px;">- Tidak ada catatan -</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if($riwayat->poin > 0)
                                            <span class="g-pill g-pill-plus">+{{ $riwayat->poin }}</span>
                                        @else
                                            <span class="g-pill g-pill-minus">{{ $riwayat->poin }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 8px; justify-content: center;">
                                            <!-- Tombol Edit AMAN dengan Data Attributes -->
                                            <button type="button" 
                                                data-id="{{ $riwayat->id }}" 
                                                data-poin="{{ $riwayat->poin }}" 
                                                data-catatan="{{ $catatan }}"
                                                onclick="openEditModal(this)" 
                                                style="background: #f1f5f9; color: #3b82f6; padding: 6px; border-radius: 6px; transition: all 0.2s; border: none; cursor: pointer;" title="Edit Data" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f1f5f9'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            
                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('poin.destroy', $riwayat->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus riwayat poin ini? Total poin siswa akan dihitung ulang secara otomatis.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background: #f1f5f9; color: #ef4444; padding: 6px; border-radius: 6px; transition: all 0.2s; border: none; cursor: pointer;" title="Batalkan/Hapus" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#f1f5f9'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #64748b; font-style: italic;">
                                        Belum ada riwayat poin (prestasi/pelanggaran) di grup Anda saat ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- MODAL POP-UP EDIT -->
                <div id="modalEdit" class="g-modal-overlay">
                    <div class="g-modal-box">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
                            <h3 style="margin: 0; font-size: 18px; font-weight: 900; color: #0f172a;">✏️ Edit Data Poin</h3>
                            <button type="button" onclick="closeEditModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
                        </div>
                        
                        <form id="formEdit" method="POST" action="">
                            @csrf
                            @method('PUT')
                            
                            <label class="g-form-label">Ubah Nilai Poin</label>
                            <input type="number" name="poin" id="inputPoin" class="g-form-input" required>
                            
                            <label class="g-form-label">Catatan / Alasan</label>
                            <textarea name="catatan" id="inputCatatan" class="g-form-input" rows="4" placeholder="Kosongkan jika tidak ada catatan..."></textarea>
                            
                            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                                <button type="button" onclick="closeEditModal()" class="g-btn g-btn-cancel">Batal</button>
                                <button type="submit" class="g-btn g-btn-save">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- JAVASCRIPT UNTUK MENGENDALIKAN MODAL -->
                <script>
                    function openEditModal(button) {
                        // Mengambil data dari atribut HTML tombol yang ditekan (Lebih aman dari karakter unik)
                        let id = button.getAttribute('data-id');
                        let poin = button.getAttribute('data-poin');
                        let catatan = button.getAttribute('data-catatan');

                        // Membangun URL tujuan action form secara dinamis
                        let baseUrl = "{{ url('/poin') }}";
                        document.getElementById('formEdit').action = baseUrl + '/' + id;
                        
                        // Memasukkan data lama ke dalam form input
                        document.getElementById('inputPoin').value = poin;
                        document.getElementById('inputCatatan').value = catatan;
                        
                        // Menampilkan modal pop-up
                        document.getElementById('modalEdit').classList.add('active');
                    }

                    function closeEditModal() {
                        document.getElementById('modalEdit').classList.remove('active');
                    }
                </script>

            @endif
        </div>
    </div>
</x-app-layout>