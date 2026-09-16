@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $cetak = now()->format('d/m/Y H:i');

    $keterangan = [];
    if ($filter['kelas']) { $keterangan[] = 'Kelas ' . $filter['kelas']; }
    if ($filter['status']) { $keterangan[] = 'Status ' . $filter['status']; }
    if ($filter['jk']) { $keterangan[] = $filter['jk']; }
    if ($filter['angkatan']) { $keterangan[] = 'Angkatan ' . $filter['angkatan']; }
    if ($filter['q']) { $keterangan[] = 'pencarian "' . $filter['q'] . '"'; }
    if ($filter['perlu']) { $keterangan[] = 'filter kelengkapan: ' . $filter['perlu']; }
    $judulFilter = empty($keterangan) ? 'Semua siswa' : implode(' · ', $keterangan);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa - {{ $namaSekolah }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px 20px; background: #fff; }

        .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 13px; font-weight: 600;
               text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }

        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 10px; }
        .kop img { width: 52px; height: 52px; object-fit: contain; }
        .kop .inisial { width: 52px; height: 52px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; letter-spacing: .2px; }
        .kop p { margin: 2px 0 0; font-size: 11.5px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }

        .judul { text-align: center; margin: 16px 0 4px; }
        .judul h2 { margin: 0; font-size: 15px; letter-spacing: 1.5px; text-transform: uppercase; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }

        .kelas { margin-top: 18px; break-inside: auto; }
        .kelas h3 { margin: 0 0 6px; font-size: 13px; background: #f1f5f9; border-left: 4px solid #1e3a8a;
                    padding: 6px 9px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; color: #475569; }
        td.c { text-align: center; }
        tr { break-inside: avoid; page-break-inside: avoid; }

        .kaki { margin-top: 16px; font-size: 10.5px; color: #64748b; display: flex; justify-content: space-between;
                border-top: 1px solid #e2e8f0; padding-top: 8px; }

        @media print {
            body { padding: 0; }
            .tanpa-cetak { display: none !important; }
            @page { margin: 12mm 10mm; }
        }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <a href="{{ route('siswa.index', request()->query()) }}" class="btn">← Kembali ke Data Induk</a>
        <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="kop">
        @if($pengaturan && $pengaturan->logo_path)
            <img src="{{ url('berkas/' . $pengaturan->logo_path) }}" alt="Logo">
        @else
            <span class="inisial">{{ mb_substr($namaSekolah, 0, 1) }}</span>
        @endif
        <div>
            <h1>{{ $namaSekolah }}</h1>
            <p>{{ $motto }}</p>
        </div>
    </div>

    <div class="judul">
        <h2>Daftar Siswa</h2>
        <p>{{ $judulFilter }} — total {{ $total }} siswa</p>
    </div>

    @forelse($perKelas as $kelas => $daftar)
        <div class="kelas">
            <h3>Kelas {{ $kelas }} ({{ $daftar->count() }} siswa)</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width:28px" class="c">No</th>
                        <th style="width:95px">NISN</th>
                        <th style="width:70px">NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width:30px" class="c">JK</th>
                        <th style="width:55px" class="c">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daftar as $i => $s)
                        <tr>
                            <td class="c">{{ $i + 1 }}</td>
                            <td>{{ $s->nisn }}</td>
                            <td>{{ $s->nis ?? '-' }}</td>
                            <td>{{ $s->nama_lengkap }}</td>
                            <td class="c">{{ $s->jk ? substr($s->jk, 0, 1) : '-' }}</td>
                            <td class="c">{{ $s->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p style="font-size:13px;color:#64748b;margin-top:20px">Tidak ada data siswa untuk filter ini.</p>
    @endforelse

    <div class="kaki">
        <span>Dicetak dari SIAKAD {{ $namaSekolah }}</span>
        <span>{{ $cetak }}</span>
    </div>

    <script>
        // Cetak otomatis saat halaman dibuka lewat tombol "Cetak Daftar"
        if (window.location.search.indexOf('auto=1') !== -1) {
            window.addEventListener('load', function () { window.print(); });
        }
    </script>
</body>
</html>
