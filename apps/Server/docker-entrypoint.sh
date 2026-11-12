
#!/bin/sh
set -eu

echo "=== INICIANDO BACKEND CONTINUUM ==="

# Cria os diretórios temporários
mkdir -p /tmp/apache2/run \
         /tmp/apache2/lock \
         /tmp/apache2/log

echo "=== VERIFICANDO MPM ==="
apache2ctl -M | grep mpm_prefork_module

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "Apache validado com sucesso!"

exec apache2-foreground
