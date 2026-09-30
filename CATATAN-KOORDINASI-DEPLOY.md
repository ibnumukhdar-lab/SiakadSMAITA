# CATATAN KOORDINASI DEPLOY SIAKAD — dua agen, satu server

Ditulis 1 Oktober 2026 setelah kejadian 30 Sep: deploy dari PC **menghapus** pekerjaan
yang dibuat langsung di server lewat sesi Hermes Telegram/VPS (Bee Smart Evaluasi,
dashboard, kepanjangan "SIAKAD" di login & navigasi, `public/vendor/qrcode.min.js`).

Ada **dua agen Hermes** yang bekerja pada aplikasi yang sama:

| Agen | Di mana | Cara menyentuh siakad |
|---|---|---|
| Agen PC (Hermes laptop) | `D:\SIAKAD-SMAITA` (repo git) | edit di klon → `bash deploy-server.sh` |
| Agen VPS (Hermes Telegram) | `/root` di VPS | dulu: edit langsung di hosting / scp skrip ke hosting |

Bahaya terbesar: **salah satu sisi menukar seluruh folder live** dengan salinannya,
sementara sisi lain punya perubahan yang belum ikut. Itulah yang terjadi.

---

## Aturan yang berlaku sekarang

1. **Satu sumber kebenaran = repo git** (`D:\SIAKAD-SMAITA` ↔ GitHub `ibnumukhdar-lab/SiakadSMAITA`).
   Perubahan apa pun (dari sisi mana pun) harus berakhir sebagai commit di repo ini.
   Mengedit langsung di hosting boleh untuk perbaikan mendesak, **tetapi harus segera
   ditarik ke repo** (lihat langkah 4).

2. **Menimpa kode server hanya lewat SATU skrip: `deploy-server.sh`** (portabel —
   bisa dijalankan dari PC maupun VPS: `bash deploy-server.sh [dir-kode]`).
   Skrip ini punya tiga penjaga:
   - **0/6 — periksa berkas penting** di kode sumber: kepanjangan "SIAKAD" di
     `auth/login.blade.php` & `layouts/navigation.blade.php`, dan
     `public/vendor/qrcode.min.js`. Berhenti bila salah satu hilang
     (lewati sengaja: `LEWATI_CEK_BERKAS=1`).
   - **1b/6 — pengaman perubahan di server**: setiap deploy sukses mencatat md5 323 berkas
     (`.manifest-deploy.md5`). Deploy berikutnya membandingkan; kalau ada berkas **baru
     atau berubah** di server yang tidak dikenal → **BERHENTI** dan mencetak daftarnya
     (lewati sengaja: `IZINKAN_TIMPA=1`).
   - **6/6 — mencatat keadaan** setelah deploy sukses (dasar pemeriksaan berikutnya).

3. **Hanya satu yang deploy pada satu waktu.** Kalau agen VPS baru saja deploy,
   agen PC harus `git pull`/memeriksa dulu sebelum menimpa — dan sebaliknya.
   Praktisnya: pengaman 1b/6 akan menangkap perbedaan itu dan memaksa pemeriksaan.

4. **Kalau pengaman bilang ada perubahan di server** (mis. hasil edit langsung):
   jangan timpa. Tarik dulu:
   ```
   bash tarik-dari-server.sh          # unduh ke _dari-server/ + daftar diff
   # gabungkan yang benar ke repo, commit, baru deploy
   ```

5. **Kotak pesan antar-agen: folder `/root/siakad/` di VPS.**
   Berkas paket/instruksi ditaruh di sana beserta `BACA-INI-*.txt`.
   Siapa pun yang selesai bekerja menaruh ringkasan (apa yang diubah, kapan, di berkas apa).

---

## Alur kerja yang disarankan

**Agen VPS (Telegram):**
- Menarik dulu: `cd /root/siakad-src && git pull` (klon repo) — atau, bila mengedit langsung
  di hosting: segera `bash tarik-dari-server.sh` di PC supaya perubahan masuk repo.
- Mengubah kode → `git commit` + `git push` (atau taruh berkasnya di `/root/siakad/`).
- Deploy: `bash /root/siakad-src/deploy-server.sh /root/siakad-src`.

**Agen PC:**
- `git pull` sebelum mulai; bila ada paket di `/root/siakad/`, periksa dulu.
- Deploy: `bash deploy-server.sh` (dari `D:\SIAKAD-SMAITA`).
- Setelah deploy: `git push` supaya VPS bisa menarik perubahan yang sama.

**Dilarang:** mengedit berkas di hosting tanpa menariknya ke repo, dan men-deploy
dari salinan yang belum menyusul klon yang lain.
