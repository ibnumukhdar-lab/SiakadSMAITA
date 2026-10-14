{{-- GAYA & SKRIP BERSAMA MODUL DINIYAH
     Ditulis di sini (bukan mengandalkan kelas Tailwind baru) karena CSS SIAKAD
     adalah hasil build Vite — kelas yang belum pernah dipakai tidak ada di CSS. --}}
<style>
    /* ---------- kerangka halaman ---------- */
    .dk-wrap { max-width: 1500px; margin: 0 auto; padding: 16px; }
    @media (min-width: 640px) { .dk-wrap { padding: 24px; } }
    @media (min-width: 1024px) { .dk-wrap { padding: 28px 32px; } }
    .dk-tumpuk { display: flex; flex-direction: column; gap: 14px; }
    .dk-tumpuk > * { min-width: 0; max-width: 100%; }   /* penting: tanpa ini sub-menu bisa memaksa halaman lebih lebar */

    .dk-kepala { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; justify-content: space-between; }
    .dk-kepala-teks { min-width: 0; }
    .dk-kicker { margin: 0; font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #94a3b8; }
    .dk-judul { margin: 2px 0 0; font-size: 21px; font-weight: 800; color: #1e293b; letter-spacing: -.01em; }
    .dk-lead { margin: 4px 0 0; font-size: 12.5px; font-weight: 600; color: #64748b; max-width: 78ch; }
    .dk-periode { margin: 0; font-size: 12px; font-weight: 700; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 11px; padding: 8px 12px; white-space: nowrap; }
    .dk-periode span { display: block; font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
    .dk-periode.buka { background: #f0fdf4; border-color: #86efac; color: #166534; }
    @media (max-width: 640px) { .dk-periode { white-space: normal; width: 100%; } }
    .dk-periode.tutup { background: #fffbeb; border-color: #fcd34d; color: #92400e; }

    /* ---------- sub-menu ---------- */
    .dk-tab { display: flex; gap: 2px; overflow-x: auto; overscroll-behavior-x: contain; border-bottom: 1px solid #e2e8f0; margin-top: 2px; min-width: 0; max-width: 100%; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
    .dk-tab a { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 14px; font-size: 13px; font-weight: 700; color: #64748b; white-space: nowrap; border-bottom: 2px solid transparent; text-decoration: none; }
    .dk-tab a:hover { color: #1f3a5f; }
    .dk-tab a.aktif { color: #1f3a5f; border-color: #1f3a5f; }

    /* ---------- kartu & formulir ---------- */
    .dk-kartu { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; }
    .dk-kartu > h2 { margin: 0 0 12px; font-size: 14px; font-weight: 800; color: #1e293b; }
    .dk-kartu > h2 .dk-ket { display: block; margin-top: 2px; font-size: 11.5px; font-weight: 600; color: #64748b; }
    .dk-pesan { border-radius: 11px; padding: 11px 14px; font-size: 12.5px; font-weight: 700; }
    .dk-pesan.ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .dk-pesan.galat { background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; }
    .dk-kosong { text-align: center; padding: 22px 14px; color: #64748b; font-size: 13px; font-weight: 600; }
    .dk-kosong .dk-ikon { display: block; font-size: 26px; margin-bottom: 6px; }

    label.dk-lbl { display: block; min-width: 0; }
    label.dk-lbl > span { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .dk-kendali { width: 100%; min-height: 44px; border: 1px solid #cbd5e1; border-radius: 10px; padding: 0 12px; background: #fff; color: #0f172a; font-size: 14px; font-weight: 600; }
    .dk-kendali:focus { outline: 2px solid #93c5fd; outline-offset: 1px; border-color: #60a5fa; }
    .dk-kendali.dk-kecil { min-height: 40px; font-size: 13px; }
    .dk-bantu { margin: 6px 0 0; font-size: 11.5px; font-weight: 600; color: #94a3b8; }
    .dk-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .dk-item { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; }
    .dk-item + .dk-item { margin-top: 10px; }
    .dk-item.buka { border-color: #86efac; background: #f0fdf4; }
    .dk-span2 { grid-column: span 1; }
    @media (min-width: 900px) { .dk-span2 { grid-column: span 2; } }
    .dk-baris-kendali { display: flex; flex-wrap: wrap; gap: 10px; }

    .dk-kisi { display: grid; gap: 12px; grid-template-columns: 1fr; }
    @media (min-width: 640px) { .dk-kisi.dua { grid-template-columns: 1fr 1fr; } .dk-kisi.empat { grid-template-columns: repeat(2, 1fr); } }
    @media (min-width: 900px) { .dk-kisi.empat { grid-template-columns: repeat(4, 1fr); } }
    .dk-lebar { grid-column: 1 / -1; }

    .dk-tombol { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px; padding: 0 16px; border-radius: 10px; border: 1px solid transparent; font-size: 13px; font-weight: 800; text-decoration: none; cursor: pointer; }
    .dk-tombol.utama { background: #1f3a5f; color: #fff; }
    .dk-tombol.utama:hover { background: #182c47; }
    .dk-tombol.sekunder { background: #fff; color: #334155; border-color: #cbd5e1; }
    .dk-tombol.sekunder:hover { background: #f8fafc; }
    .dk-tombol.hijau { background: #15803d; color: #fff; }
    .dk-tombol.hijau:hover { background: #166534; }
    .dk-tombol.polos { background: transparent; color: #64748b; border-color: transparent; padding: 0 8px; }
    .dk-tombol.polos:hover { color: #1f3a5f; }

    /* pilihan kelas (centang) */
    .dk-cip { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; font-size: 12.5px; font-weight: 700; color: #334155; }
    .dk-cip input { width: 18px; height: 18px; }
    .dk-cip:has(input:checked) { background: #f2f6fb; border-color: #1f3a5f; color: #1f3a5f; }

    /* ---------- tabel (layar sedang ke atas) ---------- */
    .dk-tabel-bungkus { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0 -4px; }
    table.dk-tabel { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 640px; }
    table.dk-tabel th, table.dk-tabel td { padding: 10px 12px; text-align: left; vertical-align: middle; font-size: 13px; color: #334155; overflow-wrap: break-word; }
    table.dk-tabel thead th { background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #64748b; position: sticky; top: 0; z-index: 2; }
    table.dk-tabel tbody tr + tr td { border-top: 1px solid #f1f5f9; }
    table.dk-tabel tbody tr:hover td { background: #fbfdff; }
    table.dk-tabel td.nama { font-weight: 700; color: #1e293b; }
    .dk-nomor { text-align: center; white-space: nowrap; color: #64748b; }
    .dk-tengah { text-align: center; }
    .dk-kanan { text-align: right; }
    .dk-arab { font-family: 'Amiri', serif; direction: rtl; color: #475569; }

    /* ---------- kartu per santri (khusus HP) ---------- */
    .dk-saja-hp { display: block; }
    .dk-saja-lebar { display: none; }
    @media (min-width: 768px) { .dk-saja-hp { display: none; } .dk-saja-lebar { display: block; } }

    .dk-baris-santri { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; }
    .dk-baris-santri + .dk-baris-santri { margin-top: 10px; }
    .dk-nama { font-size: 14px; font-weight: 800; color: #1e293b; }
    .dk-meta { font-size: 11.5px; font-weight: 700; color: #64748b; }
    .dk-meta .dk-pil { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #f1f5f9; color: #475569; font-weight: 800; }

    /* pilih status: tombol besar sekali sentuh */
    .dk-pilih { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; margin-top: 10px; }
    .dk-pilih label { position: relative; display: block; }
    .dk-pilih input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .dk-pilih span { display: flex; align-items: center; justify-content: center; min-height: 44px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; font-size: 12.5px; font-weight: 800; color: #475569; }
    .dk-pilih input:checked + span { color: #fff; border-color: #1f3a5f; background: #1f3a5f; }
    .dk-pilih input[value="hadir"]:checked + span { background: #15803d; border-color: #15803d; }
    .dk-pilih input[value="sakit"]:checked + span { background: #b45309; border-color: #b45309; }
    .dk-pilih input[value="izin"]:checked + span { background: #1d4ed8; border-color: #1d4ed8; }
    .dk-pilih input[value="alpa"]:checked + span { background: #b91c1c; border-color: #b91c1c; }
    .dk-pilih input:focus-visible + span { outline: 2px solid #93c5fd; outline-offset: 1px; }

    /* ---------- bar aksi lengket ---------- */
    .dk-bar { position: sticky; bottom: 0; z-index: 6; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; margin-top: 12px; padding: 10px 0 8px; background: #fff; border-top: 1px solid #e2e8f0; box-shadow: 0 -8px 16px -10px rgba(15, 23, 42, .3); border-radius: 12px 12px 0 0; }
    .dk-daftar-santri { padding-bottom: 8px; }
    @media (max-width: 640px) {
        .dk-bar { gap: 6px; padding: 8px 0 6px; }
        .dk-bar .dk-cari { flex: 1 1 100%; }
        .dk-bar .dk-tombol { flex: 1 1 0; padding: 0 10px; font-size: 12.5px; }
        .dk-hitung { display: none; }              /* di HP penghitung disembunyikan agar bar tetap tipis */
        .dk-saja-hp { padding-bottom: 64px; }      /* ruang agar kartu terakhir selalu bisa diangkat melewati bar lengket */
    }
    .dk-hitung { font-size: 12px; font-weight: 700; color: #475569; }
    .dk-cari { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
</style>
<script>
    /* Modul ini menampilkan tabel (layar lebar) DAN daftar kartu (HP) dengan nama
       kolom yang sama supaya bisa dipakai dua-duanya. Hanya satu yang boleh terkirim:
       kontrol di sisi yang tersembunyi dinonaktifkan sesuai lebar layar. */
    (function () {
        function aturSisi() {
            var lebar = window.matchMedia('(min-width: 768px)').matches;
            document.querySelectorAll('.dk-saja-hp [name]').forEach(function (el) { el.disabled = lebar; });
            document.querySelectorAll('.dk-saja-lebar [name]').forEach(function (el) { el.disabled = ! lebar; });
        }
        document.addEventListener('DOMContentLoaded', aturSisi);
        window.addEventListener('resize', aturSisi);
        aturSisi();

        /* "Semua hadir" mengisi semua kontrol status yang sedang terlihat. */
        window.dkSemuaHadir = function () {
            document.querySelectorAll('.dk-pilih input[value="hadir"]').forEach(function (r) { r.checked = true; });
            document.querySelectorAll('select[name^="status"]').forEach(function (s) { s.value = 'hadir'; });
            window.dkHitung();
        };

        /* Penghitung baris yang sedang terlihat (tabel di layar lebar, kartu di HP). */
        window.dkHitung = function () {
            var p = document.getElementById('dk-hitung');
            if (! p) return;
            var baris = Array.prototype.filter.call(document.querySelectorAll('.dk-bar-santri'), function (el) { return el.offsetParent !== null; });
            var kata = p.dataset.kata || 'santri';
            var dasar = p.dataset.dasar || '';
            p.textContent = (dasar ? dasar + ' · ' : '') + baris.length + ' ' + kata;
        };
        document.addEventListener('change', function (e) {
            if (e.target && (e.target.name || '').indexOf('status') === 0) window.dkHitung();
        });
        document.addEventListener('DOMContentLoaded', window.dkHitung);
        window.addEventListener('resize', window.dkHitung);
    })();
</script>
