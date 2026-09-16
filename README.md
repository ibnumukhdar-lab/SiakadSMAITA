# SiakadSMAITA

SIAKAD.SMAITARAFAH.SCH.ID

Sistem Akademik / Kesiswaan **SMA IT Arafah** — aplikasi Laravel yang menjalankan
**https://siakad.smaitarafah.sch.id**.

Repo ini adalah salinan kode yang **sama persis dengan yang ada di hosting**
(akun Hostinger `u8151173`), supaya ada riwayat versi dan bisa dipulihkan.

## Isi repo

| Ada di repo | Keterangan |
|---|---|
| `app/`, `routes/`, `resources/`, `config/`, `database/`, `bootstrap/`, `public/` (tanpa `build`) | kode aplikasi |
| `public/build/` | hasil build Vite (Tailwind + JS) — **ikut di-commit** karena server tidak punya `node_modules` dan tidak bisa `npm run build` |
| `.htaccess` | aturan rewrite di docroot (semua permintaan masuk ke `public/`) |
| `JALANKAN.cmd`, `deploy-server.sh` | jalankan lokal & skrip deploy ke hosting |

**Tidak** ada di repo (dan tidak boleh di-commit):
`.env` (berisi sandi DB), `vendor/`, `node_modules/`, `storage/` (269 MB — unggahan
guru: audio Bee Smart, arsip surat, log, sesi). Data unggahan hanya ada di server.

## Hak akses & catatan modul Data Induk

- Menu modul dijaga izin `buka-menu-*` (Spatie). Super Admin lolos otomatis lewat `Gate::before`.
- Menghapus siswa dari Data Induk **tidak permanen** — datanya masuk **Tong Sampah**
  (`/siswa/tong-sampah`) dan masih bisa dipulihkan. Hapus permanen hanya dari halaman
  Tong Sampah dan butuh izin `hapus-permanen-siswa` (hanya Super Admin yang memilikinya
  secara bawaan; bisa diberikan lewat Kelola Akun).
- Validasi: NISN wajib 10 digit angka (spasi & karakter tak terlihat dari Excel otomatis
  dibuang). Data lama yang NISN-nya belum 10 digit tetap bisa disunting selama NISN-nya
  tidak diubah — barisnya ditandai ⚠ di daftar, dan ada tombol "Saring yang perlu dibetulkan".
- Impor CSV: tiap baris divalidasi lebih dulu; baris bermasalah **tidak** diimpor dan
  rinciannya ditampilkan setelah impor. Impor tidak lagi menghapus data di Tong Sampah.
- Kolom yang boleh diisi dari form dibatasi daftar putih (`KOLOM_ISI` di `SiswaController`).

## Fitur Data Induk (tahap 2)

- **Daftar server-side**: pencarian (nama/NISN/NIS), filter kelas/status/gender/angkatan/kelengkapan
  data, urut (terbaru/nama/kelas/NISN/terlama), dan paginasi (25–200 per halaman) diproses di server —
  aman untuk ribuan baris. Indeks DB ditambahkan lewat migrasi `tambah_indeks_pencarian_siswas`.
- **Ekspor CSV** (`/siswa/ekspor`) mengikuti filter yang sedang aktif (23 kolom, BOM agar Excel rapi).
- **Cetak daftar** (`/siswa/cetak`) — siap print/PDF, dikelompokkan per kelas, memuat kop sekolah.
- **Kartu pelajar** (`/siswa/{id}/kartu`) — foto, NISN/NIS, kelas, tahun ajaran + QR verifikasi ke
  halaman profil publik; tombol cetak & opsi `?auto=1` untuk cetak otomatis.
- **Unggah foto dari kamera HP**: pilih dari kamera atau berkas, foto dikecilkan di perangkat
  (maks 900 px, JPEG mutu ~0.8, pola canvas + perpindahan ke input tersembunyi) sebelum dikirim.
- **Impor berpratinjau** (`/siswa/import` → pratinjau → eksekusi): judul kolom dibaca otomatis
  (urutan kolom bebas), baris bermasalah/duplikat dirinci, laporan masalah bisa diunduh CSV, dan
  data baru masuk setelah tombol impor ditekan. Pratinjau disimpan sementara di
  `storage/app/pratinjau-impor` (token UUID, dibersihkan otomatis setelah 6 jam).
- Tampilan disiapkan dua bentuk: tabel untuk PC/tablet dan kartu untuk HP (tap target ≥ 36 px,
  panel filter & aksi massal bisa dilipat di layar kecil).

> **Penting untuk pengembangan**: setiap menambah kelas Tailwind baru, jalankan `npm run build`
> sebelum deploy — hasilnya ada di `public/build` dan ikut dikirim `deploy-server.sh`. Kalau lupa,
> kelas baru (mis. `w-[68px]`, `text-[12.5px]`) tidak ada di CSS hasil build dan tampilan jadi tidak rapi.

## Modul Kelola Kelas (pengelompokan kelas)

- Halaman **Kelola Kelas** (`/kelas`, menu di bagian Utama): tambah kelas, ganti nama, atur
  **tingkat** (X/XI/XII/Lulus), urutan tampil, keterangan, dan status aktif/nonaktif; kelas
  bisa dihapus selama belum dipakai data siswa.
- Daftar kelas ini yang muncul sebagai pilihan **kelas** di Data Siswa (form Tambah/Edit,
  filter, dan aksi massal **Pindahkan ke kelas…**) — jadi pengelompokan siswa ikut daftar,
  bukan teks bebas lagi.
