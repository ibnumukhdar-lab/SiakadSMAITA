@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $cetak = now()->format('d/m/Y H:i');
    $judulDivisi = $kategori ? 'Asrama ' . ucfirst($kategori) : 'Semua Divisi';
    $totalPenghuni = $kamars->sum(fn ($k) => $k->members->count());
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penghuni Kamar - {{ $namaSekolah }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px 20px; background: #fff; }
        .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 13px; font-weight: 600;
               text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 10px; }
        .kop .inisial { width: 52px; height: 52px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; }
        .kop p { margin: 2px 0 0; font-size: 11.5px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }
        .judul { text-align: center; margin: 16px 0 6px; }
        .judul h2 { margin: 0; font-size: 15px; letter-spacing: 1.5px; text-transform: uppercase; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }
        .kamar { margin-top: 16px; break-inside: avoid; page-break-inside: avoid; }
        .kamar h3 { margin: 0 0 6px; font-size: 13px; background: #f1f5f9; border-left: 4px solid #1e3a8a;
                    padding: 6px 9px; border-radius: 4px; display: flex; justify-content: space-between; }
        .kamar h3 span { font-weight: 500; color: #475569; font-size: 11.5px; }
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; color: #475569; }
        td.c { text-align: center; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .kosong { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; font-size: 12px; color: #94a3b8; text-align: center; }
        .kaki { margin-top: 18px; font-size: 10.5px; color: #64748b; display: flex; justify-content: space-between;
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
        <a href="{{ route('asrama.kamar.index') }}" class="btn">⬅️ Kembali</a>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('asrama.kamar.cetak', ['kategori' => 'putra']) }}" class="btn {{ $kategori === 'putra' ? 'btn-utama' : '' }}">Putra</a>
            <a href="{{ route('asrama.kamar.cetak', ['kategori' => 'putri']) }}" class="btn {{ $kategori === 'putri' ? 'btn-utama' : '' }}">Putri</a>
            <a href="{{ route('asrama.kamar.cetak') }}" class="btn {{ $kategori ? '' : 'btn-utama' }}">Semua</a>
            <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak</button>
        </div>
    </div>

    <div class="kop">
        <div class="inisial">{{ strtoupper(substr($namaSekolah, 0, 1)) }}</div>
        <div>
            <h1>{{ $namaSekolah }}</h1>
            <p>{{ $motto }}</p>
        </div>
    </div>

    <div class="judul">
        <h2>Daftar Penghuni Kamar Asrama</h2>
        <p>{{ $judulDivisi }} · {{ $kamars->count() }} kamar · {{ $totalPenghuni }} penghuni · dicetak {{ $cetak }}</p>
    </div>

    @forelse($kamars as $kamar)
        <div class="kamar">
            <h3>
                <span style="font-weight:800; color:#0f172a;">KAMAR {{ strtoupper($kamar->nama_kamar) }} ({{ ucfirst($kamar->kategori) }})</span>
                <span>Musyrif: {{ $kamar->musyrif->name ?? '-' }} · Kapasitas {{ $kamar->kapasitas }} · Penghuni {{ $kamar->members->count() }}</span>
            </h3>
            @if($kamar->members->isEmpty())
                <div class="kosong">Belum ada penghuni pada kamar ini.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th style="width:32px;">No</th>
                            <th>Nama Siswa</th>
                            <th style="width:70px;">Kelas</th>
                            <th style="width:95px;">Masuk</th>
                            <th style="width:120px;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kamar->members as $i => $m)
                            <tr>
                                <td class="c">{{ $i + 1 }}</td>
                                <td>{{ $m->student->nama_lengkap ?? 'Siswa dihapus' }}</td>
                                <td class="c">{{ $m->student->kelas ?? '-' }}</td>
                                <td class="c">{{ $m->tanggal_masuk ? \Carbon\Carbon::parse($m->tanggal_masuk)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $m->catatan ?: '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <div class="kosong">Tidak ada kamar pada filter ini.</div>
    @endforelse

    <div class="kaki">
        <span>Dicetak oleh: {{ $dicetakOleh }}</span>
        <span>{{ $namaSekolah }} · Sistem Akademik</span>
    </div>

</body>
</html>
