#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE COMO WWW-DATA =========="

# Cria apenas as pastas em /tmp onde o www-data tem total acesso
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground