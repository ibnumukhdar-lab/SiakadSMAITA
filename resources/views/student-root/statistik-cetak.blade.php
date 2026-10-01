<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Statistik Student Root — {{ $labelPeriode }}</title>
<style>
    @page { size: A4; margin: 12mm 12mm 14mm; }
    * { box-sizing: border-box; }
    body { font-family: "Segoe UI", Arial, sans-serif; color: #1e293b; font-size: 11px; margin: 0; }
    .kop { text-align: center; border-bottom: 2.5px solid #0f2b46; padding-bottom: 6px; margin-bottom: 10px; }
    .kop .yayasan { font-size: 11px; letter-spacing: .5px; text-transform: uppercase; color: #475569; }
    .kop h1 { margin: 2px 0 1px; font-size: 16px; color: #0f2b46; letter-spacing: .3px; }
    .kop .alamat { font-size: 10px; color: #64748b; }
    h2 { font-size: 12.5px; color: #0f2b46; margin: 14px 0 6px; border-left: 3px solid #0f2b46; padding-left: 7px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #cbd5e1; padding: 3.5px 6px; font-size: 10.5px; }
    th { background: #eef2f7; text-transform: uppercase; font-size: 9.5px; letter-spacing: .3px; color: #475569; }
    td.ang, th.ang { text-align: right; font-variant-numeric: tabular-nums; }
    .plus { color: #0f766e; font-weight: 600; }
    .min { color: #b0453b; font-weight: 600; }
    .ringkas { display: flex; gap: 8px; margin-bottom: 4px; }
    .kotak { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; }
    .kotak .l { font-size: 9px; text-transform: uppercase; letter-spacing: .3px; color: #64748b; }
    .kotak .n { font-size: 14px; font-weight: 700; color: #0f2b46; }
    .dua { display: flex; gap: 10px; }
    .dua > div { flex: 1; }
    ul { margin: 0; padding-left: 15px; }
    li { margin-bottom: 2px; }
    .ttd { margin-top: 18px; display: flex; justify-content: space-between; text-align: center; font-size: 10.5px; }
    .ttd div { width: 31%; }
    .ttd .garis { margin-top: 46px; border-top: 1px solid #334155; padding-top: 3px; }
    .kecil { font-size: 9.5px; color: #64748b; }
    .peringatan { border: 1px solid #f2dfb8; background: #fffaf0; border-radius: 6px; padding: 7px 9px; font-size: 10.5px; }
</style>
</head>
<body>

<div class="kop">
    <div class="yayasan">{{ $pengaturan->nama_yayasan ?? 'Yayasan Miftahul Ulum Arafah' }}</div>
    <h1>SMA IT ARAFAH</h1>
    <div class="alamat">{{ $pengaturan->alamat ?? 'Sukabumi, Jawa Barat' }} · Laporan Statistik Student Root</div>
</div>

<p style="margin:0 0 8px; font-size: 11px;">
    <strong>Laporan Statistik Program Student Root</strong> — {{ $labelPeriode }}<br>
    <span class="kecil">Kelas: {{ $kelas ?: 'semua' }} · Grup binaan: {{ $grup ? ($grupList->firstWhere('id', $grup)->nama_grup ?? '—') : 'semua' }}
    · Jumlah santri: {{ $jumlah_santri }} · Dicetak: {{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
</p>

<div class="ringkas">
    <div class="kotak"><div class="l">Total entri poin</div><div class="n">{{ number_format($ringkasan['entri'], 0, ',', '.') }}</div></div>
    <div class="kotak"><div class="l">Total poin</div><div class="n">{{ $ringkasan['poin'] > 0 ? '+' : '' }}{{ number_format($ringkasan['poin'], 0, ',', '.') }}</div></div>
    <div class="kotak"><div class="l">Rata-rata per santri</div><div class="n">{{ $ringkasan['rata_rata'] > 0 ? '+' : '' }}{{ number_format($ringkasan['rata_rata'], 1, ',', '.') }}</div></div>
    <div class="kotak"><div class="l">Positif / nol / negatif</div><div class="n">{{ $ringkasan['santri_positif'] }}/{{ $ringkasan['santri_nol'] }}/{{ $ringkasan['santri_negatif'] }}</div></div>
</div>

@if(count($bulanan) > 0)
<h2>A. Perbandingan antar bulan</h2>
<table>
    <thead>
        <tr>
            <th>Bulan</th><th class="ang">Entri</th><th class="ang">Total poin</th>
            <th class="ang">Entri plus</th><th class="ang">Santri dapat plus</th>
            <th class="ang">Entri minus</th><th class="ang">Santri dapat minus</th>
            <th class="ang">Δ poin</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bulanan as $b)
            <tr>
                <td>{{ $b['label'] }}</td>
                <td class="ang">{{ number_format($b['entri'], 0, ',', '.') }}</td>
                <td class="ang {{ $b['poin'] < 0 ? 'min' : 'plus' }}">{{ $b['poin'] > 0 ? '+' : '' }}{{ number_format($b['poin'], 0, ',', '.') }}</td>
                <td class="ang">{{ number_format($b['entri_plus'], 0, ',', '.') }}</td>
                <td class="ang">{{ $b['siswa_plus'] }}</td>
                <td class="ang">{{ number_format($b['entri_min'], 0, ',', '.') }}</td>
                <td class="ang">{{ $b['siswa_min'] }}</td>
                <td class="ang">
                    @if($b['delta_poin'] === null) —
                    @else {{ $b['delta_poin'] > 0 ? '+' : '' }}{{ number_format($b['delta_poin'], 0, ',', '.') }} {{ $b['delta_poin'] < 0 ? '(turun)' : '(naik)' }}
                        @if($b['delta_persen'] !== null) {{ $b['delta_persen'] > 0 ? '+' : '' }}{{ number_format($b['delta_persen'], 1, ',', '.') }}%@endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>B. Aktivitas paling banyak dilakukan</h2>
<div class="dua">
    <div>
        <p style="margin:0 0 4px;font-weight:600;color:#0f766e">Poin positif teratas</p>
        <table>
            <thead><tr><th>Aktivitas</th><th class="ang">Poin</th><th class="ang">Entri</th><th class="ang">Santri</th></tr></thead>
            <tbody>
            @forelse($aktivitas['positif'] as $a)
                <tr><td>{{ $a['nama'] }}</td><td class="ang">{{ $a['poin_kriteria'] > 0 ? '+' : '' }}{{ $a['poin_kriteria'] }}</td><td class="ang">{{ number_format($a['jumlah'], 0, ',', '.') }}</td><td class="ang">{{ $a['siswa'] }}</td></tr>
            @empty
                <tr><td colspan="4">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <p style="margin:0 0 4px;font-weight:600;color:#b0453b">Poin negatif teratas</p>
        <table>
            <thead><tr><th>Aktivitas</th><th class="ang">Poin</th><th class="ang">Entri</th><th class="ang">Santri</th></tr></thead>
            <tbody>
            @forelse($aktivitas['negatif'] as $a)
                <tr><td>{{ $a['nama'] }}</td><td class="ang">{{ $a['poin_kriteria'] }}</td><td class="ang">{{ number_format($a['jumlah'], 0, ',', '.') }}</td><td class="ang">{{ $a['siswa'] }}</td></tr>
            @empty
                <tr><td colspan="4">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(count($catatan) > 0)
<h2>C. Catatan yang paling sering ditulis</h2>
<table>
    <thead><tr><th>Catatan</th><th>Menempel pada kriteria</th><th class="ang">Jumlah</th></tr></thead>
    <tbody>
        @foreach($catatan as $c)
            <tr><td>{{ $c['teks'] }}</td><td>{{ $c['kriteria'] }}</td><td class="ang">{{ $c['jumlah'] }}</td></tr>
        @endforeach
    </tbody>
</table>
@endif

<h2>D. Sebaran per kelas &amp; grup binaan</h2>
<div class="dua">
    <div>
        <table>
            <thead><tr><th>Kelas</th><th class="ang">Santri</th><th class="ang">Entri</th><th class="ang">Poin</th><th class="ang">Rata-rata</th></tr></thead>
            <tbody>
                @foreach($per_kelas as $k)
                    <tr><td>{{ $k['kelas'] }}</td><td class="ang">{{ $k['siswa'] }}</td><td class="ang">{{ number_format($k['entri'], 0, ',', '.') }}</td><td class="ang {{ $k['poin'] < 0 ? 'min' : 'plus' }}">{{ $k['poin'] > 0 ? '+' : '' }}{{ $k['poin'] }}</td><td class="ang">{{ $k['rata'] > 0 ? '+' : '' }}{{ $k['rata'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <table>
            <thead><tr><th>Grup</th><th class="ang">Anggota</th><th class="ang">Entri</th><th class="ang">Poin</th></tr></thead>
            <tbody>
                @foreach($per_grup as $g)
                    <tr><td>{{ $g['grup'] }}</td><td class="ang">{{ $g['anggota'] }}</td><td class="ang">{{ number_format($g['entri'], 0, ',', '.') }}</td><td class="ang {{ $g['poin'] < 0 ? 'min' : 'plus' }}">{{ $g['poin'] > 0 ? '+' : '' }}{{ $g['poin'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($kualitas['entri_salah_kriteria'] > 0 || $kualitas['entri_tanpa_catatan'] > 0)
<h2>E. Catatan kualitas data</h2>
<div class="peringatan">
    <ul>
        @if($kualitas['entri_salah_kriteria'] > 0)
            <li><strong>{{ number_format($kualitas['entri_salah_kriteria'], 0, ',', '.') }} entri</strong> berpoin positif memakai kriteria yang tidak sesuai catatannya (catatan kegiatan asrama) — perlu dirapikan petugas lewat menu input poin.</li>
        @endif
        <li><strong>{{ number_format($kualitas['entri_tanpa_catatan'], 0, ',', '.') }} entri</strong> tanpa catatan.</li>
        <li><strong>{{ count($kualitas['kriteria_tak_terpakai']) }} kriteria</strong> belum pernah dipakai.</li>
    </ul>
</div>
@endif
@endif

<div class="ttd">
    <div>
        <div>Mengetahui,<br>Kepala SMA IT Arafah</div>
        <div class="garis">Fahrizal, M.Pd<br><span class="kecil">NIPA: YMA. 0917 071</span></div>
    </div>
    <div>
        <div>Koordinator Student Root</div>
        <div class="garis">............................................<br><span class="kecil">NIPA: YMA. ............</span></div>
    </div>
    <div>
        <div>Sukabumi, {{ now()->locale('id')->isoFormat('D MMMM Y') }}<br>Petugas Tata Usaha</div>
        <div class="garis">............................................<br><span class="kecil">NIP/NIPA: ....................</span></div>
    </div>
</div>

</body>
</html>
