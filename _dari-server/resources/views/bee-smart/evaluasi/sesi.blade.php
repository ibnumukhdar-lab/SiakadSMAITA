<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Pilih Sesi — Evaluasi BEE Smart</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#f5f7fb;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;color:#0f172a;padding-bottom:40px}
  .kepala{background:linear-gradient(135deg,#152a47,#1e3a8a);color:#fff;padding:20px 18px}
  .kepala h1{margin:0;font-size:18px}
  .kepala p{margin:6px 0 0;font-size:13px;color:rgba(255,255,255,.85)}
  .bungkus{max-width:680px;margin:0 auto;padding:0 16px}
  .pesan{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:14px;padding:13px 16px;font-size:13.5px;margin:16px 0 0;line-height:1.6}
  h2{font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin:24px 0 10px}
  .periode{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-bottom:14px}
  .periode .judul{font-weight:800;font-size:15.5px;color:#1e3a8a}
  .periode .rinci{font-size:12px;color:#64748b;margin-top:5px;line-height:1.7}
  .sesi{display:grid;gap:10px;margin-top:14px}
  @media (min-width:560px){.sesi{grid-template-columns:1fr 1fr}}
  .kartu-sesi{border:1.5px solid #e2e8f0;border-radius:14px;padding:14px;background:#fff;display:block;text-decoration:none}
  .kartu-sesi.inggris{border-color:#c7d7f5;background:#f5f9ff}
  .kartu-sesi.arab{border-color:#f0dfc0;background:#fffaf0}
  .kartu-sesi .nama{font-size:16px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px}
  .kartu-sesi .ket{font-size:12.5px;color:#64748b;margin-top:6px;line-height:1.6}
  .pil{display:inline-block;font-size:11px;font-weight:800;border-radius:999px;padding:3px 10px;margin-top:9px}
  .pil.belum{background:#eef2f7;color:#475569}
  .pil.jalan{background:#fef3c7;color:#92400e}
  .pil.selesai{background:#dcfce7;color:#166534}
  .nilai{font-size:22px;font-weight:800;color:#1e3a8a;float:right}
  .kosong{text-align:center;color:#94a3b8;font-size:14px;padding:26px 10px;background:#fff;border:1px solid #e2e8f0;border-radius:16px}
  .kaki{font-size:12px;color:#94a3b8;text-align:center;margin-top:16px;line-height:1.7}

  /* ---- kemajuan sub-sesi mode di dalam satu sesi bahasa ---- */
  .kemajuan{margin-top:12px;border-top:1px dashed #dbe4f0;padding-top:11px}
  .kemajuan .ringkas{display:flex;gap:14px;flex-wrap:wrap;font-size:11.5px;color:#64748b;margin-bottom:9px}
  .kemajuan .ringkas b{display:block;font-size:16px;color:#1e3a8a}
  .mode-pil{display:flex;flex-wrap:wrap;gap:5px}
  .mode-pil span{font-size:10.5px;font-weight:800;border-radius:999px;padding:3px 8px;border:1px solid #e2e8f0;background:#fff;color:#64748b;white-space:nowrap}
  .mode-pil span.tuntas{background:#dcfce7;border-color:#86efac;color:#166534}
  .mode-pil span.jalan{background:#fef3c7;border-color:#fde68a;color:#92400e}
  .mode-pil span.habis{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
</style>
</head>
<body>
<div class="kepala">
  <h1>🐝 Evaluasi BEE Smart — pilih sesi</h1>
  <p>{{ $siswa->nama_lengkap }} · {{ $siswa->kelas ?? '-' }} · NIS {{ $siswa->nis ?? '-' }}</p>
</div>

<div class="bungkus">
  @if ($pesan)
    <div class="pesan">{{ $pesan }}</div>
  @endif

  @if ($periode->isEmpty())
    <div class="kosong" style="margin-top:16px">Belum ada sesi evaluasi yang dibuka.<br>Hubungi guru BEE Smart-mu.</div>
  @else
    @foreach ($periode as $e)
      <h2>{{ $e->judul }}</h2>
      <div class="periode">
        <div class="judul">{{ $e->label_kelas }} · {{ $e->label_jenis }} · sesi terpisah</div>
        <div class="rinci">
          {{ $e->mulai->format('d M') }} – {{ $e->selesai->format('d M Y') }} ·
          @php
            $jmlMode = count(\App\Http\Controllers\BeeEvaluasiController::MODE7);
            $totalSoal = ($jmlMode - 1) * 20 + \App\Http\Controllers\BeeEvaluasiController::PAPAN_JODOH;
          @endphp
          <b>{{ $jmlMode }} mode · {{ $totalSoal }} soal per sesi bahasa</b> ·
          lulus ≥ {{ $e->kkm }} · maks {{ $e->maks_percobaan }}× per sesi
        </div>

        <div class="sesi">
          @foreach (['inggris' => ['🇬🇧 Bahasa Inggris', 'Kosakata &amp; kalimat bahasa Inggris'], 'arab' => ['🇸🇦 Bahasa Arab', 'Mufrodat &amp; jumlah bahasa Arab']] as $kode => $info)
            @php
              $p = $percobaan[$e->id][$kode] ?? null;
              $sisa = max(0, $e->maks_percobaan - ($jumlah[$e->id][$kode] ?? 0));
              $rk = $ringkas[$e->id][$kode] ?? null;
            @endphp
            <div class="kartu-sesi {{ $kode }}">
              <div class="nama">
                {!! $info[0] !!}
                @if ($p && $p->status === 'selesai' && ! $rk)<span class="nilai">{{ $p->nilai }}</span>@endif
              </div>
              <div class="ket">{{ $info[1] }} · {{ $sisa }} percobaan tersisa</div>

              @if (! $p)
                <span class="pil belum">Belum dikerjakan</span>
              @elseif ($p->status === 'berjalan')
                <span class="pil jalan">Sedang berjalan — lanjutkan</span>
              @else
                <span class="pil selesai">{{ $p->nilai >= $e->kkm ? 'Lulus' : 'Belum lulus' }} · nilai {{ $p->nilai }}{{ $rk ? ' (rata-rata mode)' : ' · benar '.$p->benar.'/'.($p->benar + $p->salah) }}</span>
              @endif

              @if ($rk)
                {{-- Kemajuan sub-sesi mode (urutan bebas, boleh berhenti lalu lanjut) --}}
                <div class="kemajuan">
                  <div class="ringkas">
                    <div><b>{{ $rk['mode_dinilai'] }}/{{ count($rk['modes_pakai'] ?? \App\Http\Controllers\BeeEvaluasiController::MODE7) }}</b>mode dikerjakan</div>
                    <div><b>{{ $rk['dijawab'] }}</b>soal dijawab</div>
                    <div><b>{{ $rk['nilai'] }}</b>rata-rata sekarang</div>
                  </div>
                  <div class="mode-pil">
                    @foreach (($rk['modes_pakai'] ?? \App\Http\Controllers\BeeEvaluasiController::MODE7) as $m)
                      @php $x = $rk['modes'][$m]; @endphp
                      <span class="{{ $x['tuntas'] ? 'tuntas' : ($x['babak_habis'] ? 'habis' : ($x['dinilai'] ? 'jalan' : '')) }}">
                        {{ $infoMode[$m]['ikon'] }} {{ $infoMode[$m]['label'] }}
                        {{ $x['dinilai'] ? $x['nilai'] : '-' }}@if ($x['babak'] > 1) · {{ $x['babak'] }} babak{{ $x['babak_habis'] ? ' (bisa diulang)' : '' }}@endif
                      </span>
                    @endforeach
                  </div>
                </div>
              @else
                {{-- Belum dikerjakan: tetap tunjukkan mode yang ada di dalam sesi --}}
                <div class="kemajuan">
                  <div class="sub" style="margin-bottom:9px">
                    @php
                      $jmlMode = count(\App\Http\Controllers\BeeEvaluasiController::MODE7);
                      $totalSoal = ($jmlMode - 1) * 20 + \App\Http\Controllers\BeeEvaluasiController::PAPAN_JODOH;
                    @endphp
                    Isi sesi: <b>{{ $jmlMode }} mode</b> · {{ $totalSoal }} soal · 3 nyawa tiap mode
                  </div>
                  <div class="mode-pil">
                    @foreach (\App\Http\Controllers\BeeEvaluasiController::MODE7 as $m)
                      <span>{{ $infoMode[$m]['ikon'] }} {{ $infoMode[$m]['label'] }}</span>
                    @endforeach
                  </div>
                </div>
              @endif

              <form method="post" action="{{ route('evaluasi.mulai') }}" style="margin-top:12px">
                @csrf
                <input type="hidden" name="evaluasi_id" value="{{ $e->id }}">
                <input type="hidden" name="bahasa" value="{{ $kode }}">
                @if ($p && $p->status === 'selesai')
                  <a class="pil belum" style="text-decoration:none" href="{{ route('evaluasi.hasil', $p->kode) }}">Lihat hasil &amp; pembahasan</a>
                  @if ($sisa > 0)
                    <button type="submit" style="border:0;background:none;color:#1e3a8a;font-weight:800;font-size:12.5px;cursor:pointer;padding:6px 0;margin-left:8px">Ulangi ({{ $sisa }}× tersisa)</button>
                  @endif
                @else
                  <button type="submit" style="border:0;border-radius:11px;background:#1e3a8a;color:#fff;font-weight:800;font-size:14.5px;padding:12px 18px;width:100%;cursor:pointer">
                    {{ $p ? 'Lanjutkan sesi ini →' : 'Mulai '.(str_contains($kode, 'arab') ? 'Bahasa Arab' : 'Bahasa Inggris').' →' }}
                  </button>
                @endif
              </form>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  @endif

  <p class="kaki">
    Tidak perlu login. Tiap sesi bahasa berisi <b>{{ count(\App\Http\Controllers\BeeEvaluasiController::MODE7) }} mode</b> × 20 soal, urutan bebas, 3 nyawa tiap mode, tanpa timer.
  </p>
  <p class="kaki"><a href="{{ route('evaluasi.masuk') }}" style="color:#1e3a8a;font-weight:700">← Ganti NIS/NISN</a></p>
</div>
</body>
</html>
