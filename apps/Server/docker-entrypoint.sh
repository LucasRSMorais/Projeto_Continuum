
#!/bin/sh
set -eu

echo "========== CORRIGINDO MODULOS APACHE =========="

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Os módulos já são configurados no Dockerfile durante o BUILD.
# Não precisamos removê-los novamente durante a inicialização.

echo "=== MODULOS MPM RESTANTES ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== VALIDANDO APACHE ==="
apache2ctl -M
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
