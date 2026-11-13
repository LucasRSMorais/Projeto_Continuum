#!/bin/sh
set -eu

echo "========== DIAGNOSTICO RUNTIME =========="

echo "=== MODULOS MPM ATIVOS ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== REFERENCIAS MPM ==="
grep -RInE '^[[:space:]]*LoadModule[[:space:]]+mpm_' \
    /etc/apache2/mods-enabled/ || true

echo "=== TESTE APACHE ==="
apache2ctl -M

echo "=== INICIANDO APACHE ==="
exec apache2-foreground