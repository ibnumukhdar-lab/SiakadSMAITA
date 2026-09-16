@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $tautanVerifikasi = route('siswa.public', $siswa->nisn);
    $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($tautanVerifikasi);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Pelajar - {{ $siswa->nama_lengkap }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px 14px 36px; background: #f1f5f9; font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; }

        .bar { max-width: 360px; margin: 0 auto 16px; display: flex; gap: 8px; }
        .btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 40px;
               border-radius: 10px; border: 1px solid #cbd5e1; background: #fff; color: #334155;
               font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }

        /* ==== KARTU ==== */
        .kartu { width: 100%; max-width: 360px; margin: 0 auto; background: #fff; border-radius: 14px;
                 overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, .12); border: 1px solid #e2e8f0; }

        .kepala { background: linear-gradient(135deg, #1e3a8a, #1e40af); color: #fff; padding: 12px 14px;
                  display: flex; align-items: center; gap: 10px; }
        .kepala .logo { width: 38px; height: 38px; border-radius: 9px; background: rgba(255,255,255,.16);
                        display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .kepala .logo img { width: 100%; height: 100%; object-fit: contain; }
        .kepala .logo span { font-weight: 800; font-size: 17px; }
        .kepala h1 { margin: 0; font-size: 13px; font-weight: 800; line-height: 1.2; }
        .kepala p { margin: 2px 0 0; font-size: 9.5px; letter-spacing: 1.2px; text-transform: uppercase; opacity: .8; }

        .pita { background: #e0e7ff; color: #1e3a8a; font-size: 10px; font-weight: 800; letter-spacing: 1.6px;
                text-transform: uppercase; text-align: center; padding: 5px; }

        .isi { display: flex; gap: 12px; padding: 14px; }
        .foto { width: 78px; height: 104px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc;
                overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .foto img { width: 100%; height: 100%; object-fit: cover; }
        .foto span { font-size: 30px; color: #cbd5e1; }

        .data { min-width: 0; flex: 1; }
        .data .nama { font-size: 14.5px; font-weight: 800; line-height: 1.25; margin: 0 0 6px; word-break: break-word; }
        .baris { display: flex; justify-content: space-between; gap: 8px; font-size: 11px; padding: 2.5px 0;
                 border-bottom: 1px dashed #e2e8f0; }
        .baris:last-child { border-bottom: 0; }
        .baris .label { color: #64748b; }
        .baris .nilai { font-weight: 700; text-align: right; }

        .kaki { display: flex; align-items: center; gap: 10px; padding: 10px 14px 12px; border-top: 1px solid #e2e8f0;
                background: #f8fafc; }
        .kaki img { width: 62px; height: 62px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; padding: 2px; }
        .kaki .ket { font-size: 9.5px; color: #64748b; line-height: 1.5; }
        .kaki .ket strong { color: #334155; }

        @media print {
            body { background: #fff; padding: 0; }
            .tanpa-cetak { display: none !important; }
            .kartu { box-shadow: none; border: 1px solid #94a3b8; width: 85mm; max-width: 85mm; margin: 0 auto; }
            @page { margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <a href="{{ route('siswa.index') }}" class="btn">← Kembali</a>
        <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak Kartu</button>
    </div>

    <div class="kartu">
        <div class="kepala">
            <div class="logo">
                @if($pengaturan && $pengaturan->logo_path)
                    <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" alt="Logo">
                @else
                    <span>{{ mb_substr($namaSekolah, 0, 1) }}</span>
                @endif
            </div>
            <div>
                <h1>{{ $namaSekolah }}</h1>
                <p>{{ $motto }}</p>
            </div>
        </div>

        <div class="pita">Kartu Pelajar</div>

        <div class="isi">
            <div class="foto">
                @if($siswa->foto)
                    <img src="{{ url('berkas/' . $siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}">
                @else
                    <span>👤</span>
                @endif
            </div>

            <div class="data">
                <p class="nama">{{ $siswa->nama_lengkap }}</p>
                <div class="baris"><span class="label">NISN</span><span class="nilai">{{ $siswa->nisn }}</span></div>
                <div class="baris"><span class="label">NIS</span><span class="nilai">{{ $siswa->nis ?? '-' }}</span></div>
                <div class="baris"><span class="label">Kelas</span><span class="nilai">{{ $siswa->kelas ?? '-' }}</span></div>
                <div class="baris"><span class="label">Tahun Ajaran</span><span class="nilai">{{ $siswa->tahun_ajaran ?? '-' }}</span></div>
                <div class="baris"><span class="label">Tinggal</span><span class="nilai">{{ $siswa->tinggal_bersama ?? '-' }}</span></div>
            </div>
        </div>

        <div class="kaki">
            <img src="{{ $qr }}" alt="QR verifikasi">
            <div class="ket">
                Pindai QR untuk <strong>verifikasi keabsahan siswa</strong> di situs SIAKAD.<br>
                Kartu ini milik siswa dan wajib dibawa selama menjadi siswa {{ $namaSekolah }}.
            </div>
        </div>
    </div>

    <script>
        if (window.location.search.indexOf('auto=1') !== -1) {
            window.addEventListener('load', function () { window.print(); });
        }
    </script>
</body>
</html>
