#!/bin/bash
# TARIK PERUBAHAN DARI SERVER → KLON LOKAL (1 Okt 2026)
# =====================================================================
# Dipakai bila pengaman deploy (langkah 1b/6) melaporkan ada berkas di server
# yang BERUBAH/BARU — mis. karena ada yang mengedit langsung di hosting
# (hpanel / sesi Telegram). Skrip ini mengunduh berkas itu ke folder
# `_dari-server/` beserta daftar perbedaannya, supaya bisa DIGABUNG ke repo
# (bukan ditimpa mentah-mentah).
#
# Pemakaian:
#   bash tarik-dari-server.sh              # unduh semua berkas yang berbeda
#   bash tarik-dari-server.sh app/Support/SandiOrtu.php   # hanya berkas tertentu
#
# Hasil:  _dari-server/<path>      salinan dari server
#         _dari-server/RINGKASAN.txt daftar + perintah diff untuk memeriksa
# =====================================================================
set -euo pipefail

SERVER_DIR='~/public_html/siakad.smaitarafah.sch.id'
TUJUAN="_dari-server"
SUMBER_LOKAL="${SUMBER_LOKAL:-/d/SIAKAD-SMAITA}"
DIR_SERUPA="app resources routes database config public"

mkdir -p "$TUJUAN"

if [ "$#" -gt 0 ]; then
  BERKAS="$*"
  echo "== memeriksa berkas yang diminta =="
else
  echo "== mencari berkas yang berbeda antara server dan kode lokal =="
  TEMP=$(mktemp -d)
  ssh -n -o ConnectTimeout=20 nizhom "cd $SERVER_DIR && find $DIR_SERUPA -type f -not -path 'public/build/*' -not -name '.manifest-deploy.md5' -exec md5sum {} +" | tr -d '\r' | LC_ALL=C sort -k2 > "$TEMP/md5-server.txt"
  ( cd "$SUMBER_LOKAL" && find $DIR_SERUPA -type f -not -path 'public/build/*' -not -name '.manifest-deploy.md5' -exec md5sum {} + \
      | sed -E 's/^([0-9a-f]{32}) \*(.*)$/\1  \2/' | tr -d '\r' | LC_ALL=C sort -k2 ) > "$TEMP/md5-lokal.txt"

  awk '{print $2}' "$TEMP/md5-lokal.txt" | LC_ALL=C sort > "$TEMP/nama-lokal.txt"
  awk '{print $2}' "$TEMP/md5-server.txt" | LC_ALL=C sort > "$TEMP/nama-server.txt"

  # berkas baru di server + berkas yang isinya berbeda
  LC_ALL=C comm -13 "$TEMP/nama-lokal.txt" "$TEMP/nama-server.txt" > "$TEMP/baru.txt"
  LC_ALL=C join -1 2 -2 2 -o 1.2,1.1,2.1 "$TEMP/md5-lokal.txt" "$TEMP/md5-server.txt" | awk '$2 != $3 {print $1}' > "$TEMP/beda.txt"

  BERKAS=$(cat "$TEMP/baru.txt" "$TEMP/beda.txt" | LC_ALL=C sort -u)
  echo "   berkas baru di server : $(wc -l < "$TEMP/baru.txt")"
  echo "   berkas berbeda isi    : $(wc -l < "$TEMP/beda.txt")"
  rm -rf "$TEMP"
fi

if [ -z "${BERKAS:-}" ]; then
  echo "   -> tidak ada perbedaan. Kode lokal dan server sudah sinkron."
  exit 0
fi

: > "$TUJUAN/RINGKASAN.txt"
for f in $BERKAS; do
  mkdir -p "$TUJUAN/$(dirname "$f")"
  if scp -q -o ConnectTimeout=20 "nizhom:$SERVER_DIR/$f" "$TUJUAN/$f" 2>/dev/null; then
    echo "   diunduh: $f"
    echo "$f" >> "$TUJUAN/RINGKASAN.txt"
  else
    echo "   (tidak ada di server / gagal): $f"
  fi
done

{
  echo "BERKAS YANG DITARIK DARI SERVER ($(date '+%d/%m/%Y %H:%M'))"
  echo "======================================================="
  echo "Periksa bedanya, lalu SALIN yang benar ke repo lokal:"
  echo
  while read -r f; do
    [ -n "$f" ] || continue
    echo "  diff -u \"$SUMBER_LOKAL/$f\" \"$TUJUAN/$f\""
  done < "$TUJUAN/RINGKASAN.txt"
  echo
  echo "Setelah digabung: hapus folder $TUJUAN, lalu jalankan deploy seperti biasa."
} >> "$TUJUAN/RINGKASAN.txt"

echo
echo "== selesai. Baca $TUJUAN/RINGKASAN.txt lalu bandingkan tiap berkas. =="
