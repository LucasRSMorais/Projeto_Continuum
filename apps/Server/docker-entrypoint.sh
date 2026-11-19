
#!/bin/sh
set -eu

echo "========== INICIANDO APACHE =========="

echo "=== MODULOS MPM ==="
apache2ctl -M | grep mpm

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
