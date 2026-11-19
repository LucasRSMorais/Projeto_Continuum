
#!/bin/sh
set -eu

echo "========== INICIANDO APACHE =========="

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== MODULOS MPM ATIVOS ==="
apache2ctl -M | grep mpm

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
