@php
    /**
     * RAPOR STUDENT ROOT — halaman cetak (satu santri = satu halaman A4).
     *
     * Tata letak (revisi Fahri 17 Sep 2026):
     * - Predikat karakter DIHAPUS (ambangnya sulit dibakukan) → poin positif & negatif ditampilkan apa adanya.
     * - Kartu Tren Bulanan diletakkan di BAWAH kartu poin, lebar penuh satu halaman.
     * - Tabel project: kolom Tahapan dipecah menjadi kolom per tahap (Observasi, Perencanaan, dst),
     *   nama project dibuat lebih lebar, nilai akhir di kolom paling kanan.
     * - Kartu rekap di bagian C dipenuhi kiri-kanan dan ukurannya diperkecil.
     */
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $dicetak = now()->format('d/m/Y H:i');
    $predikatNilai = fn ($n) => $n === null ? null : \App\Models\PenilaianPengaturan::predikat($n);

    $kesimpulan = function (int $total, int $pos, int $neg) {
        if ($total > 0 && $neg === 0) {
            return 'Seluruh catatan pada periode ini positif (' . $pos . ' kejadian, total +' . $total . ' poin) tanpa pelanggaran. Pertahankan dan jadikan teladan.';
        }
        if ($total > 0) {
            return 'Catatan positif ' . $pos . ' kejadian (+' . $total . ' poin bersih) dengan ' . $neg . ' catatan negatif. Dorong lebih aktif pada kegiatan sekolah dan asrama.';
        }
        if ($total === 0) {
            return 'Belum ada catatan poin yang menonjol pada periode ini (' . $pos . ' positif, ' . $neg . ' negatif). Dorong keaktifan pada kegiatan sekolah dan asrama.';
        }

        return 'Terdapat ' . $neg . ' catatan negatif (' . $total . ' poin) pada periode ini. Pendampingan mentor dan wali kelas disarankan.';
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapor Student Root</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px; background: #f1f5f9; font-size: 13px; }
        .bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; max-width: 210mm; margin: 0 auto 14px; }
        .bar a, .bar button { height: 38px; padding: 0 15px; border-radius: 9px; border: 1px solid #cbd5e1; background: #fff;
            color: #334155; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .bar .utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        .hal { background: #fff; max-width: 210mm; margin: 0 auto 18px; padding: 14mm 12mm; border-radius: 6px; box-shadow: 0 1px 4px rgba(15,23,42,.12); }
        .judul { text-align: center; margin: 0 0 14px; padding: 11px 0 10px; border-top: 3px solid #1e3a8a; border-bottom: 1px solid #cbd5e1; }
        .judul h2 { margin: 0; font-size: 17px; letter-spacing: 1.2px; text-transform: uppercase; }
        .judul p { margin: 4px 0 0; font-size: 12px; color: #475569; }
        .identitas { display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px; border: 1px solid #e2e8f0; border-radius: 9px; padding: 11px 14px; font-size: 12.5px; }
        .identitas .b { display: flex; gap: 7px; }
        .identitas .k { color: #64748b; min-width: 104px; }
        .identitas .v { font-weight: 700; }
        .judul-bagian { font-size: 13px; font-weight: 800; color: #1e3a8a; margin: 16px 0 8px; }

        .baris-kartu { display: grid; gap: 10px; }
        .baris-kartu.tiga { grid-template-columns: repeat(3, 1fr); }
        .baris-kartu.dua { grid-template-columns: repeat(2, 1fr); }

        .kartu { border: 1px solid #e2e8f0; border-radius: 10px; padding: 11px 14px; }
        .kartu .label { font-size: 11px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: #475569; }
        .kartu .angka { font-size: 25px; font-weight: 800; line-height: 1.15; margin-top: 2px; }
        .kartu .angka small { font-size: 12px; font-weight: 600; color: #94a3b8; }
        .kartu .keterangan { font-size: 11.5px; color: #475569; margin-top: 3px; }
        .kartu.baik { border-color: #86efac; background: #f0fdf4; }
        .kartu.kurang { border-color: #fecaca; background: #fef2f2; }
        .kartu.lebar { grid-column: 1 / -1; }

        /* Kartu rekap bawah: isi melebar kiri-kanan, ukuran lebih kecil */
        .rekap { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .rekap .kiri { display: flex; flex-direction: column; gap: 2px; }
        .rekap .kanan { font-size: 22px; font-weight: 800; white-space: nowrap; }
        .rekap .kanan small { font-size: 11px; color: #94a3b8; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-size: 10.5px; text-transform: uppercase; letter-spacing: .3px; color: #475569; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        td.angka-sel { text-align: center; font-weight: 600; }
        .sub { font-size: 10.5px; color: #64748b; margin-top: 2px; }
        .angka-nilai { font-size: 14px; }
        .belum { color: #94a3b8; }

        .tren { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; font-size: 11.5px; }
        .tren .bl { width: 74px; color: #475569; font-weight: 600; }
        .tren .bar { flex: 1; height: 10px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
        .tren .bar .p { height: 10px; background: #10b981; }
        .tren .bar .m { height: 10px; background: #ef4444; }
        .tren .n { width: 22px; text-align: right; color: #334155; font-weight: 700; }
        .tren .t { width: 42px; text-align: right; font-weight: 800; }
        .hijau { color: #047857; } .merah { color: #b91c1c; }
        .keterangan-bagian { font-size: 11px; color: #64748b; margin-top: 6px; }

        .catatan { border: 1px solid #e2e8f0; border-radius: 9px; padding: 11px 14px; margin-top: 13px; font-size: 12.5px; line-height: 1.6; }
        .catatan .t { font-size: 11px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: #475569; margin-bottom: 4px; }
        .garis { border-bottom: 1px dashed #cbd5e1; height: 18px; }
        .ttd { display: flex; justify-content: space-between; gap: 16px; margin-top: 18px; font-size: 12.5px; text-align: center; }
        .ttd .k2 { flex: 1; }
        .ttd .ruang { height: 54px; }
        .ttd .nama { font-weight: 700; border-top: 1px solid #94a3b8; padding-top: 4px; }
        .ttd .nipa { font-size: 11px; color: #475569; margin-top: 3px; }
        .kaki { margin-top: 13px; font-size: 11px; color: #64748b; display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 7px; }

        @media print {
            body { padding: 0; background: #fff; }
            .tanpa-cetak { display: none !important; }
            .hal { box-shadow: none; border-radius: 0; margin: 0; max-width: none; padding: 0; }
            .hal + .hal { page-break-before: always; }
            @page { size: A4 portrait; margin: 9mm 10mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .judul { padding: 5px 0 4px; margin-bottom: 7px; }
            .judul h2 { font-size: 13.5px; }
            .judul p { font-size: 10px; }
            .identitas { font-size: 10px; padding: 5px 9px; gap: 1px 14px; grid-template-columns: 1fr 1fr 1fr; }
            .identitas .k { min-width: 78px; }
            .judul-bagian { font-size: 10.5px; margin: 6px 0 3px; }
            .baris-kartu { gap: 7px; }
            .kartu { padding: 6px 10px; }
            .kartu .label { font-size: 9px; }
            .kartu .angka { font-size: 17px; }
            .kartu .angka small { font-size: 9.5px; }
            .kartu .keterangan { font-size: 9.5px; }
            .rekap .kanan { font-size: 16px; }
            table { font-size: 9.5px; line-height: 1.3; }
            th, td { padding: 2px 5px; }
            th { font-size: 8px; line-height: 1.15; }
            .sub { font-size: 8px; margin-top: 0; }
            .angka-nilai { font-size: 11px; }
            .tren { margin-bottom: 2px; font-size: 9.5px; gap: 6px; }
            .tren .bl { width: 54px; }
            .tren .bar { height: 7px; }
            .tren .bar .p, .tren .bar .m { height: 7px; }
            .keterangan-bagian { font-size: 8.5px; margin-top: 3px; }
            .catatan { padding: 5px 9px; margin-top: 5px; font-size: 9.5px; line-height: 1.45; }
            .catatan .t { font-size: 9px; margin-bottom: 2px; }
            .garis { height: 7px; }
            .ttd { margin-top: 6px; font-size: 10px; }
            .ttd .ruang { height: 22px; }
            .ttd .nipa { font-size: 9px; }
            .kaki { margin-top: 6px; font-size: 9px; padding-top: 5px; }
        }

        @media (max-width: 760px) {
            .hal { padding: 16px; }
            .identitas, .baris-kartu.tiga, .baris-kartu.dua { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <button type="button" class="utama" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="{{ route('sr.rapot', ['semester' => request('semester', 'semua'), 'dari' => $dari, 'sampai' => $sampai]) }}">⬅️ Kembali</a>
        @if($grupCetak)
            <a class="utama" href="{{ route('sr.rapot.cetak', ['grup' => $grupCetak->id, 'semester' => request('semester', 'semua'), 'dari' => $dari, 'sampai' => $sampai]) }}">🖨️ Cetak semua ({{ $jumlahSantri }} santri) grup {{ $grupCetak->nama_grup }}</a>
        @endif
        <span style="font-size: 12.5px; color: #64748b;">{{ $jumlahSantri }} santri · satu halaman per santri</span>
    </div>

    @forelse($daftar as $d)
        @php
            $s = $d['siswa'];
            $poinPos = collect($d['positif'])->sum('poin');
            $poinNeg = collect($d['negatif'])->sum('poin');
            $tahapKolom = $tahapKolom ?? [];
            $predProject = $predikatNilai($d['project_rata']);
        @endphp

        <div class="hal">
            <div class="judul">
                <h2>Rapor Student Root</h2>
                <p>
                    Periode {{ $dari && $sampai ? \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') . ' – ' . \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') : 'seluruh data tercatat' }}
                </p>
            </div>

            <div class="identitas">
                <div class="b"><span class="k">Nama Santri</span><span class="v">{{ $s->nama_lengkap }}</span></div>
                <div class="b"><span class="k">Kelas</span><span class="v">{{ $s->kelas ?: '-' }}</span></div>
                <div class="b"><span class="k">Grup Student Root</span><span class="v">{{ $d['grup'] ?: 'Belum masuk grup' }}</span></div>
                <div class="b"><span class="k">Mentor</span><span class="v">{{ $d['mentor'] ?: '-' }}</span></div>
                <div class="b"><span class="k">NISN</span><span class="v">{{ $s->nisn ?: '-' }}</span></div>
                <div class="b"><span class="k">Kamar</span><span class="v">{{ $d['kamar'] ?: '-' }}</span></div>
            </div>

            {{-- A. Poin Karakter: poin ditampilkan apa adanya (tanpa predikat) --}}
            <div class="judul-bagian">A. Poin Karakter</div>
            <div class="baris-kartu tiga">
                <div class="kartu">
                    <div class="label">Poin Positif</div>
                    <div class="angka hijau">+{{ $poinPos }}</div>
                    <div class="keterangan">{{ $d['pos'] }} kejadian tercatat</div>
                </div>
                <div class="kartu {{ $poinNeg < 0 ? 'kurang' : '' }}">
                    <div class="label">Poin Negatif</div>
                    <div class="angka {{ $poinNeg < 0 ? 'merah' : '' }}">{{ $poinNeg }}</div>
                    <div class="keterangan">{{ $d['neg'] }} kejadian tercatat</div>
                </div>
                <div class="kartu">
                    <div class="label">Total Poin</div>
                    <div class="angka {{ $d['total'] < 0 ? 'merah' : 'hijau' }}">{{ $d['total'] > 0 ? '+' : '' }}{{ $d['total'] }}</div>
                    <div class="keterangan">@if($d['peringkat']) peringkat {{ $d['peringkat'] }} dari {{ $semuaSantri }} santri @endif</div>
                </div>
            </div>

            <table style="margin-top: 11px;">
                <thead>
                    <tr><th style="width: 30px;">No</th><th>Perilaku Positif Terbanyak</th><th style="width: 56px;">Jml</th><th style="width: 66px;">Poin</th></tr>
                </thead>
                <tbody>
                    @forelse($d['positif'] as $i => $p)
                        <tr>
                            <td class="angka-sel">{{ $i + 1 }}</td>
                            <td>{{ $p['nama'] }}</td>
                            <td class="angka-sel">{{ $p['jumlah'] }}x</td>
                            <td class="angka-sel">+{{ $p['poin'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: #b45309;">Belum ada catatan perilaku positif pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <table style="margin-top: 11px;">
                <thead>
                    <tr><th style="width: 30px;">No</th><th>Pelanggaran / Catatan Negatif</th><th style="width: 56px;">Jml</th><th style="width: 66px;">Poin</th></tr>
                </thead>
                <tbody>
                    @forelse($d['negatif'] as $i => $p)
                        <tr>
                            <td class="angka-sel">{{ $i + 1 }}</td>
                            <td>{{ $p['nama'] }}</td>
                            <td class="angka-sel">{{ $p['jumlah'] }}x</td>
                            <td class="angka-sel">{{ $p['poin'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align: center; color: #047857;">Tidak ada catatan pelanggaran pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- B. Project: nama project lebar, tiap tahap punya kolomnya sendiri, nilai di kolom terakhir --}}
            <div class="judul-bagian">B. Project Student Root</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 26px;">No</th>
                        <th style="width: 30%;">Nama Project</th>
                        @foreach($tahapKolom as $t)
                            <th style="width: 68px; text-align: center;">{{ $t['nama'] }}</th>
                        @endforeach
                        <th style="width: 62px; text-align: center;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($d['project'] as $i => $p)
                        <tr>
                            <td class="angka-sel">{{ $i + 1 }}</td>
                            <td>
                                <b>{{ $p['nama'] }}</b>
                                <div class="sub">Mentor {{ $p['mentor'] ?: '-' }} · {{ $p['status'] }}</div>
                            </td>
                            @foreach($p['tahap'] as $t)
                                <td class="angka-sel">
                                    @if($t['skor'] !== null)
                                        {{ $t['skor'] }}
                                    @else
                                        <span class="belum">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="angka-sel">
                                <b class="angka-nilai">{{ $p['nilai'] !== null ? $p['nilai'] : '—' }}</b>
                                <div class="sub">{{ $predikatNilai($p['nilai']) ?: '' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($tahapKolom) }}" style="text-align: center; color: #64748b;">
                                Belum ada project pada periode ini. Kolom tahapan terisi otomatis begitu mentor mengisi nilainya.
                            </td>
                        </tr>
                    @endforelse
                    @if($d['project_rata'] !== null && count($d['project']) > 1)
                        <tr>
                            <td colspan="{{ 2 + count($tahapKolom) }}" style="text-align: right; font-weight: 700;">Rata-rata nilai project</td>
                            <td class="angka-sel"><b class="angka-nilai">{{ $d['project_rata'] }}</b></td>
                        </tr>
                    @endif
                </tbody>
            </table>
            @if($d['project'])
                <div class="keterangan-bagian">Tahap yang belum dinilai ditandai — ; nilai project = rata-rata terbobot dari tahapan yang sudah dinilai.</div>
            @endif

            {{-- C. Nilai Student Root: kartu memenuhi lebar, ukuran lebih kecil --}}
            <div class="judul-bagian">C. Nilai Student Root</div>
            <div class="baris-kartu dua">
                <div class="kartu">
                    <div class="rekap">
                        <div class="kiri">
                            <div class="label">Poin Karakter</div>
                            <div class="keterangan">{{ $d['pos'] }} positif (+{{ $poinPos }}) · {{ $d['neg'] }} negatif ({{ $poinNeg }})</div>
                        </div>
                        <div class="kanan {{ $d['total'] < 0 ? 'merah' : 'hijau' }}">{{ $d['total'] > 0 ? '+' : '' }}{{ $d['total'] }}<small> poin</small></div>
                    </div>
                </div>
                <div class="kartu">
                    <div class="rekap">
                        <div class="kiri">
                            <div class="label">Nilai Project</div>
                            <div class="keterangan">
                                @if($d['project_rata'] !== null)
                                    {{ $d['project_nilai'] }} project dinilai · predikat {{ $predProject }}
                                @else
                                    Belum ada project dinilai pada periode ini
                                @endif
                            </div>
                        </div>
                        <div class="kanan">
                            @if($d['project_rata'] !== null)
                                {{ $d['project_rata'] }}<small> / 100</small>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="catatan">
                <div class="t">Kesimpulan</div>
                <div>{{ $kesimpulan($d['total'], $d['pos'], $d['neg']) }}</div>
                <div class="t" style="margin-top: 9px;">Catatan Mentor</div>
                <div class="garis"></div>
                <div class="garis"></div>
            </div>

            <div class="ttd">
                <div class="k2">
                    <div>Kepala SMA IT Arafah</div>
                    <div class="ruang"></div>
                    <div class="nama">{{ optional($kepala)->nama ?: '...................' }}</div>
                    <div class="nipa">NIPA: {{ optional($kepala)->nipa ?: 'YMA. ...........' }}</div>
                </div>
                <div class="k2">
                    <div>Orang Tua / Wali</div>
                    <div class="ruang"></div>
                    <div class="nama">...................</div>
                    <div class="nipa">&nbsp;</div>
                </div>
                <div class="k2">
                    <div>Mentor Student Root</div>
                    <div class="ruang"></div>
                    <div class="nama">{{ $d['mentor'] ?: '...................' }}</div>
                    <div class="nipa">NIPA: {{ $d['mentor_nipa'] ?: 'YMA. ...........' }}</div>
                </div>
            </div>

            <div class="kaki">
                <span>Dicetak {{ $dicetak }} · Sistem Akademik</span>
                <span>{{ $namaSekolah }}</span>
            </div>
        </div>
    @empty
        <div class="hal">
            <p style="color: #64748b;">Tidak ada santri yang bisa dicetak pada pilihan ini.</p>
        </div>
    @endforelse

</body>
</html>
