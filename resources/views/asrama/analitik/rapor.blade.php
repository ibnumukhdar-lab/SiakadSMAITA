@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $labelBulan = \App\Http\Controllers\AsramaPeringkatController::labelBulan($bulan);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Asrama{{ $kamar ? ' - ' . $kamar->nama_kamar : '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px 20px; background: #fff; }
        .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .filter { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 16px; }
        .filter label { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #94a3b8; margin-bottom: 4px; }
        .filter select { height: 36px; border: 1px solid #cbd5e1; border-radius: 9px; padding: 0 10px; font-size: 13px; background: #fff; }
        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 10px; }
        .kop .inisial { width: 52px; height: 52px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; }
        .kop p { margin: 2px 0 0; font-size: 11.5px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }
        .judul { text-align: center; margin: 16px 0 12px; }
        .judul h2 { margin: 0; font-size: 15px; letter-spacing: 1.2px; text-transform: uppercase; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }
        .ringkas { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .chip { border: 1px solid #e2e8f0; border-radius: 999px; padding: 5px 12px; font-size: 11.5px; font-weight: 700; color: #334155; background: #f8fafc; }
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; color: #475569; }
        td.c { text-align: center; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .ttd { display: flex; justify-content: flex-end; margin-top: 28px; font-size: 12px; }
        .ttd div { text-align: center; }
        .ttd .ruang { height: 62px; }
        .kaki { margin-top: 16px; font-size: 10.5px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 8px; display: flex; justify-content: space-between; }
        .kosong { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 18px; font-size: 12.5px; color: #94a3b8; text-align: center; }
        @media print { body { padding: 0; } .tanpa-cetak { display: none !important; } @page { margin: 12mm 10mm; } }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <a href="{{ route('asrama.analitik', ['bulan' => $bulan]) }}" class="btn">⬅️ Kembali ke Analitik</a>
        @if($kamar)
            <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak Rapor</button>
        @endif
    </div>

    <form method="GET" action="{{ route('asrama.analitik.rapor') }}" class="filter tanpa-cetak">
        <div>
            <label>Kamar</label>
            <select name="kamar_id" required>
                <option value="">-- pilih kamar --</option>
                @foreach($daftarKamar as $k)
                    <option value="{{ $k->id }}" @selected($kamar && $kamar->id === $k->id)>{{ $k->nama_kamar }} ({{ ucfirst($k->kategori) }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Bulan</label>
            <select name="bulan">
                @foreach(($daftarBulan ?: [now()->format('Y-m')]) as $b)
                    <option value="{{ $b }}" @selected($bulan === $b)>{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan($b) }}</option>
                @endforeach
                @if(! in_array(now()->format('Y-m'), $daftarBulan, true))
                    <option value="{{ now()->format('Y-m') }}" @selected($bulan === now()->format('Y-m'))>{{ \App\Http\Controllers\AsramaPeringkatController::labelBulan(now()->format('Y-m')) }}</option>
                @endif
            </select>
        </div>
        <button type="submit" class="btn btn-utama">Tampilkan</button>
    </form>

    @if(! $kamar)
        <div class="kosong">Pilih kamar dan bulan untuk menampilkan rapor asrama per siswa.</div>
    @else
        <div class="kop">
            <div class="inisial">{{ strtoupper(substr($namaSekolah, 0, 1)) }}</div>
            <div>
                <h1>{{ $namaSekolah }}</h1>
                <p>{{ $motto }}</p>
            </div>
        </div>

        <div class="judul">
            <h2>Rapor Asrama — Kamar {{ strtoupper($kamar->nama_kamar) }}</h2>
            <p>{{ ucfirst($kamar->kategori) }} · Musyrif: {{ $kamar->musyrif->name ?? '-' }} · Bulan {{ $labelBulan }}</p>
        </div>

        <div class="ringkas">
            <span class="chip">Rata-rata nilai kamar: {{ $skorKamar !== null ? $skorKamar . '/25 (' . \App\Http\Controllers\AsramaPenilaianController::persenSkor($skorKamar) . '%)' : 'belum ada inspeksi' }}</span>
            <span class="chip">👑 Terbersih: {{ $terbersih }}×</span>
            <span class="chip">⚠️ Terkotor: {{ $terkotor }}×</span>
            <span class="chip">Penghuni aktif: {{ $anggota->count() }} orang</span>
        </div>

        @if($anggota->isEmpty())
            <div class="kosong">Kamar ini belum punya penghuni aktif.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:28px;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width:52px;">Kelas</th>
                        <th style="width:70px;">Masuk</th>
                        <th style="width:38px;">H</th>
                        <th style="width:38px;">T</th>
                        <th style="width:38px;">I</th>
                        <th style="width:38px;">S</th>
                        <th style="width:38px;">P</th>
                        <th style="width:38px;">A</th>
                        <th style="width:58px;">Poin Asrama</th>
                        <th>Catatan Izin / Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anggota as $i => $a)
                        @php
                            $sid = $a->student_id;
                            $ab = $ringkasAbsensi[$sid] ?? null;
                            $poin = $poinAsrama[$sid]->poin ?? null;
                            $izinSiswa = $izinBulan[$sid] ?? collect();
                            $catatan = $izinSiswa->map(function ($z) {
                                $teks = $z->labelJenis() . ' (' . \Carbon\Carbon::parse($z->mulai)->format('d/m') . ' ' . str_replace(':', '.', $z->jamKeluarText())
                                    . ' – ' . \Carbon\Carbon::parse($z->sampai)->format('d/m') . ' ' . str_replace(':', '.', $z->jamWajibKembaliText()) . ')';
                                if ((int) $z->terlambat_menit > 0) {
                                    $teks .= ' ⚠️ terlambat ' . (int) $z->terlambat_menit . ' menit';
                                }
                                return $teks;
                            })->implode('; ');
                        @endphp
                        <tr>
                            <td class="c">{{ $i + 1 }}</td>
                            <td>{{ $a->student->nama_lengkap ?? 'Siswa dihapus' }}</td>
                            <td class="c">{{ $a->student->kelas ?? '-' }}</td>
                            <td class="c">{{ $a->tanggal_masuk ? \Carbon\Carbon::parse($a->tanggal_masuk)->format('d/m/Y') : '-' }}</td>
                            <td class="c">{{ $ab ? (int) $ab->hadir : '-' }}</td>
                            <td class="c">{{ $ab ? (int) $ab->telat : '-' }}</td>
                            <td class="c">{{ $ab ? (int) $ab->izin : '-' }}</td>
                            <td class="c">{{ $ab ? (int) $ab->sakit : '-' }}</td>
                            <td class="c">{{ $ab ? (int) $ab->pulang : '-' }}</td>
                            <td class="c"><strong>{{ $ab ? (int) $ab->alpa : '-' }}</strong></td>
                            <td class="c"><strong>{{ $poin !== null ? ($poin > 0 ? '+' . $poin : $poin) : '0' }}</strong></td>
                            <td>{{ $catatan ?: ($a->catatan ?: '') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="font-size:11px; color:#64748b; margin-top:8px;">
                Keterangan: H=Hadir, T=Telat, I=Izin, S=Sakit, P=Pulang, A=Alpa (dari absensi asrama bulan ini).
                Poin Asrama = poin Student Root yang berasal dari inspeksi kebersihan kamar (terbersih +1 / terkotor −1).
            </p>

            <div class="ttd">
                <div>
                    <div>Musyrif Kamar</div>
                    <div class="ruang"></div>
                    <div><strong>{{ $kamar->musyrif->name ?? '....................' }}</strong></div>
                </div>
            </div>
        @endif

        <div class="kaki">
            <span>Dicetak {{ now()->format('d/m/Y H:i') }}</span>
            <span>{{ $namaSekolah }} · Sistem Akademik</span>
        </div>
    @endif

</body>
</html>
