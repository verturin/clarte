#!/bin/sh
# test_ordre_cache.sh (1.4.3) — ordre d’affichage après purge du cache.
# Usage : sh test_ordre_cache.sh <style 1> <style 2>   (identifiants de style ; ex. 2 1 puis 1 2)
# order.sh first second : purge, affiche le style « first » puis « second » (invité, ?style=), rapporte code HTTP et traces de Clarté
cd "${PHPBB:-/home/claude/phpbb/www}" && php bin/phpbbcli.php cache:purge >/dev/null
for id in "$1" "$2"; do
  code=$(curl -s -m 20 -o ${TMPDIR:-/tmp}/clarte-ordre.html -w "%{http_code}" "http://127.0.0.1:8080/index.php?style=$id")
  vars=$(grep -c 'id="clarte-vars"' ${TMPDIR:-/tmp}/clarte-ordre.html); btn=$(grep -c 'clarte-mode-toggle' ${TMPDIR:-/tmp}/clarte-ordre.html); dm=$(grep -c 'data-clarte-mode' ${TMPDIR:-/tmp}/clarte-ordre.html)
  err=$(grep -o 'Unable to find template[^<]*' ${TMPDIR:-/tmp}/clarte-ordre.html | head -1)
  echo "  style $id : HTTP $code, clarte-vars=$vars, bouton=$btn, data-clarte-mode=$dm $err"
done
