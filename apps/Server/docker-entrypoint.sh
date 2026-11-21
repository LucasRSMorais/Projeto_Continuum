#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE =========="
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== INICIANDO APACHE NA PORTA 80 ==="
exec apache2-foreground