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

## Penilaian Karakter: Adab & Keasramaan

Modul penilaian berbasis kuesioner skala Likert 1–5 per siswa.

- **Master Penilaian** (`/penilaian/master`, izin `kelola-master-penilaian` — Tata Usaha/Super Admin):
  daftar pertanyaan Adab & Keasramaan (bisa ditambah/diubah/dinonaktifkan), daftar periode penilaian
  (satu periode aktif), dan ambang predikat (bawaan A ≥ 90, B ≥ 80, C ≥ 70, sisanya D).
  Pertanyaan yang sudah dipakai di penilaian **tidak bisa dihapus** — pakai "nonaktifkan".
  Periode yang sedang aktif atau sudah punya lembar penilaian juga tidak bisa dihapus.
- **Pengisian** (`/penilaian/adab`, `/penilaian/keasramaan`, izin `nilai-adab` / `nilai-keasramaan` —
  **musyrif/musyrifah**): satu "lembar penilaian" (sesi) per musyrif per periode. Bila satu siswa dinilai
  lebih dari satu musyrif, nilainya **dirata-ratakan** di rekap (nama pengisi tampil di rekap/CSV).
  Kepala Diniyah **tidak mengisi** — beliau hanya bisa melihat rekap (izin `buka-menu-penilaian`).
  Lembar Adab maupun Keasramaan punya bantuan **isi cepat per kamar** (skor sama untuk semua penghuni kamar)
  yang masih bisa dikoreksi per siswa; kamar binaan musyrif ditandai "· binaan saya" dan ada chip filter cepat.
- **Alur**: pilih periode → buka lembar → isi per siswa (tombol "Simpan & lanjut siswa berikutnya")
  → **Finalkan**. Setelah final, nilai terkunci; hanya Super Admin yang bisa "buka kembali".
- **Perhitungan**: persentase = (jumlah skor ÷ (jumlah pertanyaan dijawab × 5)) × 100; nilai akhir
  adab/keasramaan = rata-rata persentase tiap penilai; predikat dari ambang di Master Penilaian.
- **Rekap** (`/penilaian/rekap`): tabel nilai akhir per siswa (kolom per penilai, rata-rata, predikat),
  ringkasan sebaran predikat, filter periode/kelas/nama, **ekspor CSV**, dan halaman rincian per siswa
  (`/penilaian/siswa/{id}`) berisi skor tiap pertanyaan dari setiap penilai.
- **Keamanan data**: semua tabel baru (`penilaian_kriteria`, `penilaian_periode`, `penilaian_sesi`,
  `penilaian_jawaban`, `penilaian_pengaturan`). Tabel `siswas`, `asrama_members`, `sr_group_members`,
  dan `sr_point_entries` tidak disentuh, jadi kamar asrama, grup binaan, dan poin sikap tetap utuh.

## Project Student Root (5 tahap) — penilaian per siswa, jumlah project bebas

Modul penilaian project untuk grup binaan Student Root.

- **Jumlah project bebas per grup**: mentor (Guru) menambah project sesuai kebutuhan grupnya di
  `/project-sr` (nama, tema/tujuan, tanggal mulai–selesai, status rencana/berjalan/selesai).
- **5 tahap penilaian** (bisa diubah namanya, bobot, dan panduannya di `/project-sr/tahap/master` —
  Tata Usaha/Super Admin): Observasi 15%, Perencanaan 20%, Perancangan 25%, Validasi Ahli 20%,
  Presentasi Publik 20%. Bobot ideal 100%, tapi tetap dihitung proporsional bila belum pas.
- **Status tahap di level project** (belum / berjalan / selesai / **tidak dipakai**) untuk memantau progres;
  tahap yang ditandai "tidak dipakai" otomatis dikeluarkan dari perhitungan dan bobotnya dialihkan
  ke tahap lain. Project otomatis berstatus "berjalan" saat ada nilai, dan "selesai" saat semua tahap
  yang dipakai selesai.
- **Nilai diisi per SISWA per TAHAP (0–100)** di halaman detail project — setiap anak punya nilai sendiri.
  Tersedia bantuan "Isi cepat satu tahap" (nilai sama untuk seluruh anggota), lalu bisa dikoreksi per siswa.
  Ada juga kolom catatan per siswa.
- **Nilai akhir project per siswa** = rata-rata berbobot tahap yang dipakai. Predikat memakai ambang yang
  sama dengan penilaian karakter (A ≥ 90, B ≥ 80, C ≥ 70, D < 70).
- **Nilai akhir Student Root per siswa** = rata-rata nilai project yang **lengkap** (semua tahap yang dipakai
  sudah dinilai). Project yang baru sebagian dinilai tetap tampil dengan tanda `*`/kuning dan **tidak**
  ikut menghitung nilai akhir — supaya nilai tidak terlihat bagus padahal penilaian belum selesai.
- **Rekap** (`/project-sr/rekap`): tabel siswa × project, nilai akhir + predikat, jumlah project yang
  dilaksanakan, rata-rata per tahap (untuk melihat tahap kuat/lemah), ekspor CSV.
- **Hapus project**: tombol 🗑️ "Hapus project" tampil jelas di halaman project (dan tombol ikon di daftar
  project) dengan **popup konfirmasi** yang menyebut nama project, jumlah nilai, jumlah berkas, dan
  peringatan bila project sudah dinilai. Menghapus project juga menghapus nilai, foto/dokumentasi
  (termasuk berkasnya di storage), dan portofolionya.
- **Penyusunan Portofolio** (`/project-sr/portofolio`): halaman ini otomatis menerima project yang sudah
  menuntaskan seluruh tahap yang dipakai. Isinya:
  rekap otomatis 5 tahap (status, tanggal, catatan, rata nilai per tahap), tabel nilai seluruh anggota,
  form narasi (ringkasan, latar belakang, tujuan, pelaksanaan, hasil, refleksi + tempat & tanggal
  presentasi publik), dan unggahan **foto/dokumentasi** (jpg/png/webp/gif/pdf, maks 5 MB per berkas,
  bisa beberapa sekaligus, ada kolom keterangan, bisa dihapus per berkas).
  Tombol **🖨️ Cetak Portofolio** muncul setelah minimal satu berkas diunggah.
- **Cetak portofolio** (`/project-sr/{id}/portofolio/cetak`): halaman siap cetak A4 (kop sekolah, identitas
  project, narasi, tabel rekap tahap, tabel nilai anggota + predikat, galeri dokumentasi, kolom tanda
  tangan mentor & kepala sekolah) — tombol "Cetak / Simpan PDF" memakai dialog cetak browser.
  Hanya mentor grup (dan Super Admin) yang bisa menyusun; Tata Usaha bisa melihat & mencetak.
- **Izin**: `buka-menu-project-sr` (Guru, Tata Usaha), `kelola-project-sr` + `nilai-project-sr` (Guru),
  `kelola-master-project-sr` (Tata Usaha). Mentor hanya bisa mengelola project grup binaannya sendiri;
  Super Admin dan Tata Usaha bisa melihat semua.
- **Integrasi**: nilai project muncul di halaman rincian penilaian per siswa (`/penilaian/siswa/{id}`)
  bersama nilai Adab dan Keasramaan.
- **Keamanan data**: tabel baru `sr_project_tahap`, `sr_projects`, `sr_project_tahap_status`,
  `sr_project_nilai`. Tabel `sr_groups`, `sr_group_members`, `sr_point_criteria`, dan `sr_point_entries`
  tidak disentuh — grup binaan dan poin sikap tetap utuh. Project yang sudah punya nilai tidak bisa dihapus.

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
