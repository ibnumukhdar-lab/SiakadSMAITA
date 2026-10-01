@php
    /**
     * RAPOR DINIYAH BERBAHASA ARAB (1 Okt 2026)
     * =====================================================================
     * Keputusan Kepala Sekolah: bahasa Arab DENGAN TERJEMAHAN KECIL bahasa
     * Indonesia di bawah tiap label; nama Latin punya kolom transliterasi Arab;
     * rekap kehadiran diambil otomatis dari absensi pertemuan.
     *
     * Semua label diambil dari App\Support\DiniyahArab supaya layar & cetakan
     * memakai istilah yang sama. JANGAN menulis label Arab langsung di sini.
     */
    use App\Support\DiniyahArab;

    $L = fn (string $kunci, string $bagian = 'arab') => DiniyahArab::label($kunci, $bagian);
    $s = $siswa;
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $daftarNilai = $nilai['baris'];
    $sudahDinilai = $nilai['terisi'] > 0;
@endphp
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف الدرجات — Rapor Diniyah {{ $s->nama_lengkap }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e2e8f0; font-family: Inter, system-ui, sans-serif; color: #0f172a; }
        .bar { max-width: 820px; margin: 16px auto 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .bar a, .bar button { font: inherit; font-size: 12.5px; font-weight: 800; padding: 8px 14px; border-radius: 9px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; cursor: pointer; text-decoration: none; }
        .bar button { background: #1e293b; color: #fff; border-color: #1e293b; }
        .hal { max-width: 820px; margin: 16px auto 32px; background: #fff; padding: 34px 38px; box-shadow: 0 1px 3px rgba(15,23,42,.15); }
        .arab { font-family: Amiri, serif; direction: rtl; }
        .kop { text-align: center; border-bottom: 3px double #0f172a; padding-bottom: 10px; }
        .kop .nama { font-family: Amiri, serif; font-size: 26px; font-weight: 700; }
        .kop .sub { font-family: Amiri, serif; font-size: 16px; }
        .kop .sub-id { font-size: 11.5px; font-weight: 700; color: #475569; margin-top: 2px; }
        .judul { text-align: center; margin: 14px 0 6px; font-family: Amiri, serif; font-size: 21px; font-weight: 700; }
        .judul-id { text-align: center; font-size: 11.5px; font-weight: 700; color: #475569; }
        table { border-collapse: collapse; width: 100%; }
        .identitas { margin-top: 16px; }
        .identitas td { padding: 3px 6px; vertical-align: top; font-size: 12.5px; }
        .identitas .lb { font-family: Amiri, serif; font-size: 15px; font-weight: 700; white-space: nowrap; width: 150px; }
        .identitas .lb-id { display: block; font-family: Inter, sans-serif; font-size: 10px; font-weight: 700; color: #64748b; direction: ltr; }
        .identitas .isi { font-weight: 800; }
        .nama-arab { font-family: Amiri, serif; direction: rtl; font-size: 19px; font-weight: 700; }
        .blok { margin-top: 18px; }
        .blok > h2 { font-family: Amiri, serif; font-size: 17px; margin: 0 0 6px; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .blok > h2 span { display: block; font-family: Inter, sans-serif; font-size: 10.5px; font-weight: 700; color: #64748b; direction: ltr; }
        table.tabel th, table.tabel td { border: 1px solid #94a3b8; padding: 5px 7px; font-size: 12.5px; }
        table.tabel th { background: #f1f5f9; font-family: Amiri, serif; font-size: 15px; }
        table.tabel th span { display: block; font-family: Inter, sans-serif; font-size: 9.5px; font-weight: 700; color: #64748b; direction: ltr; }
        .tengah { text-align: center; }
        .angka { font-weight: 800; }
        .predikat { font-family: Amiri, serif; font-size: 15px; }
        .predikat-id { display: block; font-family: Inter, sans-serif; font-size: 9.5px; color: #64748b; direction: ltr; }
        .baris-jumlah td { background: #f8fafc; font-weight: 800; }
        .dua-kolom { display: flex; gap: 16px; flex-wrap: wrap; }
        .dua-kolom > div { flex: 1 1 320px; }
        .ttd { display: flex; gap: 18px; margin-top: 34px; text-align: center; }
        .ttd > div { flex: 1; }
        .ttd .peran { font-family: Amiri, serif; font-size: 15px; }
        .ttd .peran-id { font-size: 10px; font-weight: 700; color: #64748b; }
        .ttd .ruang { height: 62px; }
        .ttd .nama { font-weight: 800; font-size: 12.5px; border-top: 1px solid #94a3b8; padding-top: 3px; }
        .catatan-box { border: 1px solid #94a3b8; padding: 9px 11px; height: 100%; }
        .catatan-box .judul-kecil { font-family: Amiri, serif; font-size: 15px; font-weight: 700; }
        .catatan-box .judul-kecil-id { font-size: 10px; font-weight: 700; color: #64748b; }
        .kaki { margin-top: 16px; font-size: 10.5px; font-weight: 700; color: #64748b; border-top: 1px solid #cbd5e1; padding-top: 8px; }
        @media print {
            body { background: #fff; }
            .bar { display: none !important; }
            .hal { box-shadow: none; margin: 0; max-width: 100%; padding: 0; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body>

<div class="bar">
    @if(empty($modeOrtu))
        <button type="button" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    @endif
    <a href="{{ $kembaliKe ?? route('diniyah.rapor', ['periode' => $periode?->id]) }}">&larr; Kembali</a>
    @if(! empty($modeOrtu))
        <span style="font-size: 12px; font-weight: 700; color: #64748b;">Untuk dilihat saja — wali murid tidak dapat mencetak rapor ini.</span>
    @endif
</div>
@if(! empty($modeOrtu))
    <style>@media print { body { display: none !important; } }</style>
@endif

<div class="hal">

    {{-- KOP --}}
    <div class="kop">
        <div class="nama">{{ $L('kop_nama') }}</div>
        <div class="sub">{{ $namaSekolah }} — كوتاوارينغين الشرقية</div>
        <div class="sub-id">Kulliyyat Diiniyyah Al-Arafah · {{ $namaSekolah }}</div>
    </div>

    <div class="judul">{{ $L('rapot') }}</div>
    <div class="judul-id">Rapor hasil belajar kajian diniyah{{ $periode ? ' · ' . $periode->label() : '' }}</div>

    {{-- IDENTITAS --}}
    <table class="identitas">
        <tr>
            <td class="lb">{{ $L('nama') }}<span class="lb-id">Nama santri</span></td>
            <td class="isi">{{ $s->nama_lengkap }}</td>
            <td class="lb">{{ $L('kelas') }}<span class="lb-id">Kelas</span></td>
            <td class="isi">{{ $s->kelas }} <span class="arab">({{ DiniyahArab::kelasArab($s->kelas) }})</span></td>
        </tr>
        <tr>
            <td class="lb">{{ $L('nama_arab') }}<span class="lb-id">Nama dalam tulisan Arab</span></td>
            <td class="isi"><span class="nama-arab">{{ $s->nama_arab ?: '.................................' }}</span></td>
            <td class="lb">{{ $L('nisn') }}<span class="lb-id">NISN</span></td>
            <td class="isi">{{ $s->nisn ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lb">{{ $L('semester') }}<span class="lb-id">Semester</span></td>
            <td class="isi"><span class="arab">{{ DiniyahArab::semesterArab($periode?->semester) }}</span> ({{ $periode->semester ?? '-' }})</td>
            <td class="lb">{{ $L('tahun') }}<span class="lb-id">Tahun ajaran</span></td>
            <td class="isi">{{ $tahunHijriah }} / {{ $periode->tahun_ajaran ?? \App\Models\DiniyahPeriode::tahunAjaranKalender() }} م</td>
        </tr>
    </table>

    {{-- A. NILAI PER MATA PELAJARAN --}}
    <div class="blok">
        <h2>{{ $L('mapel') }}<span>Nilai mata pelajaran kajian diniyah</span></h2>

        <table class="tabel">
            <thead>
                <tr>
                    <th style="width: 34px;">م<span>No</span></th>
                    <th>{{ $L('mapel') }}<span>Mata pelajaran</span></th>
                    <th style="width: 70px;">{{ $L('nilai') }}<span>Nilai</span></th>
                    <th style="width: 120px;">{{ $L('predikat') }}<span>Predikat</span></th>
                    <th>{{ $L('catatan') }}<span>Catatan guru</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftarNilai as $i => $baris)
                    <tr>
                        <td class="tengah angka">{{ $i + 1 }}</td>
                        <td>
                            <span class="arab" style="font-size: 15px; font-weight: 700;">{{ $baris['mapel']->nama_arab ?: $baris['mapel']->nama }}</span>
                            <span style="display: block; font-size: 11px; color: #475569; font-weight: 700;">{{ $baris['mapel']->nama }}</span>
                        </td>
                        <td class="tengah angka">{{ $baris['nilai'] ?? '—' }}</td>
                        <td class="tengah predikat">
                            {{ $baris['arab'] ?: '—' }}
                            @if($baris['nilai'] !== null)
                                <span class="predikat-id">{{ DiniyahArab::predikatIndonesia($baris['nilai']) }}</span>
                            @endif
                        </td>
                        <td style="font-size: 11.5px;">{{ $baris['catatan'] ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="tengah" style="font-size: 12px; font-weight: 700; color: #64748b;">
                            Belum ada mata pelajaran diniyah untuk kelas {{ $s->kelas }}.
                        </td>
                    </tr>
                @endforelse
                <tr class="baris-jumlah">
                    <td colspan="2" class="arab" style="font-size: 15px; text-align: right;">{{ $L('jumlah') }}</td>
                    <td class="tengah angka">{{ $nilai['rata'] ?? '—' }}</td>
                    <td colspan="2" class="predikat">
                        {{ $nilai['rata'] !== null ? DiniyahArab::predikatArab($nilai['rata']) : '—' }}
                        @if($nilai['rata'] !== null)
                            <span class="predikat-id">{{ DiniyahArab::predikatIndonesia($nilai['rata']) }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <p style="font-size: 11px; font-weight: 700; color: #475569; margin-top: 6px;">
            Terisi {{ $nilai['terisi'] }} dari {{ $nilai['jumlah_mapel'] }} mata pelajaran.
            @if(! $sudahDinilai)
                Seluruh nilai masih kosong — musyrif/musyrifah belum mengisi.
            @endif
        </p>
    </div>

    {{-- B. KEHADIRAN + CATATAN --}}
    <div class="blok">
        <h2>{{ $L('kehadiran') }}<span>Rekap kehadiran (dihitung dari absensi pertemuan)</span></h2>

        <div class="dua-kolom">
            <div>
                <table class="tabel">
                    <tbody>
                        <tr>
                            <td class="arab" style="font-size: 15px;">{{ $L('hadir') }} <span style="font-family: Inter; font-size: 10px; color: #64748b;">(Hadir)</span></td>
                            <td class="tengah angka" style="width: 70px;">{{ $rekap['hadir'] }}</td>
                        </tr>
                        <tr>
                            <td class="arab" style="font-size: 15px;">{{ $L('sakit') }} <span style="font-family: Inter; font-size: 10px; color: #64748b;">(Sakit)</span></td>
                            <td class="tengah angka">{{ $rekap['sakit'] }}</td>
                        </tr>
                        <tr>
                            <td class="arab" style="font-size: 15px;">{{ $L('izin') }} <span style="font-family: Inter; font-size: 10px; color: #64748b;">(Izin)</span></td>
                            <td class="tengah angka">{{ $rekap['izin'] }}</td>
                        </tr>
                        <tr>
                            <td class="arab" style="font-size: 15px;">{{ $L('alpa') }} <span style="font-family: Inter; font-size: 10px; color: #64748b;">(Alpa)</span></td>
                            <td class="tengah angka">{{ $rekap['alpa'] }}</td>
                        </tr>
                        <tr class="baris-jumlah">
                            <td class="arab" style="font-size: 15px;">{{ $L('total_pertemuan') }} <span style="font-family: Inter; font-size: 10px; color: #64748b;">(Jumlah pertemuan)</span></td>
                            <td class="tengah angka">{{ $rekap['total'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <div class="catatan-box">
                    <p class="judul-kecil">{{ $L('catatan_pembina') }}</p>
                    <p class="judul-kecil-id">Catatan pembina / wali kelas diniyah</p>
                    <p style="font-size: 12px; font-weight: 700; color: #334155; margin-top: 6px;">
                        {{ $kamar?->musyrif ? 'Pembina kamar ' . $kamar->nama_kamar . ': ' . $kamar->musyrif : 'Pembina kamar: ' . ($kamar?->nama_kamar ?? 'belum tercatat') }}
                    </p>
                    <p style="font-size: 12px; font-weight: 600; color: #475569; margin-top: 6px;">
                        {{ $rekap['alpa'] > 0 ? 'Perlu perhatian pada kedisiplinan kehadiran kajian (' . $rekap['alpa'] . ' kali tanpa keterangan).' : 'Kehadiran kajian baik dan tertib.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- C. TANDA TANGAN --}}
    <div class="ttd">
        <div>
            <p class="peran">{{ $L('kepala_sekolah') }}</p>
            <p class="peran-id">Kepala Sekolah</p>
            <div class="ruang"></div>
            <p class="nama">{{ $kepala->nama ?? $namaSekolah }}</p>
            @if(! empty($kepala->nipa))
                <p style="font-size: 10.5px; font-weight: 700; color: #64748b;">NIPA: {{ $kepala->nipa }}</p>
            @endif
        </div>
        <div>
            <p class="peran">{{ $L('wali') }}</p>
            <p class="peran-id">Wali santri</p>
            <div class="ruang"></div>
            <p class="nama">...............................</p>
        </div>
        <div>
            <p class="peran">{{ $L('kepala_diniyah') }}</p>
            <p class="peran-id">Kepala Kulliyyat Diiniyyah</p>
            <div class="ruang"></div>
            <p class="nama">{{ $kepalaDiniyah->nama ?? '...............................' }}</p>
            @if(! empty($kepalaDiniyah->nipa))
                <p style="font-size: 10.5px; font-weight: 700; color: #64748b;">NIPA: {{ $kepalaDiniyah->nipa }}</p>
            @endif
        </div>
    </div>

    <p class="kaki">
        Dihasilkan otomatis dari SIAKAD {{ $namaSekolah }} · {{ now()->locale('id')->translatedFormat('d F Y') }} · nilai akhir per mata pelajaran, rekap kehadiran dari absensi pertemuan.
    </p>
</div>
</body>
</html>
