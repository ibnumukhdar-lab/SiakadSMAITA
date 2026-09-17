#!/bin/bash
# Deploy SIAKAD dari D:\SIAKAD-SMAITA ke server (public_html/siakad.smaitarafah.sch.id)
# Dipakai oleh agent Hermes. Server: akun u8151173 (ssh alias nizhom).
# Aman: .env, storage (upload+cache), vendor, node_modules, zip backup TIDAK ditimpa.
set -euo pipefail

echo "== 1/5 kompres kode lokal (tanpa .git/.env/vendor/node_modules/upload/storage) =="
cd /d/SIAKAD-SMAITA
tar czf - \
  --exclude=.git --exclude=.env --exclude=vendor --exclude=node_modules \
  --exclude=TUSMAITA.CODE.zip --exclude=cgi-bin \
  --exclude='storage/app/private' --exclude='storage/app/public' \
  --exclude='storage/logs' --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' --exclude='storage/framework/views' \
  . > /tmp/siakad-deploy.tar.gz
echo "   ukuran: $(du -h /tmp/siakad-deploy.tar.gz | cut -f1)"

echo "== 2/5 kirim & ekstrak di folder _tmp =="
ssh -o ConnectTimeout=20 -o BatchMode=yes nizhom "
  cd ~/public_html &&
  rm -rf siakad.smaitarafah.sch.id_tmp &&
  mkdir -p siakad.smaitarafah.sch.id_tmp &&
  tar xzf - -C siakad.smaitarafah.sch.id_tmp &&
  cp -a siakad.smaitarafah.sch.id/.env siakad.smaitarafah.sch.id_tmp/.env &&
  cp -a siakad.smaitarafah.sch.id/.htaccess siakad.smaitarafah.sch.id_tmp/.htaccess &&
  if [ -e siakad.smaitarafah.sch.id/.well-known ]; then cp -a siakad.smaitarafah.sch.id/.well-known siakad.smaitarafah.sch.id_tmp/.well-known; fi
" < /tmp/siakad-deploy.tar.gz

echo "== 3/5 tukar folder (folder lama -> _old_<timestamp>) =="
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

echo "== 4/5 composer + cache =="
ssh -n -o ConnectTimeout=20 -o BatchMode=yes -o ServerAliveInterval=15 -o ServerAliveCountMax=6 nizhom "
  cd ~/public_html/siakad.smaitarafah.sch.id &&
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php &&
  composer install --no-dev --no-interaction --prefer-dist --no-progress 2>&1 | tail -2 &&
  php artisan migrate --force &&
  php artisan config:cache && php artisan view:cache &&
  test -d vendor && test -f vendor/autoload.php && echo '   vendor OK'
"

echo "== 5/5 verifikasi =="
KODE=$(curl -sk -o /dev/null -w '%{http_code}' --max-time 25 https://siakad.smaitarafah.sch.id/)
echo "   https://siakad.smaitarafah.sch.id -> HTTP $KODE"
if [ "$KODE" != "200" ]; then
  echo "   !! situs tidak 200. Cek: vendor ada? migrasi jalan? Jalankan langkah 4 manual."
fi
echo "SELESAI"
