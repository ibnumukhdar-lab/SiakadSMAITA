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
