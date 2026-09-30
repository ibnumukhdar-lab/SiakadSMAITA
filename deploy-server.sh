#!/bin/bash
# Deploy SIAKAD dari D:\SIAKAD-SMAITA ke server (public_html/siakad.smaitarafah.sch.id)
# Dipakai oleh agent Hermes. Server: akun u8151173 (ssh alias nizhom).
# Aman: .env, storage (upload+cache), vendor, node_modules, zip backup TIDAK ditimpa.
set -euo pipefail

echo "== 1/6 kompres kode lokal (tanpa .git/.env/vendor/node_modules/upload/storage) =="
cd /d/SIAKAD-SMAITA
tar czf - \
  --exclude=.git --exclude=.env --exclude=vendor --exclude=node_modules \
  --exclude=TUSMAITA.CODE.zip --exclude=cgi-bin \
  --exclude='storage/app/private' --exclude='storage/app/public' \
  --exclude='storage/logs' --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' --exclude='storage/framework/views' \
  . > /tmp/siakad-deploy.tar.gz
echo "   ukuran: $(du -h /tmp/siakad-deploy.tar.gz | cut -f1)"

# =====================================================================
# 1b/6 — PENGAMAN PERUBAHAN DI SERVER (ditambahkan 1 Okt 2026)
# ---------------------------------------------------------------------
# Latar: 30 Sep 2026 deploy pernah MENGHAPUS pekerjaan yang dibuat langsung
# di server (modul Bee Smart Evaluasi, dashboard, pendaftaran rute) karena
# folder live ditukar dengan kode lokal. Sejak itu setiap deploy memeriksa
# dulu: apakah ada berkas di server yang BERUBAH/BARU sejak deploy terakhir?
# Kalau ada → BERHENTI, daftar berkasnya dicetak, dan operator memutuskan.
# Setelah dipastikan aman, jalankan ulang dengan: IZINKAN_TIMPA=1 bash deploy-server.sh
# (atau perbaiki dulu: tarik perubahan server itu ke repo lokal).
# =====================================================================
echo "== 1b/6 periksa perubahan yang dibuat langsung di server =="
ssh -n -o ConnectTimeout=20 -o BatchMode=yes nizhom '
  cd ~/public_html/siakad.smaitarafah.sch.id || exit 1
  if [ ! -f .manifest-deploy.md5 ]; then
    echo "   (belum ada catatan deploy sebelumnya — pemeriksaan dilewati, catatan dibuat sekarang)"
    find app resources routes database config public -type f \
      -not -path "public/build/*" -not -name ".manifest-deploy.md5" \
      -exec md5sum {} + | sort -k2 > .manifest-deploy.md5
    exit 0
  fi
  find app resources routes database config public -type f \
    -not -path "public/build/*" -not -name ".manifest-deploy.md5" \
    -exec md5sum {} + | sort -k2 > /tmp/manifest-sekarang.md5
  awk "{print \$2}" .manifest-deploy.md5 | sort > /tmp/nama-lama.txt
  awk "{print \$2}" /tmp/manifest-sekarang.md5 | sort > /tmp/nama-baru.txt
  echo "   ── berkas BARU di server (tidak ada di catatan deploy terakhir) ──"
  comm -13 /tmp/nama-lama.txt /tmp/nama-baru.txt
  echo "   ── berkas BERUBAH di server sejak deploy terakhir ──"
  sort -k2 .manifest-deploy.md5 > /tmp/md5-lama.txt
  sort -k2 /tmp/manifest-sekarang.md5 > /tmp/md5-baru.txt
  join -1 2 -2 2 -o 1.2,1.1,2.1 /tmp/md5-lama.txt /tmp/md5-baru.txt | awk "\$2 != \$3 {print \$1}"
' | tee /tmp/siakad-selisih.txt

if grep -qE "^(app|resources|routes|database|config|public)/" /tmp/siakad-selisih.txt; then
  if [ "${IZINKAN_TIMPA:-0}" = "1" ]; then
    echo "   !! ADA PERUBAHAN DI SERVER — IZINKAN_TIMPA=1 → tetap dilanjutkan (berkas itu akan ditimpa; salinan lama tetap ada di _old_<ts>)."
    cp /tmp/siakad-selisih.txt "/tmp/siakad-selisih-$(date +%Y%m%d_%H%M%S).txt"
  else
    echo ""
    echo "   ✋ BERHENTI: ada berkas di server yang tidak dikenal kode lokal (daftar di atas)."
    echo "      Deploy ini akan MENGHAPUS/ MENIMPA berkas tersebut."
    echo "      Pilihan: (1) tarik dulu perubahan itu ke repo lokal (repot tapi benar), atau"
    echo "               (2) jalankan ulang bila memang mau ditimpa:"
    echo "                   IZINKAN_TIMPA=1 bash deploy-server.sh"
    exit 3
  fi
