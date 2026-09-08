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
  cp -a siakad.smaitarafah.sch.id/.htaccess siakad.smaitarafah.sch.id_tmp/.htaccess
" < /tmp/siakad-deploy.tar.gz

echo "== 3/5 tukar folder (folder lama -> _old_<timestamp>) =="
ssh -o ConnectTimeout=20 -o BatchMode=yes nizhom "
  cd ~/public_html &&
  TS=\$(date +%Y%m%d_%H%M%S) &&
  mv siakad.smaitarafah.sch.id siakad.smaitarafah.sch.id_old_\$TS &&
  mv siakad.smaitarafah.sch.id_tmp siakad.smaitarafah.sch.id &&
  cd siakad.smaitarafah.sch.id &&
  chmod -R u+rwX storage bootstrap/cache &&
  echo \"   backup lama: siakad.smaitarafah.sch.id_old_\$TS\"
"

echo "== 4/5 composer + cache =="
ssh -o ConnectTimeout=20 -o BatchMode=yes nizhom "
  cd ~/public_html/siakad.smaitarafah.sch.id &&
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php &&
  composer install --no-dev --no-interaction --prefer-dist --no-progress 2>&1 | tail -2 &&
  php artisan migrate --force &&
  php artisan config:cache && php artisan view:cache
"

echo "== 5/5 verifikasi =="
curl -sk -o /dev/null -w "   https://siakad.smaitarafah.sch.id -> HTTP %{http_code}\n" --max-time 25 https://siakad.smaitarafah.sch.id/
echo "SELESAI"
