@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Cerdas & Beradab';
    $tahunAjaran = $periode->tahun_ajaran ?? ($periode->nama ?? '-');
    $dicetak = now()->format('d/m/Y H:i');

    // Fungsi kecil: kesimpulan otomatis dari nilai akhir.
    $kesimpulan = function ($label, $data) {
        if (($data['rata'] ?? null) === null) {
            return $label . ': belum ada penilaian pada periode ini.';
        }

        $predikat = $data['predikat'] ?? '-';

        $teks = match ($predikat) {
            'A' => 'sangat baik — pertahankan dan jadikan teladan',
            'B' => 'baik — terus ditingkatkan',
            'C' => 'cukup — perlu perhatian dan pembinaan',
            default => 'perlu pembinaan serius',
        };

        return $label . ' ' . $teks . ' (nilai ' . $data['rata'] . ' · predikat ' . $predikat . ').';
    };

    $kelasKartu = function ($predikat) {
        return match ($predikat) {
            'A', 'B' => 'baik',
            'C' => 'cukup',
            'D' => 'kurang',
            default => '',
        };
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapot Adab &amp; Keasramaan</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 16px 18px; background: #f1f5f9; }
        .hal { background: #fff; max-width: 210mm; margin: 0 auto 16px; padding: 14mm 12mm; border-radius: 6px; box-shadow: 0 1px 4px rgba(15,23,42,.12); }

        .bar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .bar .aksi { display: flex; gap: 8px; flex-wrap: wrap; }
        .bar form { display: flex; align-items: center; gap: 6px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 36px; padding: 0 13px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
        select { height: 36px; border: 1px solid #cbd5e1; border-radius: 9px; padding: 0 8px; font-size: 12.5px; background: #fff; }

        /* ===== Kop ===== */
        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 9px; }
        .kop .inisial { width: 50px; height: 50px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; letter-spacing: .2px; }
        .kop p { margin: 2px 0 0; font-size: 11px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }

        .judul { text-align: center; margin: 0 0 12px; padding: 10px 0 9px; border-top: 2.5px solid #1e3a8a; border-bottom: 1px solid #cbd5e1; }
        .judul h2 { margin: 0; font-size: 15.5px; letter-spacing: 1.4px; text-transform: uppercase; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }

        /* Kotak rata-rata keseluruhan (paling bawah) */
        .total { display: flex; align-items: center; justify-content: space-between; gap: 12px;
                 border: 1.5px solid #1e3a8a; border-radius: 10px; padding: 9px 14px; margin-top: 12px; background: #f8fafc; }
        .total .t { font-size: 12px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: #1e3a8a; }
        .total .sub { font-size: 10.5px; color: #64748b; margin-top: 2px; line-height: 1.45; }
        .total .kanan { display: flex; align-items: center; gap: 10px; }
        .total .angka-total { font-size: 26px; font-weight: 800; line-height: 1; color: #0f172a; }
        .total .angka-total small { font-size: 11.5px; font-weight: 600; color: #94a3b8; }
        .total .predikat-besar { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px;
                                 border-radius: 9px; border: 1.5px solid #1e3a8a; background: #fff; font-weight: 800; font-size: 17px; color: #1e3a8a; }

        /* ===== Identitas ===== */
        .identitas { display: grid; grid-template-columns: 1fr 1fr; gap: 3px 18px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; font-size: 11.5px; }
        .identitas .baris { display: flex; gap: 6px; }
        .identitas .k { color: #64748b; min-width: 88px; }
        .identitas .v { font-weight: 700; color: #0f172a; }

        /* ===== Kartu nilai ===== */
        .nilai { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 12px 0; }
        .kartu { border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; }
        .kartu .label { font-size: 11px; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; color: #475569; }
        .kartu .angka { font-size: 26px; font-weight: 800; line-height: 1.1; margin-top: 2px; }
        .kartu .angka small { font-size: 11.5px; font-weight: 600; color: #94a3b8; }
        .kartu .meta { font-size: 10.5px; color: #64748b; margin-top: 4px; line-height: 1.5; }
        .kartu.baik { border-color: #86efac; background: #f0fdf4; }
        .kartu.cukup { border-color: #fde68a; background: #fffbeb; }
        .kartu.kurang { border-color: #fecaca; background: #fef2f2; }
        .predikat { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 7px;
                    border: 1px solid #cbd5e1; background: #fff; font-weight: 800; font-size: 14px; vertical-align: middle; }

        /* ===== Tabel aspek ===== */
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 4px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: middle; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; color: #475569; }
        td.c { text-align: center; }
        .judul-tabel { font-size: 11.5px; font-weight: 800; margin: 10px 0 4px; color: #1e3a8a; }
        .bar-skor { height: 4px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-top: 3px; }
        .bar-skor span { display: block; height: 4px; border-radius: 999px; background: #1e3a8a; }
        .kosong { color: #94a3b8; font-style: italic; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .skala { font-size: 10px; color: #64748b; margin: 4px 0 0; }

        .catatan { border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; margin-top: 10px; font-size: 11px; line-height: 1.6; }
        .catatan .t { font-size: 10.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: #475569; margin-bottom: 3px; }
        .garis { border-bottom: 1px dashed #cbd5e1; height: 15px; }

        .ttd { display: flex; justify-content: space-between; gap: 14px; margin-top: 16px; font-size: 11px; text-align: center; }
        .ttd .kolom { flex: 1; }
        .ttd .ruang { height: 52px; }
        .ttd .nama { font-weight: 700; border-top: 1px solid #94a3b8; padding-top: 3px; }
        .ttd .nipa { font-size: 10px; color: #475569; margin-top: 2px; letter-spacing: .3px; }

        .kaki { margin-top: 12px; font-size: 10px; color: #64748b; display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 6px; }

        @media print {
            body { padding: 0; background: #fff; }
            .tanpa-cetak { display: none !important; }
            .hal { box-shadow: none; border-radius: 0; margin: 0; max-width: none; padding: 0; }
            .hal + .hal { page-break-before: always; }
            @page { size: A4 portrait; margin: 12mm 11mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* Ukuran cetak dipadatkan supaya SATU santri pas di SATU halaman A4 */
            .kop { padding-bottom: 6px; }
            .hal { padding: 0; }
            .kop .inisial { width: 42px; height: 42px; font-size: 18px; border-radius: 8px; }
            .kop h1 { font-size: 15px; }
            .kop p { font-size: 10px; }
            .judul { margin: 0 0 8px; padding: 8px 0 7px; }
            .judul h2 { font-size: 13px; }
            .judul p { font-size: 10.5px; }
            .identitas { font-size: 10.5px; padding: 5px 10px; gap: 1px 16px; }
            .nilai { margin: 6px 0; gap: 8px; }
            .kartu { padding: 6px 10px; }
            .kartu .label { font-size: 10px; }
            .kartu .angka { font-size: 19px; }
            .kartu .meta { font-size: 9.5px; margin-top: 2px; line-height: 1.35; }
            .predikat { width: 24px; height: 24px; font-size: 12.5px; }
            .judul-tabel { font-size: 10.5px; margin: 6px 0 3px; }
            table { font-size: 9.5px; margin-bottom: 3px; }
            th, td { padding: 1.5px 5px; }
            th { font-size: 9px; }
            .bar-skor { height: 3px; margin-top: 2px; }
            .bar-skor span { height: 3px; }
            .skala { font-size: 8.5px; margin-top: 2px; }
            .total { padding: 6px 11px; margin-top: 6px; border-width: 1.5px; }
            .total .t { font-size: 11px; }
            .total .sub { font-size: 9.5px; }
            .total .angka-total { font-size: 20px; }
            .total .predikat-besar { width: 28px; height: 28px; font-size: 15px; }
            .catatan { padding: 6px 10px; margin-top: 6px; font-size: 9.8px; line-height: 1.45; }
            .catatan .t { font-size: 9.5px; }
            .garis { height: 11px; }
            .ttd { margin-top: 8px; font-size: 10px; }
            .ttd .ruang { height: 30px; }
            .ttd .nipa { font-size: 9px; margin-top: 1px; }
            .kaki { margin-top: 6px; font-size: 9px; padding-top: 4px; }
        }

        @media (max-width: 700px) {
            .hal { padding: 14px; }
            .identitas, .nilai { grid-template-columns: 1fr; }
            .kop h1 { font-size: 15px; }
        }
    </style>
</head>
<body>

    <div class="bar tanpa-cetak">
        <div class="aksi">
            <a href="{{ route('penilaian.rapot', ['periode' => $periodeId, 'kamar' => $kamar->id ?? 0]) }}" class="btn">⬅️ Kembali</a>
            @if($kamar)
                <a href="{{ route('penilaian.rapot.cetak', ['periode' => $periodeId, 'kamar' => $kamar->id]) }}" class="btn btn-utama">🖨️ Cetak {{ $daftar->count() }} santri kamar {{ $kamar->nama_kamar }}</a>
            @endif
        </div>
        <form method="GET" action="{{ route('penilaian.rapot.cetak') }}">
            @if($kamar)
                <input type="hidden" name="kamar" value="{{ $kamar->id }}">
            @endif
            <select name="periode">
                @foreach($periodeList as $p)
                    <option value="{{ $p->id }}" @selected($periodeId === $p->id)>{{ $p->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Tampilkan</button>
            <button type="button" class="btn btn-utama" onclick="window.print()">🖨️ Cetak</button>
        </form>
    </div>

    @forelse($daftar as $d)
        @php
            $s = $d['siswa'];
            $adab = $d['adab'];
            $asrama = $d['keasramaan'];
            $adaAdab = ($adab['rata'] ?? null) !== null;
            $adaAsrama = ($asrama['rata'] ?? null) !== null;

            // Rata-rata nilai KESELURUHAN rapor = rata-rata semua aspek Adab + Keasramaan yang sudah dinilai.
            $semuaNilai = collect([$adab['aspek'] ?? [], $asrama['aspek'] ?? []])
                ->flatten(1)
                ->pluck('nilai')
                ->filter(fn ($n) => $n !== null)
                ->values();
            $jumlahAspekTerisi = $semuaNilai->count();
            $nilaiKeseluruhan = $jumlahAspekTerisi > 0 ? round($semuaNilai->avg(), 2) : null;
            $predikatKeseluruhan = \App\Models\PenilaianPengaturan::predikat($nilaiKeseluruhan);
        @endphp

        <div class="hal">
            <div class="judul">
                <h2>Rapot Penilaian Adab &amp; Keasramaan</h2>
                <p>
                    Periode {{ $periode->nama ?? '-' }}
                    @if($tahunAjaran && $tahunAjaran !== ($periode->nama ?? null))
                        · Tahun Ajaran {{ $tahunAjaran }}
                    @endif
                </p>
            </div>

            <div class="identitas">
                <div class="baris"><span class="k">Nama Santri</span><span class="v">{{ $s->nama_lengkap }}</span></div>
                <div class="baris"><span class="k">Kamar</span><span class="v">{{ $d['kamar'] ?: '-' }}{{ $d['kategori'] ? ' (' . ucfirst($d['kategori']) . ')' : '' }}</span></div>
                <div class="baris"><span class="k">NISN</span><span class="v">{{ $s->nisn ?: '-' }}</span></div>
                <div class="baris"><span class="k">Musyrif</span><span class="v">{{ $d['musyrif'] ?: '-' }}</span></div>
                <div class="baris"><span class="k">Kelas</span><span class="v">{{ $s->kelas ?: '-' }}</span></div>
                <div class="baris"><span class="k">Status</span><span class="v">{{ $s->status }}</span></div>
            </div>

            @include('penilaian.partials._tabel-rapot', ['judul' => 'A. Rincian Penilaian Adab', 'data' => $adab])
            @include('penilaian.partials._tabel-rapot', ['judul' => 'B. Rincian Penilaian Keasramaan', 'data' => $asrama])

            <div class="judul-tabel">Rekap Nilai Adab &amp; Keasramaan</div>
            <div class="nilai">
                <div class="kartu {{ $kelasKartu($adab['predikat'] ?? null) }}">
                    <div class="label">A. Nilai Adab</div>
                    @if($adaAdab)
                        <div class="angka">{{ number_format($adab['rata'], 2) }} <small>/ 100</small></div>
                        <div class="meta">
                            Predikat <span class="predikat">{{ $adab['predikat'] }}</span>
                            · dinilai {{ count($adab['penilai']) }} dari {{ (int) ($d['wajib'] ?? 0) }} musyrif divisi
                            @if(! empty($adab['penilai']))
                                <br>Pengisi: {{ implode(', ', $adab['penilai']) }}
                            @endif
                            @if((int) ($d['wajib'] ?? 0) > count($adab['penilai']))
                                <br><b>⚠️ baru {{ count($adab['penilai']) }} dari {{ (int) ($d['wajib'] ?? 0) }} penilai wajib divisi — nilai belum lengkap</b>
                            @endif
                            @if($adab['ada_draft'] ?? false)
                                <br>⚠️ masih ada lembar yang belum difinalisasi
                            @endif
                        </div>
                    @else
                        <div class="angka"><span class="kosong">belum dinilai</span></div>
                        <div class="meta">Belum ada penilaian Adab pada periode ini.</div>
                    @endif
                </div>

                <div class="kartu {{ $kelasKartu($asrama['predikat'] ?? null) }}">
                    <div class="label">B. Nilai Keasramaan</div>
                    @if($adaAsrama)
                        <div class="angka">{{ number_format($asrama['rata'], 2) }} <small>/ 100</small></div>
                        <div class="meta">
                            Predikat <span class="predikat">{{ $asrama['predikat'] }}</span>
                            · dinilai {{ count($asrama['penilai']) }} dari {{ (int) ($d['wajib'] ?? 0) }} musyrif divisi
                            @if(! empty($asrama['penilai']))
                                <br>Pengisi: {{ implode(', ', $asrama['penilai']) }}
                            @endif
                            @if((int) ($d['wajib'] ?? 0) > count($asrama['penilai']))
                                <br><b>⚠️ baru {{ count($asrama['penilai']) }} dari {{ (int) ($d['wajib'] ?? 0) }} penilai wajib divisi — nilai belum lengkap</b>
                            @endif
                            @if($asrama['ada_draft'] ?? false)
                                <br>⚠️ masih ada lembar yang belum difinalisasi
                            @endif
                        </div>
                    @else
                        <div class="angka"><span class="kosong">belum dinilai</span></div>
                        <div class="meta">Belum ada penilaian Keasramaan pada periode ini.</div>
                    @endif
                </div>
            </div>

            <div class="total">
                <div>
                    <div class="t">Rata-rata Nilai Keseluruhan</div>
                    <div class="sub">
                        Dihitung dari {{ $jumlahAspekTerisi }} aspek yang sudah dinilai (Adab + Keasramaan).
                        Predikat: A ≥ {{ (int) ($ambang['a'] ?? 90) }} · B ≥ {{ (int) ($ambang['b'] ?? 80) }} · C ≥ {{ (int) ($ambang['c'] ?? 70) }} · D &lt; {{ (int) ($ambang['c'] ?? 70) }}.
                    </div>
                </div>
                <div class="kanan">
                    @if($nilaiKeseluruhan !== null)
                        <div class="angka-total">{{ number_format($nilaiKeseluruhan, 2) }}<small> / 100</small></div>
                        <div class="predikat-besar">{{ $predikatKeseluruhan }}</div>
                    @else
                        <div class="angka-total"><span class="kosong">belum dinilai</span></div>
                    @endif
                </div>
            </div>

            <div class="catatan">
                <div class="t">Kesimpulan</div>
                <div>{{ $kesimpulan('Adab', $adab) }}</div>
                <div>{{ $kesimpulan('Keasramaan', $asrama) }}</div>

                <div class="t" style="margin-top:8px;">Catatan Musyrif / Pembina</div>
                <div class="garis"></div>
                <div class="garis"></div>
            </div>

            <div class="ttd">
                <div class="kolom">
                    <div>Kepala Kulliyyat Diiniyyah Al-Arafah</div>
                    <div class="ruang"></div>
                    <div class="nama">{{ optional($kepalaKulliyyah)->nama ?: '...................' }}</div>
                    <div class="nipa">NIPA: {{ optional($kepalaKulliyyah)->nipa ?: 'YMA. ...........' }}</div>
                </div>
                <div class="kolom">
                    <div>Orang Tua / Wali</div>
                    <div class="ruang"></div>
                    <div class="nama">...................</div>
                    <div class="nipa">&nbsp;</div>
                </div>
                <div class="kolom">
                    <div>Musyrif / Musyrifah</div>
                    <div class="ruang"></div>
                    <div class="nama">{{ $d['musyrif'] ?: '...................' }}</div>
                    <div class="nipa">NIPA: {{ $d['musyrif_nipa'] ?: 'YMA. ...........' }}</div>
                </div>
            </div>

            <div class="kaki">
                <span>Dicetak {{ $dicetak }} · Sistem Akademik</span>
                <span>{{ $namaSekolah }}</span>
            </div>
        </div>
    @empty
        <div class="hal">
            <p class="kosong">Tidak ada santri yang bisa dicetak pada pilihan ini.</p>
        </div>
    @endforelse

</body>
</html>
