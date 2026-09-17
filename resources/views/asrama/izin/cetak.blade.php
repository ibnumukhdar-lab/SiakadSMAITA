@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin - {{ $izin->student->nama_lengkap ?? 'Santri' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px 20px; background: #fff; }
        .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 10px; }
        .kop .inisial { width: 52px; height: 52px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; }
        .kop p { margin: 2px 0 0; font-size: 11.5px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }
        .judul { text-align: center; margin: 18px 0 14px; }
        .judul h2 { margin: 0; font-size: 15px; letter-spacing: 1.5px; text-transform: uppercase; text-decoration: underline; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }
        table.data { width: 100%; border-collapse: collapse; font-size: 12.5px; margin-bottom: 14px; }
        table.data td { padding: 4px 0; vertical-align: top; }
        table.data td.k { width: 190px; color: #475569; }
        table.data td.s { width: 14px; }
        .isi { font-size: 12.5px; line-height: 1.6; margin: 0 0 14px; }
        .ttd { display: flex; justify-content: space-between; gap: 20px; margin-top: 30px; font-size: 12px; }
        .ttd div { text-align: center; }
        .ttd .ruang { height: 62px; }
        .kaki { margin-top: 18px; font-size: 10.5px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        @media print { body { padding: 0; } .tanpa-cetak { display: none !important; } @page { margin: 15mm 14mm; } }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <a href="{{ route('asrama.izin.index') }}" class="btn">⬅️ Kembali</a>
        <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak Surat</button>
    </div>

    <div class="kop">
        <div class="inisial">{{ strtoupper(substr($namaSekolah, 0, 1)) }}</div>
        <div>
            <h1>{{ $namaSekolah }}</h1>
            <p>{{ $motto }}</p>
        </div>
    </div>

    <div class="judul">
        <h2>Surat Keterangan Izin Santri Asrama</h2>
        <p>Nomor: {{ str_pad((string) $izin->id, 4, '0', STR_PAD_LEFT) }}/ASR/{{ \Carbon\Carbon::parse($izin->mulai)->format('m/Y') }}</p>
    </div>

    <p class="isi">Yang bertanda tangan di bawah ini menerangkan bahwa santri berikut mendapat izin meninggalkan asrama:</p>

    <table class="data">
        <tr><td class="k">Nama Santri</td><td class="s">:</td><td><strong>{{ $izin->student->nama_lengkap ?? '-' }}</strong></td></tr>
        <tr><td class="k">Kelas</td><td class="s">:</td><td>{{ $izin->student->kelas ?? '-' }}</td></tr>
        <tr><td class="k">Kamar / Kamar Asrama</td><td class="s">:</td><td>{{ $izin->kamar->nama_kamar ?? '-' }} ({{ ucfirst($izin->kamar->kategori ?? '-') }})</td></tr>
        <tr><td class="k">Jenis Izin</td><td class="s">:</td><td>{{ $izin->labelJenis() }}</td></tr>
        <tr><td class="k">Tanggal</td><td class="s">:</td><td>{{ \Carbon\Carbon::parse($izin->mulai)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($izin->sampai)->translatedFormat('d F Y') }}</td></tr>
        <tr><td class="k">Jam Boleh Keluar</td><td class="s">:</td><td><strong>{{ str_replace(':', '.', $izin->jamKeluarText()) }} WIB</strong></td></tr>
        <tr><td class="k">Batas Jam Wajib Kembali</td><td class="s">:</td><td><strong>{{ str_replace(':', '.', $izin->jamWajibKembaliText()) }} WIB</strong> ({{ \Carbon\Carbon::parse($izin->sampai)->translatedFormat('d F Y') }})</td></tr>
        <tr><td class="k">Alasan</td><td class="s">:</td><td>{{ $izin->alasan }}</td></tr>
        <tr><td class="k">Tujuan</td><td class="s">:</td><td>{{ $izin->tujuan ?: '-' }}</td></tr>
        <tr><td class="k">Penanggung Jawab</td><td class="s">:</td><td>{{ $izin->penanggung_jawab ?: '-' }}</td></tr>
        <tr><td class="k">Status</td><td class="s">:</td><td>{{ $izin->labelStatus() }}{{ $izin->kembali_pada ? ' (kembali ' . \Carbon\Carbon::parse($izin->kembali_pada)->format('d/m/Y') . ')' : '' }}</td></tr>
    </table>

    <p class="isi">Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya. Santri wajib kembali ke asrama paling lambat
        <strong>{{ \Carbon\Carbon::parse($izin->sampai)->translatedFormat('d F Y') }} pukul {{ str_replace(':', '.', $izin->jamWajibKembaliText()) }} WIB</strong>.
        Keterlambatan akan dicatat pada sistem asrama.</p>

    <div class="ttd">
        <div>
            <div>Musyrif / Pengaju</div>
            <div class="ruang"></div>
            <div><strong>{{ $izin->pengaju->name ?? '....................' }}</strong></div>
        </div>
        <div>
            <div>Kepala Diniyah</div>
            <div class="ruang"></div>
            <div><strong>{{ $izin->penyetuju->name ?? '....................' }}</strong></div>
        </div>
    </div>

    <div class="kaki">
        Dicetak {{ now()->format('d/m/Y H:i') }} · {{ $namaSekolah }} · Sistem Akademik
    </div>

</body>
</html>
