<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Hasil — {{ $evaluasi->judul }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#f5f7fb;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;color:#0f172a}
  .kepala{background:linear-gradient(135deg,#152a47,#1e3a8a);color:#fff;padding:20px 18px}
  .kepala h1{margin:0;font-size:17px}
  .kepala p{margin:6px 0 0;font-size:13px;color:rgba(255,255,255,.82)}
  .bungkus{max-width:680px;margin:0 auto;padding:0 16px 48px}
  .kartu{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px;margin-top:16px}
  .nilai{text-align:center;padding:26px 16px}
  .nilai .angka{font-size:56px;font-weight:800;line-height:1;color:#1e3a8a}
  .nilai .angka.tidak{color:#b91c1c}
  .lulus{display:inline-block;margin-top:12px;border-radius:999px;padding:7px 16px;font-size:13.5px;font-weight:800}
  .lulus.ya{background:#dcfce7;color:#166534}
  .lulus.belum{background:#fee2e2;color:#991b1b}
  .rinci{display:flex;justify-content:center;gap:22px;margin-top:18px;font-size:13px;color:#64748b}
  .rinci b{display:block;font-size:19px;color:#0f172a;font-weight:800}
  h2{font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:22px 0 10px}
  .soal{border:1px solid #e2e8f0;border-radius:14px;padding:14px;margin-bottom:10px;background:#fff}
  .soal.benar{border-left:4px solid #16a34a}
  .soal.salah{border-left:4px solid #dc2626}
  .soal .kepala-soal{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:#94a3b8;font-weight:700}
  .soal .tanya{font-size:16px;font-weight:700;color:#1e3a8a;margin:8px 0 4px}
  .soal .tanya.arab{direction:rtl;text-align:right;font-size:20px}
  .baris{font-size:14px;margin-top:6px;line-height:1.6}
  .baris .label{color:#64748b;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
  .benar-teks{color:#166534;font-weight:700}
  .salah-teks{color:#b91c1c;font-weight:700}
  .tombol{display:block;text-align:center;text-decoration:none;border-radius:12px;background:#fff;border:1px solid #cbd5e1;color:#1e3a8a;font-weight:800;font-size:15px;padding:14px;margin-top:18px}
  .kaki{font-size:12.5px;color:#64748b;text-align:center;margin-top:16px;line-height:1.7}
  table.mode{width:100%;border-collapse:collapse;font-size:13px;margin-top:8px}
  table.mode th,table.mode td{padding:8px 6px;border-bottom:1px solid #eef2f7;text-align:left}
  table.mode th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8}
  table.mode td.angka{font-weight:800;color:#1e3a8a;text-align:right;white-space:nowrap}
  table.mode tr.tuntas td{background:#f7fdf9}
  table.mode tr.belum-ikut td{color:#94a3b8}
  .catatan-poin{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;padding:11px 13px;font-size:12.5px;color:#475569;margin-top:12px;line-height:1.6}
  .sub{font-size:12.5px;color:#64748b;line-height:1.7}
  .penanda{display:inline-block;font-size:11px;font-weight:800;border-radius:999px;padding:2px 8px}
  .penanda.tuntas{background:#dcfce7;color:#166534}
  .penanda.habis{background:#fee2e2;color:#991b1b}
  .penanda.jalan{background:#fef3c7;color:#92400e}
</style>
</head>
<body>
<div class="kepala">
  <h1>Hasil Evaluasi BEE Smart</h1>
  <p>{{ $evaluasi->judul }} · SESI {{ $percobaan->bahasa === 'arab' ? 'BAHASA ARAB' : 'BAHASA INGGRIS' }}<br>{{ $siswa->nama_lengkap ?? '' }} ({{ $siswa->kelas ?? '-' }})</p>
</div>

<div class="bungkus">
  <div class="kartu nilai">
    <div class="angka {{ $percobaan->nilai >= $evaluasi->kkm ? '' : 'tidak' }}">{{ $percobaan->nilai }}</div>
    <div class="lulus {{ $percobaan->nilai >= $evaluasi->kkm ? 'ya' : 'belum' }}">
      {{ $percobaan->nilai >= $evaluasi->kkm ? 'LULUS · nilai minimal '.$evaluasi->kkm : 'Belum lulus · nilai minimal '.$evaluasi->kkm }}
    </div>
    <div class="rinci">
      <div><b>{{ $percobaan->benar }}</b>benar</div>
      <div><b>{{ $percobaan->salah }}</b>salah</div>
      <div><b>{{ $jumlahPeserta }}</b>peserta</div>
    </div>
    @if ($mode7)
      <p class="kaki" style="margin-top:12px">
        Nilai di atas adalah <b>rata-rata {{ $ringkas['mode_dinilai'] }} mode</b> yang kamu kerjakan
        ({{ $ringkas['dijawab'] }} soal dijawab dari {{ $ringkas['total'] }}). Mode yang belum dikerjakan tidak dihitung.
      </p>
    @endif
    <p class="kaki">
      Poin Student Root:
      @if ($poin['ada'])
        <b>+{{ $poin['poin'] }} poin sudah masuk</b> (hanya sekali per periode per bahasa).
      @elseif ($percobaan->nilai >= $evaluasi->kkm)
        nilai sudah mencapai KKM, tetapi entri poin tidak ditemukan — hubungi gurumu.
      @else
        belum bertambah — perlu nilai ≥ {{ $evaluasi->kkm }}.
      @endif
    </p>
  </div>

  @if ($mode7)
    <h2>Nilai tiap mode</h2>
    <div class="kartu">
      <table class="mode">
        <tr><th>Mode</th><th>Dijawab</th><th style="text-align:right">Benar</th><th style="text-align:right">Salah</th><th style="text-align:center">Babak</th><th style="text-align:right">Nilai</th><th>Keadaan</th></tr>
        @foreach (($ringkas['modes_pakai'] ?? \App\Http\Controllers\BeeEvaluasiController::MODE7) as $m)
          @php $x = $ringkas['modes'][$m]; @endphp
          <tr class="{{ $x['dinilai'] ? ($x['tuntas'] ? 'tuntas' : '') : 'belum-ikut' }}">
            <td>{{ $infoMode[$m]['ikon'] }} {{ $infoMode[$m]['label'] }}</td>
            <td>{{ $x['dijawab'] }}/{{ $x['total'] }}</td>
            <td class="angka">{{ $x['benar'] }}</td>
            <td class="angka">{{ $x['salah'] }}</td>
            <td style="text-align:center">{{ $x['babak'] }}@if ($x['babak'] > 1)<span class="sub"> · {{ implode(' · ', $x['nilai_babak']) }}</span>@endif</td>
            <td class="angka">{{ $x['dinilai'] ? $x['nilai'] : '-' }}</td>
            <td>
              @if (! $x['dinilai'])
                <span class="penanda">belum dikerjakan</span>
              @elseif ($x['babak_habis'])
                <span class="penanda habis">nyawa habis</span>
              @elseif ($x['tuntas'])
                <span class="penanda tuntas">tuntas</span>
              @else
                <span class="penanda jalan">berhenti di tengah</span>
              @endif
            </td>
          </tr>
        @endforeach
        <tr>
          <td colspan="5"><b>Rata-rata {{ $ringkas['mode_dinilai'] }} mode yang dikerjakan</b>@if ($ringkas['jumlah_babak'] > 7)<span class="sub"> ({{ $ringkas['jumlah_babak'] - 7 }} babak ulangan)</span>@endif</td>
          <td class="angka">{{ $ringkas['nilai'] }}</td>
          <td class="sub">hasil akhir sesi {{ $percobaan->bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris' }}</td>
        </tr>
      </table>
      <div class="catatan-poin">
        Rumus: nilai tiap mode = benar ÷ 20 × 100 — khusus Jodohkan: <b>poin ÷ 40 pasang × 100</b>
        (1 pasangan tepat di percobaan pertama = 1 poin; salah boleh dicoba lagi, tapi pasangan itu tidak dapat poin).
        Bila sebuah mode diulang (3 kali salah → babak baru),
        nilai mode = <b>rata-rata nilai tiap babak</b> — jadi mengulang tidak menaikkan nilai seenaknya.
        Nilai sesi bahasa = rata-rata mode yang dikerjakan. Pembahasan per soal menampilkan jawaban terakhirmu.
        Mode yang belum dikerjakan tidak ikut dihitung; mode yang berhenti karena nyawa habis tetap dihitung
        dengan soal yang tidak terjawab sebagai salah.
      </div>
    </div>
  @endif

  <h2>Pembahasan</h2>
  @foreach ($percobaan->soal as $s)
    @php $r = $nilai['rincian'][$s['nomor']] ?? null; @endphp
    <div class="soal {{ ($r['benar'] ?? false) ? 'benar' : 'salah' }}">
      <div class="kepala-soal">
        <span>
          Soal {{ $s['nomor'] }}
          @if ($mode7 && isset($s['mode']) && isset($infoMode[$s['mode']]))
            · {{ $infoMode[$s['mode']]['label'] }}
          @endif
        </span>
        <span>{{ ($r['benar'] ?? false) ? 'BENAR' : 'SALAH' }}</span>
      </div>

      @if (in_array($s['mode'] ?? '', ['dengar', 'imla_rumpang', 'imla_murni', 'ucap'], true) && ! empty($s['audio']))
        <audio controls preload="none" src="{{ url('berkas/'.$s['audio']) }}" style="width:100%;margin-top:8px"></audio>
      @endif

      <div class="tanya {{ ! empty($s['tanya_arab']) ? 'arab' : '' }}">{{ $s['tanya'] ?? '' }}</div>

      @if (($s['mode'] ?? '') === 'jodoh' && ! empty($s['kiri']))
        @php
          // papan jodoh: jawaban tersimpan "kiri-kanan-status" (1 = dapat poin, 2 = hangus, 3 = terpasang tanpa poin)
          $entriPapan = [];
          foreach (preg_split('~\s+~', trim((string) ($r['jawaban'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $potongan) {
              $bagian = explode('-', $potongan);
              if (count($bagian) === 3) {
                  $entriPapan[(int) $bagian[0]] = ['kanan' => (int) $bagian[1], 'status' => (int) $bagian[2]];
              }
          }
          $kKunci = preg_split('~\s+~', trim((string) ($r['kunci'] ?? '')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
          $jumlahPasang = count($kKunci) ?: count($s['kiri']);
          $poinPapan = 0;
          $tanpaPoinPapan = 0;
          foreach ($entriPapan as $e) {
              if ($e['status'] === 1) { $poinPapan++; }
              if ($e['status'] === 2 || $e['status'] === 3) { $tanpaPoinPapan++; }
          }
        @endphp
        <div class="baris">
          <span class="label">Pasanganmu (tepat di percobaan pertama = 1 poin)</span><br>
          @foreach ($s['kiri'] as $i => $kiri)
            @php $e = $entriPapan[$i + 1] ?? null; @endphp
            @if ($e === null)
              <span class="salah-teks">{{ $kiri }} → (belum dipasangkan)</span><br>
            @else
              <span class="{{ $e['status'] === 1 ? 'benar-teks' : 'salah-teks' }}">{{ $kiri }} → {{ $s['kanan'][$e['kanan'] - 1] ?? $e['kanan'] }}{{ $e['status'] === 1 ? ' ✔ (+1 poin)' : ' (tanpa poin)' }}</span><br>
            @endif
          @endforeach
          <span class="label">Poin papan ini: {{ $poinPapan }} dari {{ $jumlahPasang }} pasang{{ $tanpaPoinPapan ? ' · '.$tanpaPoinPapan.' pasangan tanpa poin' : '' }}</span>
        </div>
      @endif

      <div class="baris">
        <span class="label">Jawabanmu</span><br>
        <span class="{{ ($r['benar'] ?? false) ? 'benar-teks' : 'salah-teks' }}">{{ ($r['jawaban'] ?? '') !== '' && ($r['jawaban'] ?? null) !== null ? $r['jawaban'] : '(tidak dijawab)' }}</span>
      </div>

      @if (! ($r['benar'] ?? false))
        <div class="baris">
          <span class="label">Jawaban benar</span><br>
          <span class="benar-teks">{{ $r['kunci'] ?? '' }}</span>
        </div>
      @endif
    </div>
  @endforeach

  <a class="tombol" href="{{ route('evaluasi.sesi') }}">← Kembali ke daftar sesi</a>
  <a class="tombol" href="{{ route('evaluasi.masuk') }}">Ganti NIS/NISN</a>
  <p class="kaki">
    Simpan tangkapan layar halaman ini bila perlu.
  </p>
</div>
</body>
</html>
