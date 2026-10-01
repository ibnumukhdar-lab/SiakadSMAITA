<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Evaluasi BEE Smart — SMA IT Arafah</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#f5f7fb;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;color:#0f172a}
  .kepala{background:linear-gradient(135deg,#152a47,#1e3a8a);color:#fff;padding:22px 18px}
  .bungkus{max-width:680px;margin:0 auto;padding:0 16px}
  .kepala .bungkus{padding:0}
  .kepala h1{margin:0;font-size:19px;letter-spacing:-.01em}
  .kepala p{margin:6px 0 0;font-size:13px;color:rgba(255,255,255,.82)}
  .isi{padding:20px 0 48px}
  .kartu{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px;margin-bottom:14px}
  .pesan{border-radius:14px;padding:14px 16px;font-size:14px;margin-bottom:14px;line-height:1.6}
  .pesan.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af}
  .pesan.salah{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
  .modul{border:1px solid #e2e8f0;border-radius:14px;padding:15px;margin-bottom:12px;background:#fff}
  .modul.judul{font-weight:800;color:#1e3a8a;font-size:15.5px}
  .rinci{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
  .pil{font-size:11.5px;font-weight:700;background:#f1f5f9;color:#475569;border-radius:999px;padding:4px 11px}
  .pil.emas{background:#fef6e7;color:#92600c}
  label{display:block;font-size:12.5px;font-weight:700;color:#475569;margin-bottom:6px}
  select,input{width:100%;border:1px solid #cbd5e1;border-radius:12px;padding:12px 14px;font-size:15px;background:#fff;color:#0f172a}
  input:focus,select:focus{outline:2px solid #93c5fd;border-color:#1e3a8a}
  .tombol{display:block;width:100%;border:0;border-radius:12px;background:#1e3a8a;color:#fff;font-size:15.5px;font-weight:800;padding:14px;margin-top:14px;cursor:pointer}
  .tombol:hover{background:#152a47}
  .kaki{font-size:12.5px;color:#64748b;text-align:center;line-height:1.7;margin-top:18px}
  .kosong{text-align:center;color:#94a3b8;font-size:14px;padding:26px 10px}
</style>
</head>
<body>
<div class="kepala">
  <div class="bungkus">
    <h1>🐝 Evaluasi BEE Smart</h1>
    <p>SMA IT Arafah Boarding School · penilaian triwulan &amp; semester. Tidak perlu login — cukup NIS/NISN.</p>
  </div>
</div>

<div class="bungkus isi">
  @if ($pesan)
    <div class="pesan {{ str_contains($pesan, 'tidak ditemukan') || str_contains($pesan, 'sudah memakai') || str_contains($pesan, 'belum punya kelas') ? 'salah' : 'info' }}">{{ $pesan }}</div>
  @endif

  @if ($evaluasi->isEmpty())
    <div class="kartu kosong">
      Belum ada evaluasi yang dibuka.<br>Hubungi guru BEE Smart-mu.
    </div>
  @else
    @foreach ($evaluasi as $e)
      <div class="modul">
        <div class="modul judul">{{ $e->judul }}</div>
        <div class="rinci">
          <span class="pil">{{ $e->label_jenis }}</span>
          <span class="pil">{{ $e->mulai->format('d M') }} – {{ $e->selesai->format('d M Y') }}</span>
          <span class="pil">{{ $e->jumlah_soal }} soal</span>
          <span class="pil">{{ $e->durasi_menit }} menit</span>
          <span class="pil emas">Lulus ≥ {{ $e->kkm }}</span>
          <span class="pil">maks {{ $e->maks_percobaan }}× percobaan</span>
        </div>
      </div>
    @endforeach

    <div class="kartu">
      <form method="post" action="{{ route('evaluasi.verifikasi') }}">
        @csrf
        <label for="evaluasi_id">Pilih evaluasi</label>
        <select name="evaluasi_id" id="evaluasi_id" required>
          @foreach ($evaluasi as $e)
            <option value="{{ $e->id }}">{{ $e->judul }}</option>
          @endforeach
        </select>

        <label for="nis" style="margin-top:14px">NIS / NISN</label>
        <input name="nis" id="nis" value="{{ $nis }}" inputmode="numeric" autocomplete="off" required placeholder="Contoh: 624185">

        <button class="tombol" type="submit">Lanjut pilih sesi →</button>
        <p style="font-size:12.5px;color:#64748b;margin:10px 0 0;line-height:1.6">
          Setelah menekan tombol ini kamu memilih <strong>dua sesi yang terpisah</strong>:
          <strong>Bahasa Inggris</strong> dan <strong>Bahasa Arab</strong> — nilainya dicatat sendiri-sendiri,
          jadi kamu bebas mau mulai dari yang mana.
        </p>
      </form>
      <p class="kaki">
        Pastikan NIS/NISN benar — nilaimu dicatat atas nama itu.<br>
        Waktu berjalan begitu kamu menekan tombol. Siapkan koneksi yang stabil.
      </p>
    </div>
  @endif
</div>
</body>
</html>
