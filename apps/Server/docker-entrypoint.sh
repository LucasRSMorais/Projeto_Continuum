
#!/bin/sh
set -eu

echo "========== CORRIGINDO MODULOS APACHE =========="

# Garante que os diretórios temporários existam
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log



echo "=== MODULOS MPM RESTANTES ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== VALIDANDO APACHE ==="
apache2ctl -M
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