fi
echo "   aman — tidak ada perubahan asing di server."

echo "== 2/6 kirim & ekstrak di folder _tmp =="
ssh -o ConnectTimeout=20 -o BatchMode=yes nizhom "
  cd ~/public_html &&
  rm -rf siakad.smaitarafah.sch.id_tmp &&
  mkdir -p siakad.smaitarafah.sch.id_tmp &&
  tar xzf - -C siakad.smaitarafah.sch.id_tmp &&
  cp -a siakad.smaitarafah.sch.id/.env siakad.smaitarafah.sch.id_tmp/.env &&
  cp -a siakad.smaitarafah.sch.id/.htaccess siakad.smaitarafah.sch.id_tmp/.htaccess &&
  if [ -e siakad.smaitarafah.sch.id/.well-known ]; then cp -a siakad.smaitarafah.sch.id/.well-known siakad.smaitarafah.sch.id_tmp/.well-known; fi
" < /tmp/siakad-deploy.tar.gz

echo "== 3/6 tukar folder (folder lama -> _old_<timestamp>) =="
# -n = jangan mewarisi stdin; tanpa ini, ssh bisa menggantung saat skrip dijalankan
# dari proses latar belakang/tanpa tty (folder sudah ditukar tapi composer tak jalan -> situs 500).
# PENTING: storage/app dipindah dengan `mv` (instan di filesystem yang sama), BUKAN `cp`.
# Menyalin storage/app (ratusan MB) memakan menit dan pernah membuat ssh menggantung
# sebelum langkah composer -> situs 500. Kalau perlu rollback: mv kembali storage/app ke folder _old_.
ssh -n -o ConnectTimeout=20 -o BatchMode=yes -o ServerAliveInterval=15 -o ServerAliveCountMax=6 nizhom "
  cd ~/public_html &&
  TS=\$(date +%Y%m%d_%H%M%S) &&
  mv siakad.smaitarafah.sch.id siakad.smaitarafah.sch.id_old_\$TS &&
  mv siakad.smaitarafah.sch.id_tmp siakad.smaitarafah.sch.id &&
  cd siakad.smaitarafah.sch.id &&
  chmod -R u+rwX storage bootstrap/cache &&
  mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs &&
  touch storage/framework/views/.gitignore storage/logs/.gitignore &&
  # Upload (storage/app) tidak ikut tar -> PINDAHKAN dari folder lama (instan, tanpa salin)
  rm -rf storage/app &&
  mv ../siakad.smaitarafah.sch.id_old_\$TS/storage/app ./storage/app &&
  echo '   backup lama: siakad.smaitarafah.sch.id_old_'\$TS
"

echo "== 4/6 composer + cache =="
ssh -n -o ConnectTimeout=20 -o BatchMode=yes -o ServerAliveInterval=15 -o ServerAliveCountMax=6 nizhom "
  cd ~/public_html/siakad.smaitarafah.sch.id &&
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php &&
  composer install --no-dev --no-interaction --prefer-dist --no-progress 2>&1 | tail -2 &&
  php artisan migrate --force &&
  php artisan config:cache && php artisan view:cache &&
  test -d vendor && test -f vendor/autoload.php && echo '   vendor OK'
"

echo "== 5/6 verifikasi =="
KODE=$(curl -sk -o /dev/null -w '%{http_code}' --max-time 25 https://siakad.smaitarafah.sch.id/ || true)
echo "   https://siakad.smaitarafah.sch.id -> HTTP $KODE"
if [ "$KODE" != "200" ]; then
  echo "   !! situs tidak 200. Cek: vendor ada? migrasi jalan? Jalankan langkah 4 manual."
fi
echo "== 6/6 catat keadaan deploy (untuk pengaman pemeriksaan berikutnya) =="
ssh -n -o ConnectTimeout=20 -o BatchMode=yes nizhom '
  cd ~/public_html/siakad.smaitarafah.sch.id || exit 1
  find app resources routes database config public -type f \
    -not -path "public/build/*" -not -name ".manifest-deploy.md5" \
    -exec md5sum {} + | sort -k2 > .manifest-deploy.md5
  echo "   catatan dibuat: $(wc -l < .manifest-deploy.md5) berkas"
  echo "   (hapus ~/public_html/siakad.smaitarafah.sch.id_old_* yang lama untuk menghemat ruang)"
'
echo "SELESAI"
