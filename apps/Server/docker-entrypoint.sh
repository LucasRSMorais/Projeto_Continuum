
#!/bin/sh
set -eu

echo "=== INICIANDO BACKEND CONTINUUM ==="

# Cria os diretórios temporários do Apache
mkdir -p /tmp/apache2/run \
         /tmp/apache2/lock \
         /tmp/apache2/log

# Verifica a configuração do Apache
echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "Apache validado com sucesso!"

# Inicia o Apache
exec apache2-foreground
