
#!/bin/sh
set -eu

echo "========== INICIANDO APACHE =========="

echo "=== USUARIO ATUAL ==="
whoami

echo "=== MODULOS MPM ATIVOS ==="
ls -la /etc/apache2/mods-enabled/mpm*

# Garante os diretorios temporarios
mkdir -p /tmp/apache2/run \
         /tmp/apache2/lock \
         /tmp/apache2/log

# Define o nome do servidor
echo "ServerName localhost" > /tmp/apache-servername.conf

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