- Ganti nama kelas yang masih dipakai akan **ditolak** kecuali admin mencentang
  "Ikut pindahkan siswa" (barulah kolom kelas siswa ikut diperbarui) — supaya tidak ada siswa
  yang nyangkut di nama kelas lama.
- Bila ada nama kelas di data siswa yang belum terdaftar (mis. hasil impor lama), halaman
  Kelola Kelas menampilkan blok peringatan + tombol **Daftarkan sebagai kelas** (hanya menambah
  ke daftar, tidak mengubah data siswa).
- **Desain penting**: kolom `siswas.kelas` tetap teks — sengaja TIDAK dijadikan relasi/FK supaya
  modul yang sudah berjalan (Asrama: kamar & penilaian; Student Root: grup binaan & poin;
  Bee Smart; dashboard) tidak terpengaruh. Dashboard per tingkat (X/XI/XII) dihitung dari daftar
  kelas, jadi kelas seperti "X IPA 1" tetap terhitung sebagai tingkat X.

## Tahun Ajaran, Wali Kelas, dan Kenaikan Kelas

- **Tahun Ajaran** (`/tahun-ajaran`): daftar tahun ajaran dengan **satu yang aktif**. Yang aktif dipakai
  sebagai isian otomatis `tahun_ajaran` saat menambah/mengimpor siswa dan sebagai nilai bawaan pada
  proses kenaikan kelas. Ganti nama tahun ajaran yang masih dipakai akan ditolak kecuali dicentang
  "Ikut perbarui data siswa"; tahun yang aktif atau masih dipakai tidak bisa dihapus.
- **Wali Kelas**: diatur per kelas (menu Kelola Kelas, form Tambah maupun Edit) — bisa dipilih dari
  akun pengguna (dikelompokkan per peran: Guru, Musyrif, Tata Usaha, dst.) atau ditulis manual bila
  walinya belum punya akun. Namanya tampil di daftar kelas.
- **Kenaikan Kelas** (`/kenaikan-kelas`): memindahkan **seluruh siswa satu kelas sekaligus** —
  pilih beberapa kelas asal, tentukan kelas tujuan (atau **Lulus/Alumni**), isi tahun ajaran baru,
  dan lihat ringkasan "N siswa akan dipindahkan" sebelum diproses. Hanya menyentuh kolom `kelas`,
  `status`, dan `tahun_ajaran` siswa; kamar Asrama dan grup Student Root tidak tersentuh.
- Aksi massal di Data Siswa juga punya opsi **Pindahkan ke kelas…** untuk memindahkan siswa terpilih
  (per halaman), melengkapi proses per kelas di halaman Kenaikan Kelas.

## Peta produksi

- Live: `https://siakad.smaitarafah.sch.id`
- Host: Hostinger, akun `u8151173`, SSH alias `nizhom` (port 65002)
- Folder aplikasi: `~/public_html/siakad.smaitarafah.sch.id` — **Laravel ada DI DALAM docroot**
  (berbeda dari situs `smaitarafah.sch.id` yang aplikasinya di luar docroot).
  `.htaccess` di root meneruskan semua permintaan ke `public/`.
- Basis data: MySQL/MariaDB `u8151173_siakad` (charset `latin1`, data produksi asli).
  Cadangan: `~/siakad-smaita-dump.sql` di home server.
- Aset frontend diambil dari `public/build` (hasil `npm run build`), bukan CDN.

## Menjalankan di komputer

1. Dobel-klik `JALANKAN.cmd` → menyalakan MySQL XAMPP bila mati, lalu
   `php artisan serve --port=8070` → buka http://localhost:8070
2. Database lokal: `siakad_smaita` (MySQL XAMPP, root tanpa sandi).
3. Salin `.env.example` → `.env` lalu sesuaikan `DB_*` (jangan pakai kredensial produksi).

## Deploy ke hosting

```
bash deploy-server.sh
```

Skrip mengemas kode (tanpa `.env`, `vendor`, `node_modules`, `storage`) → unggah ke
folder `_tmp` di server → simpan `.env`/`.htaccess` → tukar folder → **kembalikan
`storage/app` (unggahan) dari folder lama** → `composer install --no-dev` →
`php artisan migrate --force` → `config:cache` lalu `view:cache` → cek HTTP 200.
Folder lama tetap ada sebagai `_old_<tanggal>` untuk rollback.

Catatan penting:

- Jangan `route:cache`.
- Jangan menaruh file di `/storage/{path}` — framework memakai prefix itu sendiri;
  semua tautan berkas memakai `/berkas/{path}`.
- Perubahan skema DB = migration baru, jangan mengubah migration lama.

## Akun & peran

Peran (Spatie): Super Admin, Guru, Tata Usaha, Kepala Sekolah, Kepala Diniyah, Musyrif.
Login memakai email + sandi dari tabel `users`.

## Modul

| Modul | Isi |
|---|---|
| Master Siswa | data siswa, kelas, status (soft delete) |
| E-Arsip | surat masuk/keluar, unggah berkas, ekspor CSV |
| Student Root | master kriteria poin, grup binaan, input poin sikap, TV display |
| Asrama | kamar putra/putri, musyrif, inspeksi harian, finalisasi → injeksi poin |
| Bee Smart | kosakata mingguan 3 bahasa + audio, mode kelas, buku saku, klaim poin |
| Kelola Akun | pengguna, peran, matriks izin menu (`buka-menu-*`), impersonate |
| Pengaturan | identitas sekolah & tampilan |
