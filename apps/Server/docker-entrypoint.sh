#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE SEGURO =========="
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== INICIANDO APACHE DIRETAMENTE NA PORTA 80 ==="
exec /usr/sbin/apache2 -D FOREGROUND