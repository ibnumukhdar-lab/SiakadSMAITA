<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Mengerjakan — {{ $evaluasi->judul }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#f5f7fb;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;color:#0f172a;padding-bottom:96px}
  .kepala{position:sticky;top:0;z-index:10;background:linear-gradient(135deg,#152a47,#1e3a8a);color:#fff;padding:14px 16px}
  .kepala-atas{display:flex;align-items:center;justify-content:space-between;gap:12px}
  .kepala h1{margin:0;font-size:15px;font-weight:800;line-height:1.3}
  .kepala small{display:block;font-weight:500;opacity:.8;font-size:11.5px;margin-top:2px}
  .jam{background:rgba(255,255,255,.14);border-radius:10px;padding:7px 12px;font-weight:800;font-size:15px;font-variant-numeric:tabular-nums;white-space:nowrap}
  .jam.waspada{background:#b91c1c}
  .tanpa-jam{background:rgba(255,255,255,.14);border-radius:10px;padding:7px 12px;font-weight:700;font-size:12px;white-space:nowrap;text-align:right;line-height:1.4}
  .bar{height:5px;background:rgba(255,255,255,.2);margin-top:12px;border-radius:999px;overflow:hidden}
  .bar span{display:block;height:100%;width:0;background:#facc15;transition:width .25s}
  .bungkus{max-width:680px;margin:0 auto;padding:16px}
  .kartu{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:18px}
  .nomor{font-size:12px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;color:#94a3b8}
  .petunjuk{font-size:13.5px;color:#475569;margin:10px 0 4px;line-height:1.6}
  .tanya{font-size:26px;font-weight:800;color:#1e3a8a;margin:14px 0 4px;line-height:1.4;word-break:break-word}
  .tanya.arab{font-size:30px;direction:rtl;text-align:right;font-family:'Noto Naskh Arabic','Amiri',serif}
  .sub{font-size:13px;color:#64748b;font-style:italic}
  .pilihan{display:grid;gap:10px;margin-top:16px}
  .pilih{border:1.5px solid #e2e8f0;background:#fff;border-radius:12px;padding:14px;font-size:15.5px;text-align:left;cursor:pointer;font-weight:600;color:#0f172a}
  .pilih:hover{border-color:#93c5fd}
  .pilih.aktif{border-color:#1e3a8a;background:#eff6ff;color:#1e3a8a}
  .pilih.benar{border-color:#16a34a;background:#dcfce7;color:#166534}
  .pilih.salah{border-color:#dc2626;background:#fee2e2;color:#991b1b}
  .pilih:disabled{cursor:default;opacity:.85}
  .huruf,.kata{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
  .huruf button,.kata button{border:1.5px solid #dbe4f0;background:#fff;border-radius:10px;padding:10px 14px;font-size:16px;font-weight:700;cursor:pointer;color:#0f172a}
  .huruf button:disabled{opacity:.3;cursor:default}
  .kata button.pakai{display:none}
  .jawab{border:1.5px dashed #cbd5e1;border-radius:12px;min-height:52px;padding:12px;margin-top:16px;font-size:16px;font-weight:700;color:#1e3a8a;display:flex;align-items:center;flex-wrap:wrap;gap:6px}
  .jawab.kosong{color:#cbd5e1;font-weight:500;font-size:14px}
  .jawab button{border:1.5px solid #bfdbfe;background:#eff6ff;border-radius:9px;padding:7px 11px;font-size:15px;font-weight:700;color:#1e3a8a;cursor:pointer}
  .balik{border:0;background:#f1f5f9;color:#475569;border-radius:8px;padding:7px 12px;font-size:12.5px;font-weight:700;cursor:pointer;margin-top:12px}
  .audio{border:0;background:#1e3a8a;color:#fff;border-radius:12px;padding:14px 20px;font-size:15px;font-weight:800;cursor:pointer;margin-top:14px}
  .kendali{position:fixed;left:0;right:0;bottom:0;background:#fff;border-top:1px solid #e2e8f0;padding:12px 16px;display:flex;gap:10px;align-items:center;z-index:12}
  .kendali .bungkus-kecil{max-width:680px;margin:0 auto;display:flex;gap:10px;width:100%}
  .kendali button{flex:1;border:0;border-radius:12px;padding:14px;font-size:15px;font-weight:800;cursor:pointer}
  .kendali .kedua{background:#f1f5f9;color:#334155}
  .kendali .utama{background:#1e3a8a;color:#fff}
  .kendali .utama.selesai{background:#15803d}
  .kendali button:disabled{opacity:.45;cursor:default}
  .daftar-nomor{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
  .daftar-nomor button{width:32px;height:32px;border-radius:9px;border:1px solid #e2e8f0;background:#fff;font-size:12.5px;font-weight:700;color:#64748b;cursor:pointer}
  .daftar-nomor button.terjawab{background:#dcfce7;border-color:#86efac;color:#166534}
  .daftar-nomor button.salah{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
  .daftar-nomor button.kini{border-color:#1e3a8a;color:#1e3a8a;box-shadow:0 0 0 2px #bfdbfe}
  .peringatan{background:#fef3c7;border:1px solid #fde68a;color:#92400e;border-radius:12px;padding:12px 14px;font-size:13px;margin-bottom:12px;line-height:1.6}
  .pesan{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;border-radius:12px;padding:12px 14px;font-size:13.5px;margin-bottom:12px;line-height:1.6}

  /* ---- 7 mode ---- */
  .nyawa{display:flex;align-items:center;gap:5px;font-size:16px;line-height:1}
  .nyawa s{text-decoration:none;opacity:.25;filter:grayscale(1)}
  .menu-mode{display:grid;gap:10px;margin-top:14px}
  @media (min-width:600px){.menu-mode{grid-template-columns:1fr 1fr}}
  .kartu-mode{display:block;width:100%;text-align:left;border:1.5px solid #e2e8f0;background:#fff;border-radius:14px;padding:13px 14px;cursor:pointer}
  .kartu-mode:hover{border-color:#93c5fd}
  .kartu-mode.tuntas{border-color:#86efac;background:#f0fdf4}
  .kartu-mode.jalan{border-color:#fcd34d;background:#fffbeb}
  .kartu-mode.habis{border-color:#fca5a5;background:#fef2f2}
  .kartu-mode .nm{font-size:15px;font-weight:800;color:#0f172a;display:flex;justify-content:space-between;gap:8px;align-items:center}
  .kartu-mode .cr{font-size:12px;color:#64748b;margin-top:5px;line-height:1.5}
  .kartu-mode .baris2{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:9px}
  .pil{display:inline-block;font-size:11px;font-weight:800;border-radius:999px;padding:3px 9px}
  .pil.belum{background:#eef2f7;color:#475569}
  .pil.jalan{background:#fef3c7;color:#92400e}
  .pil.tuntas{background:#dcfce7;color:#166534}
  .pil.habis{background:#fee2e2;color:#991b1b}
  .angka-mode{font-size:17px;font-weight:800;color:#1e3a8a}
  .judul-mode{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:6px}
  .judul-mode .nama{font-size:15.5px;font-weight:800;color:#1e3a8a}
  .balasan{border-radius:12px;padding:12px 14px;font-size:14px;font-weight:700;margin-top:14px;line-height:1.6;display:none}
  .balasan.benar{background:#dcfce7;border:1px solid #86efac;color:#166534;display:block}
  .balasan.salah{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;display:block}
  .balasan.info{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;display:block}
  .lubang{border-bottom:3px solid #1e3a8a;padding:0 14px;color:#94a3b8;font-weight:700}
  .lubang.isi{border-bottom-color:#16a34a;color:#166534}
  .lubang.sasaran{background:#dbeafe;border-radius:6px 6px 0 0}
  .masukan{width:100%;border:1.5px solid #cbd5e1;border-radius:12px;padding:13px 14px;font-size:17px;font-weight:700;margin-top:14px;color:#0f172a}
  .masukan.arab{direction:rtl;text-align:right;font-family:'Noto Naskh Arabic','Amiri',serif;font-size:21px}
  .tombol-aksi{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
  .tombol-aksi button{border:0;border-radius:11px;padding:12px 16px;font-size:14px;font-weight:800;cursor:pointer;background:#1e3a8a;color:#fff}
  .tombol-aksi button.pudar{background:#f1f5f9;color:#334155}
  .tombol-aksi button:disabled{opacity:.45;cursor:default}
  .mic{border:0;border-radius:999px;width:78px;height:78px;font-size:30px;background:#1e3a8a;color:#fff;cursor:pointer;margin-top:14px}
  .mic.dengar{background:#b91c1c;animation:denyut 1s infinite}
  /* mode Ucap: mikrofon kecil sebaris status + kotak "yang terdengar" (penilaian otomatis) */
  .mic-baris{display:flex;align-items:center;gap:10px;margin-top:12px}
  .mic-kecil{width:46px;height:46px;border-radius:50%;border:1.5px solid #cbd5e1;background:#fff;font-size:20px;line-height:1;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;padding:0;flex:0 0 auto}
  .mic-kecil.dengar{border-color:#dc2626;background:#fee2e2;animation:denyut 1.1s infinite}
  .mic-kecil[disabled]{opacity:.55;cursor:default}
  .mic-status{font-size:13px;color:#64748b;line-height:1.35}
  .dengar-kotak{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:9px 12px;margin-top:10px}
  .dengar-kotak .label-kotak{font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8}
  .dengar-kotak .dengar-teks{font-size:17px;font-weight:700;color:#0f172a;margin-top:2px;min-height:22px;word-break:break-word}
  .dengar-kotak .dengar-teks.samar{color:#94a3b8;font-weight:600}
  .dengar-kotak.kena{border-color:#86efac;background:#f0fdf4}
  .dengar-kotak.kena .dengar-teks{color:#166534}
  @keyframes denyut{50%{opacity:.55}}
  .jodoh{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px}
  .jodoh .kolom{display:grid;gap:8px;align-content:start}
  .jodoh .kepala-kolom{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8}
  .jodoh button{border:1.5px solid #e2e8f0;background:#fff;border-radius:11px;padding:11px 12px;font-size:14.5px;font-weight:700;cursor:pointer;color:#0f172a;text-align:left;line-height:1.4}
  .jodoh button.pilih-aktif{border-color:#1e3a8a;background:#eff6ff;color:#1e3a8a}
  .jodoh button.hijau{border-color:#16a34a;background:#dcfce7;color:#166534;cursor:default}
  .jodoh button.merah{border-color:#dc2626;background:#fee2e2;color:#991b1b;cursor:default}
  .jodoh button.arab{direction:rtl;text-align:right;font-family:'Noto Naskh Arabic','Amiri',serif}
  .jodoh .no{float:right;color:#94a3b8;font-size:12px;font-weight:800;margin-left:8px}
  /* papan jodoh versi 2: pasangan tepat lenyap, yang salah ditandai "tanpa poin" */
  .jodoh button.hangus{border-color:#fca5a5;background:#fff7f7;color:#991b1b;text-decoration:line-through}
  .jodoh button.hangus .tanda{color:#dc2626;font-weight:800;text-decoration:none}
  .jodoh button.sembunyi{display:none}
  .jodoh button.hijau.hilang-anim{opacity:0;transition:opacity .35s}
  .jodoh .putar{display:flex;align-items:center;gap:7px;font-weight:800}
  .jodoh-info{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:10px}
  .poin-pil{display:inline-block;font-size:11.5px;font-weight:800;border-radius:999px;padding:4px 10px;background:#eef2ff;color:#3730a3}
  .poin-pil.baik{background:#dcfce7;color:#166534}
  @media (max-width:430px){
    .jodoh{gap:7px}
    .jodoh button{padding:9px 10px;font-size:13.5px;border-radius:10px}
    .jodoh .kepala-kolom{font-size:10px}
    .jodoh .putar{gap:5px}
  }
  .tutup-layar{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;padding:18px;z-index:20}
  .tutup-layar.buka{display:flex}
  .tutup-layar .kotak{background:#fff;border-radius:18px;padding:22px;max-width:420px;width:100%;text-align:center}
  .tutup-layar h3{margin:0 0 8px;font-size:19px;color:#1e3a8a}
  .tutup-layar p{margin:0 0 6px;font-size:13.5px;color:#475569;line-height:1.6}
  .tutup-layar .tombol-aksi{justify-content:center}
  .ringkas-sesi{display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;font-size:12.5px;color:#64748b}
  .ringkas-sesi b{display:block;font-size:18px;color:#0f172a}

  /* ---- hati (SVG, merahnya pasti di semua HP) ---- */
  .hati{display:inline-block;line-height:0}
  .hati svg{width:17px;height:17px;display:block;filter:drop-shadow(0 1px 1px rgba(153,27,27,.35))}
  .hati.mati{opacity:.45}

  /* ---- bunyi & efek "buzz" merah saat salah ---- */
  .kartu.buzz{animation:buzz-pukul 520ms cubic-bezier(.36,.07,.19,.97) both;border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.35),0 14px 34px rgba(220,38,38,.35)}
  @keyframes buzz-pukul{10%,90%{transform:translateX(-3px)}20%,80%{transform:translateX(6px)}30%,50%,70%{transform:translateX(-11px)}40%,60%{transform:translateX(11px)}}
  .kartu.pendar{animation:pendar-hijau 560ms ease-out;border-color:#16a34a!important;box-shadow:0 0 0 3px rgba(22,163,74,.28)}
  @keyframes pendar-hijau{0%{box-shadow:0 0 0 14px rgba(22,163,74,.45)}100%{box-shadow:0 0 0 0 rgba(22,163,74,0)}}
  .kilat{position:fixed;inset:0;z-index:40;pointer-events:none;opacity:0;background:radial-gradient(circle at 50% 45%,rgba(239,68,68,.62),rgba(185,28,28,.42) 42%,rgba(127,29,29,.12) 68%,rgba(127,29,29,0) 100%)}
  .kilat.nyala{animation:kilat-merah 660ms ease-out}
  @keyframes kilat-merah{0%{opacity:0}10%{opacity:1}55%{opacity:.65}100%{opacity:0}}
  .buzz-teks{position:fixed;left:50%;top:38%;z-index:41;pointer-events:none;transform:translate(-50%,-50%) rotate(-6deg);font-size:46px;font-weight:900;letter-spacing:.06em;color:#fff;text-shadow:0 4px 0 #991b1b,0 0 28px rgba(239,68,68,.95);opacity:0}
  .buzz-teks.nyala{animation:buzz-teks 720ms ease-out}
  @keyframes buzz-teks{0%{opacity:0;transform:translate(-50%,-50%) rotate(-6deg) scale(.55)}18%{opacity:1;transform:translate(-50%,-50%) rotate(-6deg) scale(1.14)}70%{opacity:1}100%{opacity:0;transform:translate(-50%,-50%) rotate(-6deg) scale(1)}}
  @media (max-width:520px){.buzz-teks{font-size:34px}}

  /* ---- seret balok kata (drag_rumpang) ---- */
  .blok-kotak{background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;padding:14px;margin-top:14px}
  .blok-kotak.solid{border-style:solid;background:#fff}
  .label-kotak{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:9px}
  .kalimat-kotak{font-size:21px;font-weight:800;color:#1e3a8a;line-height:2.1}
  .kalimat-kotak.arab{font-size:26px;direction:rtl;text-align:right;font-family:'Noto Naskh Arabic','Amiri',serif}
  .lubang-drop{display:inline-flex;align-items:center;justify-content:center;min-width:118px;min-height:42px;margin:0 4px;padding:2px 12px;border:2px dashed #93c5fd;border-radius:11px;background:#eff6ff;color:#60a5fa;font-size:12.5px;font-weight:700;vertical-align:middle;transition:background .15s,border-color .15s}
  .lubang-drop.sasaran{border-color:#1e3a8a;background:#dbeafe;color:#1e3a8a;box-shadow:0 0 0 4px rgba(30,58,138,.14)}
  .lubang-drop.isi{border-style:solid;border-color:#16a34a;background:#dcfce7;color:#166534;font-size:20px;font-weight:800}
  .balok-tray{display:flex;flex-wrap:wrap;gap:10px}
  .balok-tray button{border:1.5px solid #cbd5e1;background:#fff;border-radius:12px;padding:12px 16px;font-size:17px;font-weight:800;color:#0f172a;cursor:pointer;min-height:46px;box-shadow:0 2px 0 #e2e8f0}
  .balok-tray button:hover{border-color:#93c5fd}
  .balok-tray button.terpakai{opacity:.35;border-style:dashed;box-shadow:none;cursor:default}
  .balok-tray.arab button{direction:rtl;text-align:right;font-family:'Noto Naskh Arabic','Amiri',serif;font-size:20px}
  .drag-aksi{display:flex;flex-wrap:wrap;gap:9px;margin-top:14px}
  .drag-aksi button{border:0;border-radius:11px;padding:13px 18px;font-size:14.5px;font-weight:800;cursor:pointer}
  .drag-aksi .utama{background:#1e3a8a;color:#fff}
  .drag-aksi .pudar{background:#f1f5f9;color:#334155}
  .drag-aksi button:disabled{opacity:.45;cursor:default}
  .babak-pil{display:inline-block;font-size:11px;font-weight:800;border-radius:999px;padding:3px 9px;background:#eef2f7;color:#475569;margin-left:6px}
  .tanya.satuan{font-size:33px;text-align:center;letter-spacing:.01em;margin:20px 0 8px}
  .tanya.audio-saja{font-size:14px;font-weight:700;color:#64748b;text-align:center;margin:4px 0 2px;letter-spacing:.02em}
  .sesi-pil{display:inline-block;font-size:11px;font-weight:800;border-radius:999px;padding:3px 9px;background:#e0e7ff;color:#3730a3;margin-right:6px}
  .audio-kotak{display:flex;justify-content:center;margin:10px 0 4px}
  /* terjemahan Indonesia pada soal "Lengkapi Kalimat" */
  .arti-kalimat{margin:12px 0 0;padding:9px 13px;background:#f6f9fc;border-left:3px solid #93c5fd;border-radius:0 10px 10px 0;font-size:14.5px;color:#475569;line-height:1.6}
  .arti-kalimat .arti-label{display:block;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px}
</style>
</head>
<body>

<div class="kepala">
  <div class="kepala-atas">
    <h1>{{ $evaluasi->judul }} — {{ $percobaan->bahasa === 'arab' ? 'Bahasa Arab' : 'Bahasa Inggris' }}<small>{{ $siswa->nama_lengkap ?? '' }} · {{ $siswa->kelas ?? '' }} · NIS {{ $siswa->nis ?? '' }}</small></h1>
    @if ($mode7)
      <div class="tanpa-jam">{{ $jmlMode }} mode · 20 soal/mode<br>tanpa batas waktu</div>
    @else
      <div class="jam" id="jam">{{ gmdate('i:s', $sisaDetik) }}</div>
    @endif
  </div>
  <div class="bar"><span id="bar"></span></div>
</div>

@if ($mode7)
{{-- ============================================================ ALUR 7 MODE --}}
<div class="bungkus" id="layarMenu">
  @if ($pesan)
    <div class="pesan">{{ $pesan }}</div>
  @endif
  <div class="peringatan">
    <b>{{ $jmlMode }} mode</b> × 20 soal · urutan bebas · <b>3 nyawa</b> tiap mode · tersimpan otomatis · tanpa timer
  </div>

  <div class="kartu">
    <div class="judul-mode">
      <div class="nama">Pilih mode</div>
    </div>
    <div class="sub">Nilai sesi = rata-rata mode yang dikerjakan. Mode yang belum dikerjakan tidak dihitung.</div>
    <div class="menu-mode" id="menuMode"></div>
  </div>

  <form method="post" action="{{ route('evaluasi.selesai', $percobaan->kode) }}" id="formSelesai">
    @csrf
    <div id="kiriman"></div>
  </form>

  <div class="kartu" style="margin-top:14px">
    <div class="nomor">Keadaan sesi</div>
    <div class="ringkas-sesi" id="ringkasSesi"></div>
    <div class="tombol-aksi">
      <button type="button" id="tombolSelesaiSesi">Selesai sesi &amp; lihat nilai →</button>
    </div>
    <div class="sub" style="margin-top:10px">Menutup sesi ini, lalu menampilkan halaman hasil.</div>
  </div>
</div>

<div class="bungkus" id="layarMode" style="display:none">
  <div class="kartu" id="kartuSoal">
    <div class="judul-mode">
      <div class="nama" id="namaMode">Mode</div>
      <div class="nyawa" id="nyawaMode"></div>
    </div>
    <div class="sub" id="caraMode"></div>
    <div class="nomor" style="margin-top:12px">Soal <span id="noKini">1</span> dari <span id="noTotal">20</span></div>
    <div id="wadahSoal"></div>
    <div class="balasan" id="balasan"></div>
  </div>

  <div class="kartu" style="margin-top:14px">
    <div class="nomor">Peta soal mode ini</div>
    <div class="daftar-nomor" id="peta"></div>
  </div>
</div>

<div class="kendali" id="kendaliMode" style="display:none">
  <div class="bungkus-kecil">
    <button type="button" class="kedua" id="tombolMenu">← Menu mode</button>
    <button type="button" class="utama" id="tombolMaju" disabled>Lanjut →</button>
  </div>
</div>

<div class="kilat" id="kilat"></div>
<div class="buzz-teks" id="buzzTeks">✘ SALAH</div>

<div class="tutup-layar" id="tutupLayar">
  <div class="kotak">
    <h3 id="tutupJudul">Mode selesai</h3>
    <p id="tutupIsi"></p>
    <div class="tombol-aksi">
      <button type="button" id="tutupUlangi" style="display:none">🔁 Ulangi mode ini dari soal pertama</button>
      <button type="button" id="tutupKembali">Kembali ke menu mode</button>
    </div>
  </div>
</div>
@else
{{-- ======================================================== ALUR LAMA (utuh) --}}
<div class="bungkus">
  <div class="peringatan" id="peringatan" style="display:none">
    Jawabanmu tersimpan otomatis di server setiap kali diisi. Kalau koneksi terputus, muat ulang halaman ini — jawaban yang sudah tersimpan tidak hilang.
  </div>

  <div class="kartu">
    <div class="nomor">Soal <span id="noKini">1</span> dari {{ count($percobaan->soal) }}</div>
    <div id="wadahSoal"></div>
  </div>

  <div class="kartu" style="margin-top:14px">
    <div class="nomor">Peta soal</div>
    <div class="daftar-nomor" id="peta"></div>
  </div>
</div>

<form method="post" action="{{ route('evaluasi.selesai', $percobaan->kode) }}" id="formSelesai">
  @csrf
  <div id="kiriman"></div>
</form>

<div class="kendali">
  <div class="bungkus-kecil">
    <button type="button" class="kedua" id="tombolBalik">← Sebelumnya</button>
    <button type="button" class="utama" id="tombolMaju">Berikutnya →</button>
  </div>
</div>
@endif

<script>
(function () {
  const SOAL = @json($percobaan->soal);
  const KODE = @json($percobaan->kode);
  const SIMPAN = @json(route('evaluasi.simpan', $percobaan->kode));
  const TOKEN = @json(csrf_token());
  const AUDIO = @json(url('berkas'));
  const IS7 = @json((bool) $mode7);
  let jawaban = @json((object) ($percobaan->jawaban ?? []));
  let selesai = false;
  let cekBelum = null;   // diisi jalur lama: konfirmasi bila masih ada soal belum dijawab

  function el(tag, cls, isi) {
    const n = document.createElement(tag);
    if (cls) { n.className = cls; }
    if (isi !== undefined && isi !== null) { n.textContent = isi; }
    return n;
  }
  function kirimSelesai() {
    if (selesai) { return; }
    if (cekBelum && !cekBelum()) { return; }
    selesai = true;
    const wadahKirim = document.getElementById('kiriman');
    wadahKirim.innerHTML = '';
    Object.keys(jawaban).forEach(function (n) {
      const inp = document.createElement('input');
      inp.type = 'hidden';
      inp.name = 'jawaban[' + n + ']';
      inp.value = jawaban[n];
      wadahKirim.appendChild(inp);
    });
    document.getElementById('formSelesai').submit();
  }

  // ---------------------------------------------------------------- BUNYI (CDN + nada cadangan)
  // Bunyi diambil dari CDN publik (Mixkit) — nama berkasnya jelas:
  //   2870 = "Correct answer tone" · 954 = "Wrong long buzzer"
  // Kalau CDN tidak bisa diakses / formatnya tidak didukung peramban, otomatis dipakai nada
  // buatan peramban (Web Audio API) supaya siswa TETAP dapat bunyi walau internet sekolah lambat.
  const SFX = {
    benar: 'https://assets.mixkit.co/active_storage/sfx/2870/2870-preview.mp3',
    salah: 'https://assets.mixkit.co/active_storage/sfx/954/954-preview.mp3'
  };
  const bunyi = {};
  let audioCtx = null;

  function siapkanBunyi() {
    ['benar', 'salah'].forEach(function (k) {
      try {
        const a = new Audio(SFX[k]);
        a.preload = 'auto';
        a.volume = (k === 'salah') ? 0.85 : 0.7;
        a.addEventListener('error', function () { bunyi[k] = null; });
        bunyi[k] = a;
      } catch (e) { bunyi[k] = null; }
    });
  }

  function getAudioCtx() {
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) { return null; }
    try {
      audioCtx = audioCtx || new AC();
      if (audioCtx.state === 'suspended' && audioCtx.resume) { audioCtx.resume(); }
      return audioCtx;
    } catch (e) { return null; }
  }

  function nadaCadangan(k) {
    const ctx = getAudioCtx();
    if (!ctx) { return; }
    try {
      const t0 = ctx.currentTime;
      const nada = function (f, t, dur, tipe, vol, fAkhir) {
        const o = ctx.createOscillator(), g = ctx.createGain();
        o.type = tipe || 'sine';
        o.frequency.setValueAtTime(f, t0 + t);
        if (fAkhir) { o.frequency.exponentialRampToValueAtTime(fAkhir, t0 + t + dur); }
        g.gain.setValueAtTime(0.0001, t0 + t);
        g.gain.exponentialRampToValueAtTime(vol, t0 + t + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + t + dur);
        o.connect(g); g.connect(ctx.destination);
        o.start(t0 + t); o.stop(t0 + t + dur + 0.02);
      };
      if (k === 'benar') { nada(880, 0, 0.16, 'sine', 0.22); nada(1320, 0.1, 0.3, 'sine', 0.2); }
      else { nada(150, 0, 0.5, 'sawtooth', 0.16, 62); nada(153, 0, 0.5, 'sawtooth', 0.12, 64); }
    } catch (e) {}
  }

  /** Bunyi pendek saat tombol jawaban ditekan (langsung, tidak menunggu jaringan). */
  function ketuk() {
    const ctx = getAudioCtx();
    if (!ctx) { return; }
    try {
      const t0 = ctx.currentTime;
      const o = ctx.createOscillator(), g = ctx.createGain();
      o.type = 'triangle';
      o.frequency.setValueAtTime(660, t0);
      g.gain.setValueAtTime(0.11, t0);
      g.gain.exponentialRampToValueAtTime(0.001, t0 + 0.08);
      o.connect(g); g.connect(ctx.destination);
      o.start(t0); o.stop(t0 + 0.09);
    } catch (e) {}
  }

  function putarBunyi(k) {
    if (k === 'ketuk') { ketuk(); return; }
    const a = bunyi[k];
    if (!a) { nadaCadangan(k); return; }
    try {
      a.currentTime = 0;
      const p = a.play();
      if (p && p.catch) { p.catch(function () { nadaCadangan(k); }); }
    } catch (e) { nadaCadangan(k); }
  }

  let audioGagalDitampilkan = false;

  /** Catatan kecil di halaman bila audio soal tidak bisa diputar di perangkat siswa. */
  function catatAudioGagal() {
    if (audioGagalDitampilkan) { return; }
    audioGagalDitampilkan = true;
    const n = el('div', 'peringatan', '⚠️ Audio soal ini tidak bisa diputar di perangkatmu. Coba HP/browser lain, atau beri tahu gurumu. Soal lain tetap bisa dikerjakan dan dijawab.');
    n.id = 'audioGagal';
    n.style.marginTop = '10px';
    const wadahLapor = document.getElementById('wadahSoal');
    if (wadahLapor) { wadahLapor.appendChild(n); }
  }

  /** Putar audio soal lewat /berkas/<nilai kolom audio>. Gagal → siswa diberi catatan. */
  function putarAudio(nama) {
    if (!nama) { return null; }
    try {
      const a = new Audio(AUDIO + '/' + nama);
      a.addEventListener('error', function () { catatAudioGagal(); });
      const pe = a.play();
      if (pe && pe.catch) { pe.catch(function () { catatAudioGagal(); }); }
      return a;
    } catch (e) { catatAudioGagal(); return null; }
  }

  function pasangKelas(elemen, kelas, ms) {
    if (!elemen) { return; }
    elemen.classList.remove(kelas);
    elemen.classList.add(kelas);
    setTimeout(function () { elemen.classList.remove(kelas); }, ms);
  }

  /** Efek SALAH: kartu bergetar (buzz) + kilatan merah selayar + tulisan "✘ SALAH" besar.
   *  penuh=false (mis. satu pasangan jodoh keliru) → cukup getaran + bunyi. */
  function efekSalah(penuh) {
    pasangKelas(document.getElementById('kartuSoal'), 'buzz', 560);
    if (penuh === false) { return; }
    pasangKelas(document.getElementById('kilat'), 'nyala', 700);
    pasangKelas(document.getElementById('buzzTeks'), 'nyala', 780);
  }

  /** Efek BENAR: kartu berpendar hijau. */
  function efekBenar() {
    pasangKelas(document.getElementById('kartuSoal'), 'pendar', 620);
  }

  const JALUR_HATI = 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z';
  function svgHati(warna) {
    return '<svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path fill="' + warna + '" d="' + JALUR_HATI + '"/></svg>';
  }

  /** Nyawa = hati MERAH (SVG, jadi merahnya pasti walau emoji di HP tidak berwarna). */
  function nyawa(wadahNyawa, sisa) {
    wadahNyawa.innerHTML = '';
    for (let i = 0; i < NYAWA; i++) {
      const hidup = i < sisa;
      const s = el('span', 'hati' + (hidup ? '' : ' mati'));
      s.innerHTML = svgHati(hidup ? '#dc2626' : '#fecaca');
      wadahNyawa.appendChild(s);
    }
  }

  if (!IS7) {
    // ==========================================================================================
    //  ALUR LAMA — TIDAK DIUBAH
    // ==========================================================================================
    let kini = 0;
    let sisa = {{ (int) $sisaDetik }};

    const wadah = document.getElementById('wadahSoal');
    const jam = document.getElementById('jam');
    const bar = document.getElementById('bar');
    const peta = document.getElementById('peta');
    const noKini = document.getElementById('noKini');

    document.getElementById('peringatan').style.display = 'block';

    // Konfirmasi jalur lama: bila masih ada soal belum dijawab dan waktu belum habis.
    cekBelum = function () {
      const belum = SOAL.filter(function (s) { return !terjawab(s.nomor); }).length;
      if (belum > 0 && sisa > 0) {
        return confirm('Masih ada ' + belum + ' soal yang belum dijawab. Kirim sekarang?');
      }
      return true;
    };

    function simpanJawaban(nomor, nilai) {
      putarBunyi('ketuk');   // bunyi pendek saat menjawab (jalur lama hanya punya bunyi ketuk, tanpa benar/salah)
      jawaban[nomor] = nilai;
      fetch(SIMPAN, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json'},
        body: JSON.stringify({nomor: nomor, jawaban: nilai})
      }).catch(function () {});
      gambarPeta();
    }

    function terjawab(n) {
      return jawaban[n] !== undefined && String(jawaban[n]).trim() !== '';
    }

    function gambarPeta() {
      peta.innerHTML = '';
      SOAL.forEach(function (s, i) {
        const b = document.createElement('button');
        b.type = 'button';
        b.textContent = s.nomor;
        if (terjawab(s.nomor)) { b.className = 'terjawab'; }
        if (i === kini) { b.className += ' kini'; }
        b.addEventListener('click', function () { kini = i; gambar(); });
        peta.appendChild(b);
      });
      const total = SOAL.length;
      const sudah = SOAL.filter(function (s) { return terjawab(s.nomor); }).length;
      bar.style.width = (total ? (sudah / total * 100) : 0) + '%';
    }

    function tombolPilihan(teks, nilai, aktif) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'pilih' + (aktif ? ' aktif' : '');
      b.textContent = teks;
      b.addEventListener('click', function () { simpanJawaban(SOAL[kini].nomor, nilai); gambar(); });
      return b;
    }

    function gambar() {
      const s = SOAL[kini];
      noKini.textContent = kini + 1;
      wadah.innerHTML = '';

      const p = document.createElement('div');
      p.className = 'petunjuk';
      p.textContent = s.petunjuk || '';
      wadah.appendChild(p);

      // ---- soal mendengar: audio dulu
      if (s.mode === 'dengar' && s.audio) {
        const putar = document.createElement('button');
        putar.type = 'button';
        putar.className = 'audio';
        putar.textContent = '▶ Putar audio';
        putar.addEventListener('click', function () { putarAudio(s.audio); });
        wadah.appendChild(putar);
      }

      // ---- pertanyaan
      const t = document.createElement('div');
      t.className = 'tanya' + (s.tanya_arab ? ' arab' : '');
      t.textContent = s.tanya || '';
      wadah.appendChild(t);

      if (s.petunjuk_kalimat) {
        const sub = document.createElement('div');
        sub.className = 'sub';
        sub.textContent = s.petunjuk_kalimat;
        wadah.appendChild(sub);
      }

      const nilaiKini = jawaban[s.nomor] || '';

      // ---- pilihan ganda / mendengar
      if (s.pilihan) {
        const kotak = document.createElement('div');
        kotak.className = 'pilihan';
        if (s.pilihan_arab) { kotak.setAttribute('dir', 'rtl'); }
        s.pilihan.forEach(function (pilih) {
          kotak.appendChild(tombolPilihan(pilih, pilih, String(nilaiKini) === String(pilih)));
        });
        wadah.appendChild(kotak);
        gambarPeta();
        return;
      }

      // ---- susun huruf
      if (s.huruf) {
        const susun = document.createElement('div');
        susun.className = 'jawab' + (nilaiKini ? '' : ' kosong');
        susun.textContent = nilaiKini || 'Ketuk huruf di bawah…';
        wadah.appendChild(susun);

        const kotak = document.createElement('div');
        kotak.className = 'huruf';
        s.huruf.forEach(function (h, idx) {
          const b = document.createElement('button');
          b.type = 'button';
          b.textContent = h;
          b.disabled = nilaiKini.toLowerCase().split('').includes(h) && nilaiKini.toLowerCase().split('').filter(function (x) { return x === h; }).length > s.huruf.slice(0, idx).filter(function (x) { return x === h; }).length;
          b.addEventListener('click', function () {
            simpanJawaban(s.nomor, (String(jawaban[s.nomor] || '') + h));
            gambar();
          });
          kotak.appendChild(b);
        });
        wadah.appendChild(kotak);

        const balik = document.createElement('button');
        balik.type = 'button';
        balik.className = 'balik';
        balik.textContent = '⌫ Hapus satu huruf';
        balik.addEventListener('click', function () {
          const a = String(jawaban[s.nomor] || '');
          simpanJawaban(s.nomor, a.slice(0, -1));
          gambar();
        });
        wadah.appendChild(balik);
        gambarPeta();
        return;
      }

      // ---- susun kata
      if (s.kata) {
        const susun = document.createElement('div');
        susun.className = 'jawab' + (nilaiKini ? '' : ' kosong');
        if (s.kata_arab) { susun.setAttribute('dir', 'rtl'); susun.style.fontSize = '20px'; }
        susun.textContent = nilaiKini || (s.kata_arab ? 'Ketuk kata Arab sesuai urutan…' : 'Ketuk kata sesuai urutan…');
        wadah.appendChild(susun);

        const kotak = document.createElement('div');
        kotak.className = 'kata';
        if (s.kata_arab) { kotak.setAttribute('dir', 'rtl'); }
        s.kata.forEach(function (k, idx) {
          const b = document.createElement('button');
          b.type = 'button';
          b.textContent = k;
          b.addEventListener('click', function () {
            const a = String(jawaban[s.nomor] || '').trim();
            simpanJawaban(s.nomor, a ? a + ' ' + k : k);
            gambar();
          });
          kotak.appendChild(b);
        });
        wadah.appendChild(kotak);

        const balik = document.createElement('button');
        balik.type = 'button';
        balik.className = 'balik';
        balik.textContent = '⌫ Hapus kata terakhir';
        balik.addEventListener('click', function () {
          const bagian = String(jawaban[s.nomor] || '').trim().split(/\s+/);
          bagian.pop();
          simpanJawaban(s.nomor, bagian.join(' '));
          gambar();
        });
        wadah.appendChild(balik);
        gambarPeta();
      }
    }

    document.getElementById('tombolMaju').addEventListener('click', function () {
      if (kini < SOAL.length - 1) { kini++; gambar(); } else { kirimSelesai(); }
    });
    document.getElementById('tombolBalik').addEventListener('click', function () {
      if (kini > 0) { kini--; gambar(); }
    });

    function detik() {
      const m = Math.floor(sisa / 60), s = sisa % 60;
      jam.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
      if (sisa <= 120) { jam.className = 'jam waspada'; }
    }

    const pengatur = setInterval(function () {
      sisa--;
      detik();
      if (sisa <= 0) { clearInterval(pengatur); kirimSelesai(); }
    }, 1000);
    detik();

    const tombolMaju = document.getElementById('tombolMaju');
    tombolMaju.addEventListener('click', function () {
      if (kini === SOAL.length - 1) { tombolMaju.textContent = 'Selesai & kirim ✓'; tombolMaju.className = 'utama selesai'; }
    });

    gambar();
    return;
  }

  // ============================================================================================
  //  ALUR 7 MODE — menu 7 sub-sesi, 3 nyawa, umpan balik langsung, tanpa timer
  // ============================================================================================
  const PETA7 = @json($peta7);
  const RINGKAS = @json($ringkas);
  const ARTI = @json($arti ?? []);          // nomor soal → terjemahan Indonesia (mode Lengkapi Kalimat)
  const INFO = @json($infoMode);
  const NYAWA = {{ (int) \App\Http\Controllers\BeeEvaluasiController::NYAWA }};
  const URUT = @json(\App\Http\Controllers\BeeEvaluasiController::MODE7);

  const bar = document.getElementById('bar');
  const menuMode = document.getElementById('menuMode');
  const ringkasSesi = document.getElementById('ringkasSesi');
  const layarMenu = document.getElementById('layarMenu');
  const layarMode = document.getElementById('layarMode');
  const kendaliMode = document.getElementById('kendaliMode');
  const tutupLayar = document.getElementById('tutupLayar');
  const wadah = document.getElementById('wadahSoal');
  const balasan = document.getElementById('balasan');
  const peta = document.getElementById('peta');
  const noKini = document.getElementById('noKini');
  const noTotal = document.getElementById('noTotal');
  const namaModeEl = document.getElementById('namaMode');
  const caraModeEl = document.getElementById('caraMode');
  const nyawaModeEl = document.getElementById('nyawaMode');
  const tombolMaju = document.getElementById('tombolMaju');
  const tombolMenu = document.getElementById('tombolMenu');

  // keadaan tiap mode — salinan dari server, diperbarui tiap jawaban
  const keadaan = {};
  const daftar = {};
  URUT.forEach(function (m) { daftar[m] = []; });
  Object.keys(PETA7).forEach(function (n) {
    const m = PETA7[n];
    if (!daftar[m]) { daftar[m] = []; }
    daftar[m].push(parseInt(n, 10));
  });
  URUT.forEach(function (m) {
    daftar[m] = (daftar[m] || []).sort(function (a, b) { return a - b; });
    const r = (RINGKAS && RINGKAS.modes && RINGKAS.modes[m]) ? RINGKAS.modes[m] : {};
    keadaan[m] = {
      dijawab: r.dijawab || 0,
      benar: r.benar || 0,
      salah: r.salah || 0,
      nyawa: (r.nyawa === undefined ? NYAWA : r.nyawa),
      nilai: r.nilai || 0,
      tuntas: !!r.tuntas,
      total: r.total || 0,
      tanpaPoin: r.tanpa_poin || 0,
      dinilai: !!r.dinilai,
      sudah: {},
      benarNomor: r.benar_nomor || {},
      babak: r.babak || 1,
      nilaiBabak: r.nilai_babak || [],
      bolehUlang: !!r.boleh_ulang,
      babakHabis: !!r.babak_habis,
      dijawabSemua: r.dijawab_semua || 0
    };
    (r.terjawab || {}) && Object.keys(r.terjawab || {}).forEach(function (n) { keadaan[m].sudah[n] = true; });
  });

  // Jumlah sesi per mode (mis. pg_kosakata = 2 sesi × 10 soal) — dibaca dari penanda 'sesi' di butir.
  const SESI_MODE = {};
  SOAL.forEach(function (s) {
    if (s.sesi) {
      const m = PETA7[s.nomor];
      SESI_MODE[m] = Math.max(SESI_MODE[m] || 0, s.sesi);
    }
  });

  let modeAktif = null;
  let nomorAktif = null;
  let sudahKirim = false;

  function modeTerpakai() {
    return URUT.filter(function (m) { return keadaan[m].dijawab > 0; });
  }
  function rataSementara() {
    const pakai = modeTerpakai();
    if (!pakai.length) { return 0; }
    let t = 0;
    pakai.forEach(function (m) { t += keadaan[m].nilai; });
    return Math.round(t / pakai.length);
  }
  function totalDijawab() {
    let n = 0;
    URUT.forEach(function (m) { n += keadaan[m].dijawab; });
    return n;
  }
  function perBar() {
    const total = Object.keys(PETA7).length || 1;
    bar.style.width = (totalDijawab() / total * 100) + '%';
  }

  function gambarMenu() {
    menuMode.innerHTML = '';
    URUT.forEach(function (m) {
      const k = keadaan[m];
      const info = INFO[m] || {label: m, ikon: '•', cara: ''};
      const kartu = el('button', 'kartu-mode', null);
      kartu.type = 'button';
      if (k.tuntas) { kartu.className += ' tuntas'; } else if (k.nyawa <= 0 && k.bolehUlang) { kartu.className += ' habis bisa-ulang'; } else if (k.dijawab > 0) { kartu.className += ' jalan'; }

      const atas = el('div', 'nm', null);
      atas.appendChild(el('span', null, info.ikon + ' ' + info.label));
      // Pil status hanya muncul bila ada kabar berguna (belum dikerjakan sudah jelas dari "0/20 soal").
      if (k.tuntas || k.bolehUlang || k.dijawab > 0) {
        const pil = el('span', 'pil ' + (k.tuntas ? 'tuntas' : ((k.nyawa <= 0 && k.bolehUlang) ? 'habis' : 'jalan')));
        pil.textContent = k.tuntas ? 'Tuntas' : ((k.nyawa <= 0 && k.bolehUlang) ? 'bisa diulang' : 'sedang dikerjakan');
        atas.appendChild(pil);
      }
      kartu.appendChild(atas);

      const cr = el('div', 'cr', null);
      cr.appendChild(document.createTextNode(info.cara));
      if (k.babak > 1) {
        cr.appendChild(el('span', 'babak-pil', 'babak ' + k.babak + (k.nilaiBabak.length ? ' · babak: ' + k.nilaiBabak.join(' · ') : '')));
      }
      if (m === 'jodoh') {
        cr.appendChild(el('span', 'sesi-pil', '2 sesi × 2 fase × 10 pasang'));
      } else if (SESI_MODE[m] > 1) {
        cr.appendChild(el('span', 'sesi-pil', SESI_MODE[m] + ' sesi × ' + Math.round(daftar[m].length / SESI_MODE[m]) + ' soal'));
      }
      kartu.appendChild(cr);

      const bawah = el('div', 'baris2', null);
      const kiri = el('div', null, null);
      const nw = el('div', 'nyawa', null);
      nyawa(nw, k.nyawa);
      kiri.appendChild(nw);
      bawah.appendChild(kiri);
      const kanan = el('div', null, null);
      if (k.dinilai) {
        kanan.appendChild(el('span', 'angka-mode', k.nilai));
        kanan.appendChild(el('span', null, ' '));
      }
      const satuan = (m === 'jodoh') ? 'pasang' : 'soal';
      const penyebut = k.total || daftar[m].length;
      kanan.appendChild(el('span', 'sub', k.dijawab + '/' + penyebut + ' ' + satuan));
      bawah.appendChild(kanan);
      kartu.appendChild(bawah);

      kartu.addEventListener('click', function () { masukMode(m); });
      menuMode.appendChild(kartu);
    });

    ringkasSesi.innerHTML = '';
    const a = el('div', null, null);
    a.appendChild(el('b', null, totalDijawab()));
    a.appendChild(document.createTextNode('soal dijawab'));
    const b = el('div', null, null);
    b.appendChild(el('b', null, modeTerpakai().length + '/' + Object.keys(daftar).length));
    b.appendChild(document.createTextNode('mode dikerjakan'));
    const c = el('div', null, null);
    c.appendChild(el('b', null, rataSementara()));
    c.appendChild(document.createTextNode('rata-rata sementara'));
    ringkasSesi.appendChild(a);
    ringkasSesi.appendChild(b);
    ringkasSesi.appendChild(c);
    perBar();
  }

  function bukaMenu() {
    modeAktif = null;
    layarMode.style.display = 'none';
    kendaliMode.style.display = 'none';
    layarMenu.style.display = 'block';
    tutupLayar.className = 'tutup-layar';
    gambarMenu();
    window.scrollTo(0, 0);
  }

  function nomorBerikut(m, dari) {
    const list = daftar[m];
    const mulai = (dari === undefined) ? 0 : list.indexOf(dari) + 1;
    for (let i = (mulai < 0 ? 0 : mulai); i < list.length; i++) {
      if (!keadaan[m].sudah[list[i]]) { return list[i]; }
    }
    for (let i = 0; i < list.length; i++) {
      if (!keadaan[m].sudah[list[i]]) { return list[i]; }
    }
    return null;
  }

  function masukMode(m) {
    modeAktif = m;
    const k = keadaan[m];
    if (k.nyawa <= 0) {
      bukaTutup(
        'Nyawa mode ini habis',
        'Mode ' + (INFO[m] ? INFO[m].label : m) + ' berhenti setelah 3 jawaban salah pada babak ' + k.babak
          + (k.nilaiBabak.length ? ' (nilai babak: ' + k.nilaiBabak.join(' · ') + ')' : '')
          + '. Kamu bisa mengulang mode ini dari soal pertama — nilai mode nanti diambil RATA-RATA dari semua babak. '
          + 'Mode lain juga tetap bisa dikerjakan.',
        m
      );
      return;
    }
    const n = nomorBerikut(m, null);
    if (n === null) {
      layarMenu.style.display = 'none';
      layarMode.style.display = 'block';
      kendaliMode.style.display = 'flex';
      bukaTutup('Mode ini tuntas', 'Semua ' + daftar[m].length + ' soal mode ' + (INFO[m] ? INFO[m].label : m) + ' sudah kamu jawab. Nilai mode ini ' + k.nilai + '. Lanjut ke mode lain, atau selesaikan sesinya.');
      return;
    }
    nomorAktif = n;
    renderSoal();
  }

  let modeUlang = null;   // mode yang bisa diulang dari soal pertama saat layar tutup dibuka

  function bukaTutup(judul, isi, modeBisaDiulang) {
    document.getElementById('tutupJudul').textContent = judul;
    document.getElementById('tutupIsi').textContent = isi;
    modeUlang = modeBisaDiulang || null;
    const tb = document.getElementById('tutupUlangi');
    if (tb) { tb.style.display = modeUlang ? 'inline-block' : 'none'; }
    tutupLayar.className = 'tutup-layar buka';
  }

  /** Mulai BABAK BARU mode ini dari soal pertama (babak lama diarsipkan server; nilai mode = rata-rata babak). */
  function mulaiBabakBaru(m) {
    const mode = m || modeUlang || modeAktif;
    if (!mode) { return; }
    tampilBalasan('info', 'Menyiapkan babak baru…');
    fetch(SIMPAN, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json'},
      body: JSON.stringify({aksi: 'babak', mode: mode, alasan: 'nyawa'})
    }).then(function (r) { return r.json(); }).then(function (r) {
      if (!r.ok) {
        tampilBalasan('info', r.pesan || 'Tidak bisa memulai babak baru.');
        return;
      }
      const k = keadaan[mode];
      k.nyawa = r.nyawa;
      k.babak = r.babak;
      k.nilaiBabak = r.nilai_babak || [];
      k.nilai = r.nilai_mode;
      k.dijawab = r.dijawab || 0;
      k.dijawabSemua = 0;
      k.tuntas = !!r.tuntas;
      k.bolehUlang = false;
      k.babakHabis = false;
      k.salah = 0;
      k.benar = 0;
      k.sudah = {};
      k.benarNomor = {};
      (daftar[mode] || []).forEach(function (no) { delete jawaban[no]; });
      modeAktif = mode;
      modeUlang = null;
      nomorAktif = daftar[mode][0];
      tutupLayar.className = 'tutup-layar';
      layarMenu.style.display = 'none';
      layarMode.style.display = 'block';
      kendaliMode.style.display = 'flex';
      tampilBalasan('info', r.pesan || ('Babak ' + r.babak + ' dimulai.'));
      renderSoal();
    }).catch(function () {
      tampilBalasan('info', 'Gagal menghubungi server. Periksa koneksi lalu coba lagi.');
    });
  }

  function soalAktif() {
    for (let i = 0; i < SOAL.length; i++) {
      if (SOAL[i].nomor === nomorAktif) { return SOAL[i]; }
    }
    return null;
  }

  function gambarPetaMode() {
    peta.innerHTML = '';
    const list = daftar[modeAktif] || [];
    noTotal.textContent = list.length;
    list.forEach(function (n, i) {
      const b = el('button', null, n);
      b.type = 'button';
      if (keadaan[modeAktif].sudah[n]) {
        b.className = keadaan[modeAktif].benarNomor[n] ? 'terjawab' : 'salah';
      }
      if (n === nomorAktif) { b.className += ' kini'; }
      b.addEventListener('click', function () {
        if (!keadaan[modeAktif].sudah[n]) { nomorAktif = n; renderSoal(); }
      });
      peta.appendChild(b);
    });
    const k = keadaan[modeAktif];
    const sudah = list.filter(function (n) { return k.sudah[n]; }).length;
    bar.style.width = (list.length ? (sudah / list.length * 100) : 0) + '%';
    noKini.textContent = (list.indexOf(nomorAktif) + 1) + '';
  }

  function bersihBalasan() {
    balasan.className = 'balasan';
    balasan.textContent = '';
  }

  function tampilBalasan(jenis, teks) {
    balasan.className = 'balasan ' + jenis;
    balasan.textContent = teks;
  }

  // ---------------------------------------------------------------- pengiriman jawaban
  function kirimJawaban(nomor, nilai, lanjutOtomatis) {
    if (sudahKirim) { return; }
    sudahKirim = true;
    tombolMaju.disabled = true;
    putarBunyi('ketuk');   // bunyi pendek saat tombol jawaban ditekan
    fetch(SIMPAN, {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json'},
      body: JSON.stringify({nomor: nomor, jawaban: nilai})
    }).then(function (r) { return r.json(); }).then(function (r) {
      sudahKirim = false;
      if (!r.ok) {
        tampilBalasan('info', r.pesan || 'Jawaban tidak diterima.');
        return;
      }
      jawaban[nomor] = nilai;
      const k = keadaan[r.mode];
      k.dijawab++;
      if (r.benar) { k.benar++; k.benarNomor[nomor] = true; } else { k.salah++; }
      k.nyawa = r.nyawa;
      k.nilai = r.nilai_mode;
      k.tuntas = r.tuntas;
      k.dinilai = true;   // mode ini sudah ada nilainya (dipakai kartu menu)
      k.sudah[nomor] = true;
      if (r.babak) { k.babak = r.babak; }
      if (r.nilai_babak) { k.nilaiBabak = r.nilai_babak; }
      if (r.boleh_ulang !== undefined) { k.bolehUlang = !!r.boleh_ulang; }
      k.babakHabis = (k.nyawa <= 0) && k.bolehUlang;

      if (r.benar) {
        putarBunyi('benar');
        efekBenar();
        tampilBalasan('benar', '✔ Benar!');
      } else if (r.kunci) {
        putarBunyi('salah');
        efekSalah(true);
        tampilBalasan('salah', '✘ Salah. Jawaban benarnya: ' + r.kunci);
      } else {
        putarBunyi('salah');
        efekSalah(true);
        tampilBalasan('salah', '✘ Salah.');
      }
      nyawa(nyawaModeEl, k.nyawa);
      gambarPetaMode();

      if (r.berhenti) {
        tombolMaju.disabled = true;
        setTimeout(function () {
          bukaTutup(
            'Nyawa habis — babak ' + (k.babak || 1) + ' selesai',
            'Mode ' + (INFO[r.mode] ? INFO[r.mode].label : r.mode) + ' berhenti setelah 3 jawaban salah.'
              + (k.nilaiBabak.length ? ' Nilai babak: ' + k.nilaiBabak.join(' · ') + '.' : '')
              + ' Nilai mode ini sementara ' + k.nilai + ' (rata-rata babak). '
              + 'Tekan tombol di bawah untuk mengulang mode ini DARI SOAL PERTAMA — nilai mode nanti diambil rata-rata semua babak.',
            r.mode
          );
        }, 900);
        return;
      }
      if (r.dijawab >= r.total) {
        tombolMaju.disabled = true;
        const pesanTuntas = function () {
          bukaTutup('Mode ini tuntas', 'Semua ' + r.total + ' soal mode ini sudah dijawab. Nilai mode ini ' + k.nilai + (k.babak > 1 ? ' (rata-rata ' + k.babak + ' babak)' : '') + '. Mode lain bisa dilanjutkan, atau tekan Selesai bila sudah cukup.');
        };
        if (r.mode === 'pg_kosakata') {
          // soal terakhir: dengarkan dulu audio jawaban, baru munculkan "tuntas"
          putarUmpanBalik((butirNomor(nomor) || {}).audio, pesanTuntas);
        } else {
          setTimeout(pesanTuntas, 900);
        }
        return;
      }
      tombolMaju.disabled = false;
      tombolMaju.className = 'utama';
      tombolMaju.textContent = 'Lanjut →';
      if (lanjutOtomatis) {
        if (r.mode === 'pg_kosakata' && r.benar) {
          // PG Kosakata & jawaban BENAR: bunyikan audio kata bahasa tujuan dulu, baru pindah soal.
          const nomorIni = nomor;
          putarUmpanBalik((butirNomor(nomor) || {}).audio, function () {
            if (nomorAktif === nomorIni && !tombolMaju.disabled) { maju(); }
          });
        } else {
          setTimeout(function () { if (!tombolMaju.disabled) { maju(); } }, 1100);
        }
      }
    }).catch(function () {
      sudahKirim = false;
      tombolMaju.disabled = false;
      tampilBalasan('info', 'Gagal mengirim ke server. Periksa koneksi lalu coba lagi.');
    });
  }

  function maju() {
    const n = nomorBerikut(modeAktif, nomorAktif);
    if (n === null) {
      bukaTutup('Mode ini selesai', 'Semua soal mode ini sudah dijawab. Nilai mode ini ' + keadaan[modeAktif].nilai + '.');
      return;
    }
    nomorAktif = n;
    renderSoal();
  }

  // ---------------------------------------------------------------- penggambar soal
  function potongKalimat(s, jawabSekarang) {
    const bagian = String(s.tanya || '').split(' ');
    const kotak = el('div', 'tanya' + (s.tanya_arab ? ' arab' : ''), null);
    bagian.forEach(function (b, i) {
      if (i > 0) { kotak.appendChild(document.createTextNode(' ')); }
      if (b === '____' || (s.lubang && i + 1 === s.lubang)) {
        const l = el('span', 'lubang' + (jawabSekarang ? ' isi' : ''), jawabSekarang || '____');
        l.setAttribute('data-lubang', '1');
        kotak.appendChild(l);
      } else {
        kotak.appendChild(document.createTextNode(b));
      }
    });
    return kotak;
  }

  /** Kalimat untuk mode SERET: bagian kosong jadi kotak tujuan (.lubang-drop). */
  function kalimatDrag(s, terisi) {
    const bagian = String(s.tanya || '').split(' ');
    const baris = el('div', 'kalimat-kotak' + (s.tanya_arab ? ' arab' : ''), null);
    let zona = null;
    bagian.forEach(function (b, i) {
      if (i > 0) { baris.appendChild(document.createTextNode(' ')); }
      const kosong = (b === '____') || (s.lubang && i + 1 === s.lubang);
      if (kosong) {
        zona = el('span', 'lubang-drop' + (terisi ? ' isi' : ''), terisi || 'seret balok ke sini');
        baris.appendChild(zona);
      } else {
        baris.appendChild(document.createTextNode(b));
      }
    });
    return { baris: baris, zona: zona };
  }

  /** Butir soal berdasarkan nomornya. */
  function butirNomor(n) {
    for (let i = 0; i < SOAL.length; i++) {
      if (SOAL[i].nomor === n) { return SOAL[i]; }
    }
    return null;
  }

  /**
   * PG Kosakata: setelah jawaban BENAR, perdengarkan audio kata bahasa tujuannya DULU,
   * baru lanjut ke soal berikutnya (permintaan Fahri). Alur tidak boleh macet:
   * tanpa audio / audio ditolak peramban → jeda pendek; selalu ada batas maksimum 5 detik.
   */
  function putarUmpanBalik(nama, onSelesai) {
    let sudah = false;
    const selesai = function () { if (sudah) { return; } sudah = true; onSelesai(); };
    const berkas = String(nama || '');
    if (berkas === '') { setTimeout(selesai, 400); return; }

    let a = null;
    try { a = new Audio(AUDIO + '/' + berkas); } catch (e) { setTimeout(selesai, 400); return; }
    if (a.addEventListener) {
      a.addEventListener('ended', selesai);
      a.addEventListener('error', function () { setTimeout(selesai, 350); });
    }
    try {
      const p = a.play();
      if (p && p.catch) { p.catch(function () { setTimeout(selesai, 350); }); }
    } catch (e) { setTimeout(selesai, 350); }
    setTimeout(selesai, 5000);   // pengaman: jangan pernah menunggu lebih lama dari ini
  }

  function tombolAudio(s, teks) {
    if (!s.audio) { return null; }
    const b = el('button', 'audio', teks || '▶ Putar audio');
    b.type = 'button';
    b.addEventListener('click', function () { putarAudio(s.audio); });
    return b;
  }

  function renderSoal() {
    const s = soalAktif();
    if (!s) { bukaMenu(); return; }
    bersihBalasan();
    tombolMaju.disabled = true;
    tombolMaju.className = 'utama';
    tombolMaju.textContent = 'Lanjut →';

    const k = keadaan[modeAktif];
    const info = INFO[modeAktif] || {label: modeAktif};
    namaModeEl.textContent = (info.ikon || '') + ' ' + (info.label || modeAktif);
    const kk2 = keadaan[modeAktif] || {};
    if (s.mode === 'pg_rumpang') {
      // "Lengkapi Kalimat" tampil bersih: kepala halaman cukup nama mode (+ penanda babak bila sudah diulang).
      // Nama mode sudah ada di kepala halaman, jadi penjelasan panjang & jenis soal tidak perlu diulang.
      caraModeEl.textContent = '';
      if (kk2.babak > 1) {
        caraModeEl.appendChild(el('span', 'babak-pil', 'babak ' + kk2.babak));
      }
    } else if (s.mode === 'jodoh' && s.versi === 2) {
      // Jodohkan: kepala halaman cukup penanda sesi/fase (aturan main ada di kartu soal)
      caraModeEl.textContent = '';
      caraModeEl.appendChild(el('span', 'sesi-pil', 'Sesi ' + (s.sesi || 1) + ' dari 2 · Fase ' + (s.fase || 1) + ' dari 2'));
      if (kk2.babak > 1) {
        caraModeEl.appendChild(el('span', 'babak-pil', 'babak ' + kk2.babak));
      }
    } else if (s.mode === 'pg_kosakata') {
      // 2 sesi × 10 soal: sebutkan sesi yang sedang dikerjakan
      caraModeEl.textContent = '';
      caraModeEl.appendChild(el('span', 'sesi-pil', 'Sesi ' + (s.sesi || 1) + ' dari 2'));
      if (kk2.babak > 1) {
        caraModeEl.appendChild(el('span', 'babak-pil', 'babak ' + kk2.babak));
      }
    } else {
      caraModeEl.textContent = (info.cara || '')
        + ((kk2.babak > 1) ? ' · Babak ' + kk2.babak + ' — nilai mode = rata-rata babak' + (kk2.nilaiBabak.length ? ' (' + kk2.nilaiBabak.join(' · ') + ')' : ': ' + kk2.nilai) : '');
    }
    nyawa(nyawaModeEl, k.nyawa);
    layarMenu.style.display = 'none';
    layarMode.style.display = 'block';
    kendaliMode.style.display = 'flex';

    layarMode.classList.add('mode-' + s.mode);
    wadah.innerHTML = '';
    // Baris "mode · jenis" dibuang (nama mode sudah di kepala halaman). Penjelasan panjang juga
    // dibuang untuk mode Lengkapi Kalimat — bentuk soalnya sudah jelas dari kalimatnya sendiri.
    if (s.mode !== 'pg_rumpang') {
      wadah.appendChild(el('div', 'petunjuk', s.petunjuk || ''));
    }
    gambarPetaMode();

    const sudahDijawab = !!k.sudah[s.nomor];
    const jwbLama = jawaban[s.nomor] || '';

    if (sudahDijawab) {
      tampilBalasan('info', 'Soal ini sudah kamu jawab: ' + jwbLama + '. Tiap soal hanya boleh dijawab sekali — pilih nomor lain di peta soal.');
    }

    // ---------------- pilihan: Lengkapi Kalimat · PG Kosakata · Simak Kata · pg_arti
    if (s.mode === 'pg_rumpang' || s.mode === 'pg_arti' || s.mode === 'pg_kosakata' || s.mode === 'dengar_kata') {
      if (s.mode === 'dengar_kata') {
        // AUDIO tanpa tulisan kata: yang tampil hanya tombol putar + pilihan kata tertulis
        wadah.appendChild(el('div', 'tanya audio-saja', '🔊 Dengarkan audionya, lalu ketuk kata yang kamu dengar'));
        const kotakAudio = el('div', 'audio-kotak', null);
        kotakAudio.appendChild(tombolAudio(s, '▶ Putar audio kata'));
        wadah.appendChild(kotakAudio);
      } else {
        const tanya = potongKalimat(s, jwbLama);
        if (s.tanya_satuan) { tanya.className += ' satuan'; }
        if (!s.pilihan && s.jenis === 'pilihan') { tanya.setAttribute('dir', s.tanya_arab ? 'rtl' : ''); }
        wadah.appendChild(tanya);
        // terjemahan bahasa Indonesia DI BAWAH kalimat (khusus mode Lengkapi Kalimat)
        if (s.mode === 'pg_rumpang' && ARTI[s.nomor]) {
          const kotakArti = el('div', 'arti-kalimat', null);
          kotakArti.appendChild(el('span', 'arti-label', 'Arti'));
          kotakArti.appendChild(el('span', null, ARTI[s.nomor]));
          wadah.appendChild(kotakArti);
        }
      }
      const kotak = el('div', 'pilihan', null);
      if (s.pilihan_arab) { kotak.setAttribute('dir', 'rtl'); }
      (s.pilihan || []).forEach(function (p) {
        const b = el('button', 'pilih', p);
        b.type = 'button';
        if (sudahDijawab) {
          b.disabled = true;
          if (String(jwbLama) === String(p)) { b.className += k.benarNomor[s.nomor] ? ' benar' : ' salah'; }
        } else {
          b.addEventListener('click', function () {
            Array.prototype.forEach.call(kotak.children, function (x) { x.disabled = true; });
            b.className += ' aktif';
            kirimJawaban(s.nomor, p, true);
          });
        }
        kotak.appendChild(b);
      });
      wadah.appendChild(kotak);
      return;
    }

    // ---------------- drag_rumpang: kalimat berlubang + tray balok (ketuk ATAU seret)
    if (s.mode === 'drag_rumpang') {
      const kk = kalimatDrag(s, jwbLama);
      const kotakKalimat = el('div', 'blok-kotak solid', null);
      kotakKalimat.appendChild(el('div', 'label-kotak', 'Lengkapi kalimat — satu kata hilang'));
      kotakKalimat.appendChild(kk.baris);
      wadah.appendChild(kotakKalimat);

      const kotakBalok = el('div', 'blok-kotak', null);
      kotakBalok.appendChild(el('div', 'label-kotak', 'Balok kata — ketuk baloknya, atau seret ke lubang'));
      const tray = el('div', 'balok-tray' + (s.pilihan_arab ? ' arab' : ''), null);
      kotakBalok.appendChild(tray);
      const aksi = el('div', 'drag-aksi', null);
      const lepas = el('button', 'pudar', '↺ Lepas balok');
      lepas.type = 'button';
      const periksa = el('button', 'utama', '✔ Periksa jawaban');
      periksa.type = 'button';
      aksi.appendChild(lepas);
      aksi.appendChild(periksa);
      kotakBalok.appendChild(aksi);
      wadah.appendChild(kotakBalok);

      let pilih = null;

      function gambarBalok() {
        Array.prototype.forEach.call(tray.children, function (x) {
          if (pilih !== null && x.getAttribute('data-kata') === pilih) { x.className = 'terpakai'; } else { x.className = ''; }
        });
        if (kk.zona) {
          if (pilih === null) { kk.zona.className = 'lubang-drop'; kk.zona.textContent = 'seret balok ke sini'; }
          else { kk.zona.className = 'lubang-drop isi'; kk.zona.textContent = pilih; }
        }
        lepas.disabled = (pilih === null) || sudahDijawab;
        periksa.disabled = (pilih === null) || sudahDijawab;
      }

      function pakaiBalok(kata) {
        if (sudahDijawab) { return; }
        putarBunyi('ketuk');
        pilih = kata;
        gambarBalok();
      }

      (s.balok || s.pilihan || []).forEach(function (kata, idx) {
        const b = el('button', null, kata);
        b.type = 'button';
        b.setAttribute('data-kata', kata);
        b.setAttribute('data-idx', idx);
        b.draggable = true;
        b.addEventListener('dragstart', function (e) { if (e.dataTransfer) { e.dataTransfer.setData('text/plain', kata); } });
        b.addEventListener('click', function () { pakaiBalok(kata); });
        tray.appendChild(b);
      });

      if (kk.zona) {
        kk.zona.addEventListener('dragover', function (e) { if (e.preventDefault) { e.preventDefault(); } kk.zona.className = 'lubang-drop sasaran'; });
        kk.zona.addEventListener('dragleave', function () { gambarBalok(); });
        kk.zona.addEventListener('drop', function (e) {
          if (e.preventDefault) { e.preventDefault(); }
          const kata = (e.dataTransfer && e.dataTransfer.getData) ? e.dataTransfer.getData('text/plain') : '';
          if (kata) { pakaiBalok(kata); } else { gambarBalok(); }
        });
      }

      lepas.addEventListener('click', function () {
        if (sudahDijawab) { return; }
        pilih = null;
        gambarBalok();
      });

      if (sudahDijawab) {
        pilih = jwbLama || null;
        Array.prototype.forEach.call(tray.children, function (x) { x.disabled = true; });
        gambarBalok();
        return;
      }

      periksa.addEventListener('click', function () {
        if (pilih === null) { return; }
        periksa.disabled = true;
        lepas.disabled = true;
        Array.prototype.forEach.call(tray.children, function (x) { x.disabled = true; });
        kirimJawaban(s.nomor, pilih, true);
      });

      gambarBalok();
      return;
    }

    // ---------------- susun_kata
    if (s.mode === 'susun_kata') {
      const jawabKotak = el('div', 'jawab' + (jwbLama ? '' : ' kosong'), null);
      if (s.kata_arab) { jawabKotak.setAttribute('dir', 'rtl'); }
      wadah.appendChild(jawabKotak);
      const tray = el('div', 'kata', null);
      if (s.kata_arab) { tray.setAttribute('dir', 'rtl'); }
      wadah.appendChild(tray);
      const aksi = el('div', 'tombol-aksi', null);
      wadah.appendChild(aksi);
      const tersusun = [];

      function gambarSusun() {
        jawabKotak.innerHTML = '';
        if (!tersusun.length) {
          jawabKotak.className = 'jawab kosong';
          jawabKotak.textContent = s.kata_arab ? 'Ketuk kata Arab sesuai urutan…' : 'Ketuk kata sesuai urutan…';
        } else {
          jawabKotak.className = 'jawab';
          tersusun.forEach(function (t) {
            const b = el('button', null, t.kata);
            b.type = 'button';
            b.disabled = sudahDijawab;
            b.addEventListener('click', function () {
              if (sudahDijawab) { return; }
              const idx = tersusun.indexOf(t);
              if (idx > -1) { tersusun.splice(idx, 1); }
              gambarSusun();
            });
            jawabKotak.appendChild(b);
          });
        }
        periksa.disabled = sudahDijawab || tersusun.length !== (s.jumlah_kata || 0);
      }

      (s.kata || []).forEach(function (kata, idx) {
        const b = el('button', null, kata);
        b.type = 'button';
        b.setAttribute('data-idx', idx);
        b.addEventListener('click', function () {
          if (sudahDijawab) { return; }
          const pakai = tersusun.filter(function (t) { return t.idx === idx; }).length;
          if (pakai) { return; }
          tersusun.push({idx: idx, kata: kata});
          b.classList.add('pakai');
          gambarSusun();
        });
        tray.appendChild(b);
      });

      const hapus = el('button', 'pudar', '⌫ Hapus kata terakhir');
      hapus.type = 'button';
      hapus.addEventListener('click', function () {
        if (sudahDijawab) { return; }
        const t = tersusun.pop();
        if (t) {
          const b = tray.querySelector('button[data-idx="' + t.idx + '"]');
          if (b) { b.classList.remove('pakai'); }
        }
        gambarSusun();
      });
      const ulang = el('button', 'pudar', '↺ Ulangi');
      ulang.type = 'button';
      ulang.addEventListener('click', function () {
        if (sudahDijawab) { return; }
        tersusun.length = 0;
        Array.prototype.forEach.call(tray.children, function (x) { x.classList.remove('pakai'); });
        gambarSusun();
      });
      const periksa = el('button', null, 'Periksa');
      periksa.type = 'button';
      periksa.disabled = true;

      if (sudahDijawab) {
        jawabKotak.className = 'jawab';
        jawabKotak.textContent = jwbLama;
        Array.prototype.forEach.call(tray.children, function (x) { x.disabled = true; });
      } else {
        periksa.addEventListener('click', function () {
          periksa.disabled = true;
          hapus.disabled = true;
          ulang.disabled = true;
          Array.prototype.forEach.call(tray.children, function (x) { x.disabled = true; });
          kirimJawaban(s.nomor, tersusun.map(function (t) { return t.kata; }).join(' '), true);
        });
        gambarSusun();
      }
      aksi.appendChild(hapus);
      aksi.appendChild(ulang);
      aksi.appendChild(periksa);
      return;
    }

    // ---------------- imla_rumpang
    if (s.mode === 'imla_rumpang') {
      const putar = tombolAudio(s, '▶ Putar audio kata');
      if (putar) { wadah.appendChild(putar); }
      if (s.audio_kalimat) {
        const pk = el('button', 'audio', '▶ Putar audio kalimat');
        pk.type = 'button';
        pk.addEventListener('click', function () { putarAudio(s.audio_kalimat); });
        wadah.appendChild(pk);
      }
      wadah.appendChild(potongKalimat(s, jwbLama));
      wadah.appendChild(kotakKetik(s, sudahDijawab, jwbLama, 'Ketik kata yang hilang lalu tekan Periksa'));
      return;
    }

    // ---------------- imla_murni
    if (s.mode === 'imla_murni') {
      const putar = tombolAudio(s, '▶ Putar audio');
      if (putar) { wadah.appendChild(putar); }
      wadah.appendChild(el('div', 'tanya', '🎧 Dengar lalu ketik'));
      wadah.appendChild(kotakKetik(s, sudahDijawab, jwbLama, 'Ketik kata utuh yang kamu dengar'));
      return;
    }

    // ---------------- ucap: mikrofon, PENILAIAN OTOMATIS (tanpa tombol "periksa")
    if (s.mode === 'ucap') {
      const putar = tombolAudio(s, '▶ Putar contoh');
      if (putar) { wadah.appendChild(putar); }

      const labelUcap = el('div', 'sub', 'Ucapkan kata ini:');
      labelUcap.style.marginTop = '10px';
      wadah.appendChild(labelUcap);
      wadah.appendChild(el('div', 'tanya' + (s.tanya_arab ? ' arab' : ''), s.tanya || ''));

      if (sudahDijawab) {
        const kotak = el('div', 'balasan info');
        kotak.textContent = 'Sudah dijawab: ' + jwbLama;
        wadah.appendChild(kotak);
        return;
      }

      const Kenal = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!Kenal) {
        const ket = el('div', 'sub', 'Peramban ini belum mendukung pengenal suara. Ucapkan, lalu KETIK jawabanmu di kotak bawah.');
        ket.style.marginTop = '10px';
        wadah.appendChild(ket);
        wadah.appendChild(kotakKetik(s, false, '', 'Ketik kata yang kamu ucapkan'));
        return;
      }

      const barisMic = el('div', 'mic-baris', null);
      const mic = el('button', 'mic-kecil', '🎙️');
      mic.type = 'button';
      mic.title = 'Nyalakan mikrofon';
      const status = el('span', 'mic-status', 'Ketuk mikrofon, ucapkan katanya, lalu tekan tombol ⏹ (matikan) — baru dinilai.');
      barisMic.appendChild(mic);
      barisMic.appendChild(status);
      wadah.appendChild(barisMic);

      const kotakDengar = el('div', 'dengar-kotak', null);
      kotakDengar.appendChild(el('span', 'label-kotak', 'Yang terdengar'));
      const teksDengar = el('div', 'dengar-teks samar', '…');
      kotakDengar.appendChild(teksDengar);
      wadah.appendChild(kotakDengar);

      let potongan = [];         // ucapan-ucapan FINAL di dalam perekaman ini (di-reset tiap kali mikrofon dinyalakan)
      let sementaraKini = '';    // tangkapan SEMENTARA terakhir — sering masih memuat kata terakhir yang belum final
      let teksSaatStop = '';     // teks yang tampil saat tombol ⏹ ditekan (jaring pengaman bila finalnya telat datang)
      let belumLengkap = 0;      // berapa kali ucapannya belum lengkap (biar siswa tidak terjebak)
      let selesaiSoal = false;
      let micNyala = false;

      const kenal = new Kenal();
      kenal.lang = s.tanya_arab ? 'ar-SA' : 'en-US';
      kenal.interimResults = true;      // tulisan muncul sambil siswa bicara
      kenal.continuous = true;          // tidak terputus sendiri saat siswa berhenti sejenak
      kenal.maxAlternatives = 1;

      function tampilkanDengar(teks, samar) {
        teksDengar.textContent = teks || '…';
        teksDengar.className = 'dengar-teks' + (samar ? ' samar' : '');
      }

      /* Gabungkan dua tangkapan dengan membuang tumpang tindih di ujungnya (kata-per-kata). */
      function gabungUcap(a, b) {
        const A = String(a || '').split(' ').filter(function (x) { return x !== ''; });
        const B = String(b || '').split(' ').filter(function (x) { return x !== ''; });
        if (!A.length) { return B.join(' '); }
        if (!B.length) { return A.join(' '); }
        const maks = Math.min(A.length, B.length);
        for (let n = maks; n > 0; n--) {
          let sama = true;
          for (let i = 0; i < n; i++) { if (A[A.length - n + i] !== B[i]) { sama = false; break; } }
          if (sama) { return A.slice(0, A.length - n).concat(B).join(' '); }
        }
        return A.concat(B).join(' ');
      }

      /* Apa yang SUDAH terdengar sekarang: ucapan final + tangkapan sementara (kata terakhir yang belum final). */
      function teksKini() {
        let t = potongan.join(' ').replace(/\s+/g, ' ').trim();
        if (sementaraKini !== '') { t = gabungUcap(t, sementaraKini).replace(/\s+/g, ' ').trim(); }
        return t;
      }

      /* Dinyalakan OLEH SISWA (tidak otomatis). */
      function mulaiDengar() {
        if (selesaiSoal || micNyala) { return; }
        potongan = [];                    // mulai dari bersih supaya percobaan baru tidak menumpuk
        sementaraKini = '';
        teksSaatStop = '';
        tampilkanDengar('', true);
        try {
          kenal.start();
          micNyala = true;
          mic.textContent = '⏹';
          mic.title = 'Matikan mikrofon (lalu dinilai)';
          mic.className = 'mic-kecil dengar';
          status.textContent = 'Mikrofon hidup — ucapkan katanya, lalu tekan ⏹ kalau sudah selesai.';
        } catch (e) {
          micNyala = false;
          status.textContent = 'Mikrofon belum bisa dinyalakan. Ketuk sekali lagi (peramban biasanya minta izin dulu).';
        }
      }

      /* Ditekan siswa saat ucapannya selesai → BARU dinilai benar/salah di server.
         Kata TERAKHIR sering baru berupa tangkapan sementara; kalau langsung dihentikan, kata itu hilang
         (dulu inilah sebab "teksnya sudah benar tapi dinilai salah"). Karena itu teks dikunci saat tombol
         ditekan, pengenal diberi jeda sedikit untuk memfinalkan, lalu yang dikirim = yang paling lengkap. */
      function matikanDanNilai() {
        if (selesaiSoal) { return; }
        teksSaatStop = teksKini();
        micNyala = false;
        mic.textContent = '🎙️';
        mic.title = 'Nyalakan mikrofon';
        mic.className = 'mic-kecil';
        try { kenal.stop(); } catch (e) {}
        status.textContent = 'Menunggu hasil akhir…';
        setTimeout(nilaiHasilUcap, 380);
      }

      function nilaiHasilUcap() {
        if (selesaiSoal) { return; }
        const kandidat = [teksKini(), teksSaatStop].concat(potongan);
        let kirim = '';
        let miripTerbaik = -1;
        for (let i = 0; i < kandidat.length; i++) {
          const c = String(kandidat[i] || '').replace(/\s+/g, ' ').trim();
          if (c === '') { continue; }
          const m = miripUcap(c, s.tanya || '');
          if (m > miripTerbaik || (m === miripTerbaik && c.length > kirim.length)) { miripTerbaik = m; kirim = c; }
        }
        if (kirim === '') {
          status.textContent = 'Tidak ada suara yang tertangkap. Ketuk mikrofon lalu ucapkan lagi.';
          return;
        }
        // Ucapan yang baru SEBAGIAN dari kata yang diminta (kata terakhir belum tertangkap) jangan
        // langsung dihukum salah — minta ulangi dulu, paling banyak 3 kali supaya tidak terjebak.
        const rapiKirim = rapiUcap(kirim);
        const rapiTanya = rapiUcap(s.tanya || '');
        if (belumLengkap < 3 && rapiTanya !== '' && rapiKirim !== '' && rapiKirim !== rapiTanya
            && rapiTanya.indexOf(rapiKirim) === 0) {
          belumLengkap++;
          tampilkanDengar(kirim, false);
          status.textContent = 'Kata terakhirnya belum tertangkap. Ketuk mikrofon, ucapkan sekali lagi, lalu tekan ⏹.';
          return;
        }
        tampilkanDengar(kirim, false);        // apa yang tampil = apa yang dinilai
        selesaiSoal = true;
        mic.disabled = true;
        status.textContent = 'Dinilai…';
        kirimJawaban(s.nomor, kirim, false);   // server yang menentukan benar/salah
      }

      kenal.onresult = function (e) {
        let ucapanTerakhir = '', sementara = '';
        const hasil = e.results || [];
        for (let i = 0; i < hasil.length; i++) {
          const r = hasil[i] || {};
          const t = (r[0] && r[0].transcript) ? r[0].transcript : '';
          if (r.isFinal) { ucapanTerakhir = t; } else { sementara = t; }
        }
        sementara = sementara.replace(/\s+/g, ' ').trim();
        ucapanTerakhir = ucapanTerakhir.replace(/\s+/g, ' ').trim();
        if (ucapanTerakhir !== '') {
          potongan.push(ucapanTerakhir);
          sementaraKini = '';
          tampilkanDengar(teksKini(), false);
          status.textContent = 'Tercatat. Ucapkan lagi bila perlu, lalu tekan ⏹ kalau sudah selesai.';
        } else if (sementara !== '') {
          sementaraKini = sementara;
          // ditampilkan SAMBIL BERJALAN termasuk kata yang belum final — inilah yang dilihat siswa
          tampilkanDengar(teksKini(), true);
        }
      };
      kenal.onend = function () {
        if (selesaiSoal) { return; }
        micNyala = false;
        mic.textContent = '🎙️';
        mic.className = 'mic-kecil';
        status.textContent = 'Mikrofon berhenti sendiri. Ketuk mikrofon untuk melanjutkan, lalu tekan ⏹ kalau sudah selesai.';
      };
      kenal.onerror = function (e) {
        if (selesaiSoal) { return; }
        micNyala = false;
        mic.textContent = '🎙️';
        mic.className = 'mic-kecil';
        const kode = (e && e.error) ? e.error : '';
        if (kode === 'not-allowed' || kode === 'service-not-allowed') {
          status.textContent = 'Mikrofon belum diizinkan. Ketuk mikrofon lalu pilih "Izinkan".';
        } else {
          status.textContent = 'Mikrofon gagal berbunyi. Ketuk mikrofon untuk mencoba lagi.';
        }
      };

      mic.addEventListener('click', function () {
        if (micNyala) { matikanDanNilai(); } else { mulaiDengar(); }
      });
      return;
    }

    // --- pembantu penilaian ucapan DI SISI KLIEN (untuk memutuskan kapan dikirim otomatis).
    //     Aturannya sengaja sama dengan server (huruf kecil, tanda baca & harakat dibuang) tetapi
    //     ambangnya lebih longgar: server tetap penentu akhirnya (>= 0.8).
    function rapiUcap(t) {
      let x = (t === undefined || t === null) ? '' : String(t);
      x = x.toLowerCase();
      x = x.replace(/[\u064B-\u065F\u0670\u06D6-\u06ED\u0640]/g, '');   // harakat & tatweel Arab
      x = x.replace(/[.,;:!?"'`()\[\]{}\-_\/\\|<>@#$%^&*+=~]/g, ' ');
      return x.replace(/\s+/g, ' ').trim();
    }
    function jarakUcap(a, b) {
      const m = a.length, nn = b.length;
      if (m === 0) { return nn; }
      if (nn === 0) { return m; }
      const baris = [];
      for (let j = 0; j <= nn; j++) { baris[j] = j; }
      for (let i = 1; i <= m; i++) {
        let sebelum = baris[0];
        baris[0] = i;
        for (let j = 1; j <= nn; j++) {
          const simpan = baris[j];
          baris[j] = Math.min(baris[j] + 1, baris[j - 1] + 1, sebelum + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1));
          sebelum = simpan;
        }
      }
      return baris[nn];
    }
    function miripUcap(a, b) {
      const x = rapiUcap(a), y = rapiUcap(b);
      if (x === '' || y === '') { return 0; }
      if (x === y) { return 1; }
      return 1 - jarakUcap(x, y) / Math.max(x.length, y.length);
    }

    // ---------------- jodoh versi 2: papan 10 pasang, pasangan tepat LANGSUNG LENYAP
    if (s.mode === 'jodoh' && s.versi === 2) {
      const n = s.jumlah_pasangan || (s.kiri || []).length;
      const variasi = s.variasi || 'teks';

      // aturan main ditulis singkat sekali saja di atas papan
      const info = el('div', 'sub', null);
      info.textContent = variasi === 'audio'
        ? 'Dengarkan tiap audio di kiri, lalu ketuk kata yang cocok di kanan. Pasangan tepat langsung hilang.'
        : 'Ketuk satu kata di kiri, lalu padanannya di kanan. Pasangan tepat langsung hilang.';
      wadah.appendChild(info);
      const infoDua = el('div', 'sub', null);
      infoDua.textContent = 'Salah? Boleh coba lagi — tapi pasangan itu tidak dapat poin. Nyawa tidak berkurang.';
      infoDua.style.marginTop = '2px';
      wadah.appendChild(infoDua);

      // state papan dibaca dari jawaban tersimpan (jadi tetap benar walau halaman dibuka ulang)
      const entri = {};
      String(jawaban[s.nomor] || '').split(/\s+/).forEach(function (potongan) {
        const bagian = potongan.split('-');
        if (bagian.length === 3) {
          const ki = parseInt(bagian[0], 10), ka = parseInt(bagian[1], 10), st = parseInt(bagian[2], 10);
          if (ki > 0 && ka > 0 && st > 0) { entri[ki] = {kanan: ka, status: st}; }
        }
      });

      const kotakPoin = el('div', 'jodoh-info', null);
      const pilPoin = el('span', 'poin-pil', 'poin 0');
      const pilSisa = el('span', 'poin-pil', n + ' pasang tersisa');
      kotakPoin.appendChild(pilPoin);
      kotakPoin.appendChild(pilSisa);
      wadah.appendChild(kotakPoin);

      const papan = el('div', 'jodoh', null);
      const kiriKol = el('div', 'kolom', null);
      const kananKol = el('div', 'kolom', null);
      kiriKol.appendChild(el('div', 'kepala-kolom', variasi === 'audio' ? '🔊 Audio' : 'Indonesia'));
      kananKol.appendChild(el('div', 'kepala-kolom', 'Pasangan'));
      papan.appendChild(kiriKol);
      papan.appendChild(kananKol);
      wadah.appendChild(papan);

      // peta audio boleh berbentuk larik (versi baru) atau objek bernomor (versi lama) → samakan
      const audioKiriJodoh = (function () {
        const a = s.audio_kiri || [];
        if (Array.isArray(a)) { return a; }
        return Object.keys(a).map(function (x) { return parseInt(x, 10); }).sort(function (x, y) { return x - y; })
          .map(function (x) { return a[x]; });
      })();

      const kiriBtn = [], kananBtn = [];
      let pilihKiri = null;

      function entriLengkap() {
        for (let i = 1; i <= n; i++) {
          const st = (entri[i] || {}).status || 0;
          if (st !== 1 && st !== 3) { return false; }
        }
        return true;
      }

      function terapkan() {
        let poin = 0, terpasang = 0;
        for (let i = 1; i <= n; i++) {
          const st = (entri[i] || {}).status || 0;
          if (st === 1) { poin++; }
          if (st === 1 || st === 3) { terpasang++; }
        }
        kiriBtn.forEach(function (b, idx) {
          const st = (entri[idx + 1] || {}).status || 0;
          b.className = (st === 2) ? 'hangus' : '';
          b.classList.remove('pilih-aktif');
          if (st === 1 || st === 3) { b.classList.add('sembunyi'); }
          if (st === 2 && b.querySelectorAll('.tanda').length === 0) { b.appendChild(el('span', 'tanda', ' ✗')); }
        });
        kananBtn.forEach(function (b, idx) {
          let dipakai = false;
          for (let i = 1; i <= n; i++) {
            const e = entri[i] || {};
            if (e.kanan === idx + 1 && (e.status === 1 || e.status === 3)) { dipakai = true; }
          }
          b.className = dipakai ? 'sembunyi' : (s.kanan_arab ? 'arab' : '');
        });
        pilPoin.textContent = 'poin ' + poin;
        pilSisa.textContent = (n - terpasang) + ' pasang tersisa';
        pilPoin.className = 'poin-pil' + (poin > 0 ? ' baik' : '');
      }

      function trimPetaAudio(x) { return (x === undefined || x === null) ? '' : String(x).trim(); }

      (s.kiri || []).forEach(function (kata, i) {
        const b = el('button', null, null);
        b.type = 'button';
        b.setAttribute('data-kiri', (i + 1) + '');
        if (variasi === 'audio') {
          const isi = el('span', 'putar', null);
          isi.appendChild(document.createTextNode('▶'));
          isi.appendChild(el('span', null, (i + 1) + ''));
          b.appendChild(isi);
        } else {
          b.appendChild(document.createTextNode(kata));
        }
        b.addEventListener('click', function () {
          if (b.classList.contains('sembunyi')) { return; }
          if (variasi === 'audio') {
            const namaAudio = trimPetaAudio(audioKiriJodoh[i]);
            if (!namaAudio) {
              tampilBalasan('info', '⚠️ Rekaman kata ini belum ada. Laporkan ke gurumu — kartu ini tetap bisa dipasangkan.');
            } else {
              putarAudio(namaAudio);
            }
          }
          kiriBtn.forEach(function (x) { x.classList.remove('pilih-aktif'); });
          b.classList.add('pilih-aktif');
          pilihKiri = i;
          if (variasi !== 'audio') { putarBunyi('ketuk'); }
        });
        kiriBtn.push(b);
        kiriKol.appendChild(b);
      });

      (s.kanan || []).forEach(function (kata, j) {
        const b = el('button', (s.kanan_arab ? 'arab' : ''), null);
        b.type = 'button';
        b.appendChild(document.createTextNode(kata));
        b.appendChild(el('span', 'no', (j + 1) + ''));
        b.addEventListener('click', function () {
          if (b.classList.contains('sembunyi')) { return; }
          if (pilihKiri === null) {
            tampilBalasan('info', 'Ketuk dulu ' + (variasi === 'audio' ? 'tombol audio' : 'kata') + ' di kolom kiri.');
            return;
          }
          kirimKetuk(pilihKiri + 1, j + 1);
        });
        kananBtn.push(b);
        kananKol.appendChild(b);
      });

      function kirimKetuk(kiri, kanan) {
        if (sudahKirim) { return; }
        sudahKirim = true;
        fetch(SIMPAN, {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json'},
          body: JSON.stringify({nomor: s.nomor, kiri: kiri, kanan: kanan, ketuk: true})
        }).then(function (r) { return r.json(); }).then(function (r) {
          sudahKirim = false;
          if (!r.ok) { tampilBalasan('info', r.pesan || 'Ketukan tidak diterima.'); return; }
          entri[kiri] = {kanan: kanan, status: r.status};
          pilihKiri = null;
          const kk = keadaan[r.mode] || keadaan[modeAktif];
          if (kk) {
            kk.dijawab = r.dijawab;
            kk.total = r.total;
            kk.nilai = r.nilai_mode;
            kk.dinilai = true;
            kk.tuntas = r.tuntas;
            kk.tanpaPoin = r.tanpa_poin;
          }
          terapkan();
          if (r.pasangan_benar) {
            putarBunyi('benar');
            efekBenar();
            tampilBalasan('benar', r.dapat_poin ? '✔ Pasangan tepat! +1 poin' : '✔ Terpasang — tapi pasangan ini tidak dapat poin.');
          } else {
            putarBunyi('salah');
            efekSalah(false);
            tampilBalasan('salah', '✘ Belum tepat. Boleh coba lagi, tapi pasangan ini tidak dapat poin lagi.');
          }
          nyawa(nyawaModeEl, k.nyawa);
          gambarPetaMode();
          if (entriLengkap()) {
            // papan ini selesai (semua pasangan terpasang) → tandai & lanjut sendiri
            k.sudah[s.nomor] = true;
            if (r.terjawab && Object.keys(r.terjawab).length) { k.terjawab = r.terjawab; }
            tombolMaju.disabled = false;
            tombolMaju.className = 'utama';
            tombolMaju.textContent = 'Lanjut →';
            setTimeout(function () { if (nomorAktif === s.nomor) { maju(); } }, 1000);
          }
        }).catch(function () {
          sudahKirim = false;
          tampilBalasan('info', 'Gagal mengirim pasangan. Periksa koneksi lalu coba lagi.');
        });
      }

      if (sudahDijawab && entriLengkap()) {
        // papan sudah selesai sebelumnya: tampilkan keadaan akhir tanpa bisa diketik lagi
        terapkan();
        Array.prototype.forEach.call(kiriBtn, function (x) { x.disabled = true; });
        Array.prototype.forEach.call(kananBtn, function (x) { x.disabled = true; });
        return;
      }

      terapkan();
      return;
    }

    // ---------------- jodoh (versi lama, 5 pasang, dinilai utuh) — tetap didukung
    if (s.mode === 'jodoh') {
      const n = s.jumlah_pasangan || (s.kiri || []).length;
      const papan = el('div', 'jodoh', null);
      const kiriKol = el('div', 'kolom', null);
      const kananKol = el('div', 'kolom', null);
      kiriKol.appendChild(el('div', 'kepala-kolom', 'Indonesia'));
      kananKol.appendChild(el('div', 'kepala-kolom', 'Pasangan'));
      papan.appendChild(kiriKol);
      papan.appendChild(kananKol);
      wadah.appendChild(papan);

      const kiriBtn = [], kananBtn = [];
      const pasang = new Array(n).fill(0);
      let pilihKiri = null;
      let terpakai = 0;

      (s.kiri || []).forEach(function (k, i) {
        const b = el('button', null, k);
        b.type = 'button';
        b.addEventListener('click', function () {
          if (b.classList.contains('hijau') || b.classList.contains('merah')) { return; }
          kiriBtn.forEach(function (x) { x.classList.remove('pilih-aktif'); });
          b.classList.add('pilih-aktif');
          pilihKiri = i;
        });
        kiriBtn.push(b);
        kiriKol.appendChild(b);
      });

      (s.kanan || []).forEach(function (k, j) {
        const b = el('button', (s.kanan_arab ? 'arab' : ''), null);
        b.type = 'button';
        b.appendChild(document.createTextNode(k));
        b.appendChild(el('span', 'no', (j + 1) + ''));
        b.addEventListener('click', function () {
          if (b.classList.contains('hijau') || b.classList.contains('merah')) { return; }
          if (pilihKiri === null) {
            tampilBalasan('info', 'Ketuk dulu kata Indonesia di kolom kiri, lalu pasangannya di kolom kanan.');
            return;
          }
          const i = pilihKiri;
          pasang[i] = j + 1;
          kiriBtn[i].classList.remove('pilih-aktif');
          pilihKiri = null;
          periksaPasangan(i, j + 1);
        });
        kananBtn.push(b);
        kananKol.appendChild(b);
      });

      function periksaPasangan(i, nomorKanan) {
        const teks = pasang.join(' ');
        fetch(SIMPAN, {
          method: 'POST',
          headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'Accept': 'application/json'},
          body: JSON.stringify({nomor: s.nomor, jawaban: teks, posisi: i + 1, periksa: true})
        }).then(function (r) { return r.json(); }).then(function (r) {
          if (!r.ok) { tampilBalasan('info', r.pesan || 'Tidak bisa diperiksa.'); return; }
          jawaban[s.nomor] = pasang.join(' ');
          kiriBtn[i].classList.add(r.posisi_benar ? 'hijau' : 'merah');
          kananBtn[nomorKanan - 1].classList.add(r.posisi_benar ? 'hijau' : 'merah');
          // tiap pasangan langsung diberi bunyi (dan getaran bila keliru; kilatan layar hanya untuk soal yang salah)
          if (r.posisi_benar) { putarBunyi('benar'); } else { putarBunyi('salah'); efekSalah(false); }
          terpakai++;
          tampilBalasan(r.posisi_benar ? 'benar' : 'salah', r.posisi_benar ? '✔ Pasangan tepat!' : '✘ Pasangan itu belum tepat.');
          if (terpakai >= n) {
            const lengkap = pasang.join(' ');
            kirimJawaban(s.nomor, lengkap, true);
          }
        }).catch(function () {
          tampilBalasan('info', 'Gagal memeriksa pasangan. Periksa koneksi.');
        });
      }
      return;
    }

    // ---------------- cadangan (jenis lain dari mesin lama)
    if (s.pilihan) {
      const tanya = el('div', 'tanya' + (s.tanya_arab ? ' arab' : ''), s.tanya || '');
      wadah.appendChild(tanya);
      const kotak = el('div', 'pilihan', null);
      (s.pilihan || []).forEach(function (p) {
        const b = el('button', 'pilih', p);
        b.type = 'button';
        b.disabled = sudahDijawab;
        b.addEventListener('click', function () {
          Array.prototype.forEach.call(kotak.children, function (x) { x.disabled = true; });
          b.className += ' aktif';
          kirimJawaban(s.nomor, p, true);
        });
        kotak.appendChild(b);
      });
      wadah.appendChild(kotak);
      return;
    }

    wadah.appendChild(el('div', 'tanya', s.tanya || ''));
    wadah.appendChild(kotakKetik(s, sudahDijawab, jwbLama, 'Ketik jawabanmu'));
  }

  function kotakKetik(s, sudahDijawab, jwbLama, petunjuk) {
    const kotak = el('div', null, null);
    const inp = document.createElement('input');
    inp.type = 'text';
    inp.className = 'masukan' + ((s.tanya_arab || s.kata_arab) ? ' arab' : '');
    inp.placeholder = petunjuk;
    inp.autocomplete = 'off';
    inp.autocapitalize = 'off';
    inp.spellcheck = false;
    inp.value = sudahDijawab ? jwbLama : '';
    inp.disabled = sudahDijawab;
    kotak.appendChild(inp);
    const aksi = el('div', 'tombol-aksi', null);
    const periksa = el('button', null, '✔ Periksa');
    periksa.type = 'button';
    periksa.disabled = sudahDijawab || inp.value.trim() === '';
    inp.addEventListener('input', function () { periksa.disabled = sudahDijawab || inp.value.trim() === ''; });
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !periksa.disabled) { periksa.click(); } });
    periksa.addEventListener('click', function () {
      periksa.disabled = true;
      inp.disabled = true;
      kirimJawaban(s.nomor, inp.value.trim(), true);
    });
    aksi.appendChild(periksa);
    kotak.appendChild(aksi);
    return kotak;
  }

  // ---------------------------------------------------------------- kendali
  tombolMaju.addEventListener('click', function () { maju(); });
  tombolMenu.addEventListener('click', function () { bukaMenu(); });
  document.getElementById('tutupKembali').addEventListener('click', function () { bukaMenu(); });
  document.getElementById('tutupUlangi').addEventListener('click', function () { mulaiBabakBaru(modeUlang); });
  document.getElementById('tombolSelesaiSesi').addEventListener('click', function () {
    const belum = URUT.filter(function (m) { return !keadaan[m].tuntas; });
    let pesan = 'Selesaikan sesi ini dan lihat nilainya?';
    if (belum.length) {
      pesan = 'Masih ada ' + belum.length + ' mode yang belum tuntas. Mode yang belum dikerjakan TIDAK dihitung dalam rata-rata. Selesaikan sekarang?';
    }
    if (window.confirm(pesan)) { kirimSelesai(); }
  });

  siapkanBunyi();
  gambarMenu();
})();
</script>
</body>
</html>
