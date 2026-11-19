#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE COMO WWW-DATA =========="

# Garante que as pastas temporárias existem e têm permissão
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== VALIDANDO CONFIGURACAO DO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground