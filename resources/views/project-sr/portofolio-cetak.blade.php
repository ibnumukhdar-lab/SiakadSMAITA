@php
    $namaSekolah = $pengaturan->nama_sekolah ?? 'SMA IT Arafah';
    $motto = $pengaturan->motto ?? 'Beriman, Berakhlak, Cerdas';
    $logo = $pengaturan->logo_path ?? null;
    $cetak = now()->format('d/m/Y H:i');
    $p = $portofolio;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio {{ $project->nama }} - {{ $namaSekolah }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Tahoma, sans-serif; color: #0f172a; margin: 0; padding: 18px 22px; background: #fff; }

        .bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border-radius: 9px;
               border: 1px solid #cbd5e1; background: #fff; color: #334155; font-size: 13px; font-weight: 600;
               text-decoration: none; cursor: pointer; }
        .btn-utama { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }

        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px solid #1e3a8a; padding-bottom: 10px; }
        .kop img { width: 52px; height: 52px; object-fit: contain; }
        .kop .inisial { width: 52px; height: 52px; border-radius: 10px; background: #1e3a8a; color: #fff;
                        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; }
        .kop h1 { margin: 0; font-size: 17px; }
        .kop p { margin: 2px 0 0; font-size: 11px; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }

        .judul { text-align: center; margin: 14px 0 10px; }
        .judul h2 { margin: 0; font-size: 15px; letter-spacing: 1.5px; text-transform: uppercase; }
        .judul p { margin: 3px 0 0; font-size: 11.5px; color: #475569; }

        .kartu { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; break-inside: avoid; }
        .kartu h3 { margin: 0 0 5px; font-size: 12.5px; text-transform: uppercase; letter-spacing: .6px; color: #1e3a8a; }

        .identitas { display: grid; grid-template-columns: 118px 1fr 118px 1fr; gap: 4px 10px; font-size: 11.5px; }
        .identitas .label { color: #64748b; }
        .identitas .nilai { font-weight: 600; }

        .bagian { font-size: 11.5px; line-height: 1.55; white-space: pre-wrap; }
        .bagian-kosong { color: #94a3b8; font-style: italic; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; vertical-align: top; }
        thead th { background: #f1f5f9; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; }
        td.tengah, th.tengah { text-align: center; }
        tfoot th { background: #f8fafc; }

        .foto-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .foto { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; break-inside: avoid; }
        .foto img { width: 100%; height: 190px; object-fit: cover; display: block; }
        .foto .ket { font-size: 10.5px; color: #475569; padding: 5px 7px; }

        .ttd { display: flex; justify-content: space-between; gap: 20px; margin-top: 22px; font-size: 11.5px; break-inside: avoid; }
        .ttd .kolom { width: 45%; text-align: center; }
        .ttd .garis { margin-top: 44px; border-top: 1px solid #475569; padding-top: 3px; font-weight: 600; }

        .kaki { margin-top: 12px; font-size: 10px; color: #94a3b8; text-align: center; }

        @media print {
            body { padding: 0 12px; }
            .bar { display: none; }
            .foto img { height: 165px; }
            .kartu, .foto, .ttd { break-inside: avoid; }
            @page { size: A4 portrait; margin: 12mm 12mm; }
        }

        /* Layar HP: identitas jadi 2 kolom supaya tidak berdesakan */
        @media screen and (max-width: 560px) {
            .identitas { grid-template-columns: 104px 1fr; }
            .foto img { height: 150px; }
        }
    </style>
</head>
<body>

<div class="bar">
    <a class="btn" href="{{ route('project-sr.portofolio', $project->id) }}">← Kembali ke penyusunan</a>
    <button class="btn btn-utama" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="kop">
    @if($logo)
        <img src="{{ url('berkas/' . $logo) }}" alt="{{ $namaSekolah }}">
    @else
        <div class="inisial">{{ mb_strtoupper(mb_substr($namaSekolah, 0, 1)) }}</div>
    @endif
    <div>
        <h1>{{ $namaSekolah }}</h1>
        <p>{{ $motto }}</p>
    </div>
</div>

<div class="judul">
    <h2>Portofolio Project Student Root</h2>
    <p>{{ $project->nama }}</p>
</div>

{{-- IDENTITAS --}}
<div class="kartu">
    <h3>Identitas project</h3>
    <div class="identitas">
        <div class="label">Grup binaan</div>
        <div class="nilai">{{ $project->grup->nama_grup ?? '-' }}</div>
        <div class="label">Mentor</div>
        <div class="nilai">{{ $project->mentor->name ?? '-' }}</div>

        <div class="label">Status</div>
        <div class="nilai">{{ \App\Models\SrProject::STATUS[$project->status] ?? $project->status }} · {{ $project->progresTahap() }} tahap selesai</div>
        <div class="label">Nilai project</div>
        <div class="nilai">
            @if($rataProject !== null)
                {{ $rataProject }}% ({{ \App\Models\PenilaianPengaturan::predikat($rataProject) }})
            @else
                -
            @endif
        </div>

        <div class="label">Periode</div>
        <div class="nilai">
            {{ $project->tanggal_mulai?->format('d/m/Y') ?? '—' }} s.d. {{ $project->tanggal_selesai?->format('d/m/Y') ?? '—' }}
        </div>
        <div class="label">Presentasi publik</div>
        <div class="nilai">
            {{ $p->tempat ?? '-' }}@if($p?->tanggal_presentasi) · {{ $p->tanggal_presentasi->format('d/m/Y') }}@endif
        </div>
    </div>
</div>

{{-- NARASI --}}
@php
    $bagian = [
        'Ringkasan project' => $p->ringkasan ?? null,
        'Latar belakang (observasi)' => $p->latar_belakang ?? null,
        'Tujuan & sasaran' => $p->tujuan ?? null,
        'Pelaksanaan' => $p->pelaksanaan ?? null,
        'Hasil / karya' => $p->hasil ?? null,
        'Refleksi & tindak lanjut' => $p->refleksi ?? null,
    ];
@endphp

@foreach($bagian as $judulBagian => $isi)
    <div class="kartu">
        <h3>{{ $judulBagian }}</h3>
        <div class="bagian @if(! $isi) bagian-kosong @endif">{{ $isi ?: 'Belum diisi.' }}</div>
    </div>
@endforeach

{{-- REKAP TAHAP --}}
<div class="kartu">
    <h3>Rekap tahapan project</h3>
    <table>
        <thead>
            <tr>
                <th style="width:28px" class="tengah">#</th>
                <th>Tahap</th>
                <th class="tengah" style="width:58px">Bobot</th>
                <th class="tengah" style="width:78px">Status</th>
                <th class="tengah" style="width:78px">Tanggal</th>
                <th class="tengah" style="width:60px">Rata nilai</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tahap as $t)
                @php $s = $status[$t->id] ?? null; @endphp
                <tr>
                    <td class="tengah">{{ $t->urutan }}</td>
                    <td>{{ $t->nama }}</td>
                    <td class="tengah">{{ $t->bobot }}%</td>
                    <td class="tengah">{{ \App\Models\SrProjectTahap::STATUS_TAHAP[$s->status ?? 'belum'] ?? '-' }}</td>
                    <td class="tengah">{{ $s?->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td class="tengah">{{ $tahapRata[$t->id] !== null ? $tahapRata[$t->id] . '%' : '-' }}</td>
                    <td>{{ $s->catatan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- NILAI SISWA --}}
<div class="kartu">
    <h3>Nilai anggota ({{ $anggota->count() }} siswa)</h3>
    <table>
        <thead>
            <tr>
                <th style="width:24px" class="tengah">#</th>
                <th>Nama siswa</th>
                @foreach($tahap as $t)
                    <th class="tengah" style="width:44px" title="{{ $t->nama }}">{{ $t->urutan }}</th>
                @endforeach
                <th class="tengah" style="width:58px">Nilai</th>
                <th class="tengah" style="width:64px">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($anggota as $i => $a)
                @php $n = $nilaiSiswa[$a->id] ?? null; @endphp
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $a->nama_lengkap }}</td>
                    @foreach($tahap as $t)
                        <td class="tengah">{{ $n['per_tahap'][$t->id] ?? '—' }}</td>
                    @endforeach
                    <td class="tengah">{{ $n['rata'] ?? '—' }}</td>
                    <td class="tengah">{{ $n['predikat'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Rata-rata tahap</th>
                @foreach($tahap as $t)
                    <th class="tengah">{{ $tahapRata[$t->id] !== null ? $tahapRata[$t->id] : '—' }}</th>
                @endforeach
                <th class="tengah">{{ $rataProject ?? '—' }}</th>
                <th class="tengah">{{ $rataProject !== null ? \App\Models\PenilaianPengaturan::predikat($rataProject) : '—' }}</th>
            </tr>
        </tfoot>
    </table>
    <div style="font-size:10.5px;color:#64748b;margin-top:5px">
        Kolom 1–{{ $tahap->count() }} = urutan tahap:
        @foreach($tahap as $t){{ $t->urutan }}. {{ $t->nama }} ({{ $t->bobot }}%)@if(!$loop->last), @endif @endforeach.
    </div>
</div>

{{-- DOKUMENTASI --}}
<div class="kartu">
    <h3>Dokumentasi kegiatan ({{ $dokumen->count() }} berkas)</h3>
    @if($dokumen->isEmpty())
        <div class="bagian bagian-kosong">Belum ada foto/dokumentasi yang diunggah.</div>
    @else
        <div class="foto-grid">
            @foreach($dokumen as $d)
                <div class="foto">
                    @if(Str::endsWith(strtolower((string) $d->nama_asli), '.pdf'))
                        <div style="padding:24px 10px;text-align:center;font-size:11.5px;color:#475569">📄 Lampiran PDF: {{ $d->nama_asli }}</div>
                    @else
                        <img src="{{ $d->url }}" alt="{{ $d->keterangan ?: $project->nama }}">
                    @endif
                    <div class="ket">{{ $d->keterangan ?: ($d->nama_asli ?: '-') }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="ttd">
    <div class="kolom">
        <div>Menyusun,<br>Mentor Student Root</div>
        <div class="garis">{{ $project->mentor->name ?? '.............................' }}</div>
    </div>
    <div class="kolom">
        <div>Mengetahui,<br>Kepala {{ $namaSekolah }}</div>
        <div class="garis">.............................</div>
    </div>
</div>

<div class="kaki">Dicetak dari SIAKAD {{ $namaSekolah }} · {{ $cetak }}</div>

</body>
</html>
