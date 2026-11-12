
#!/bin/sh
set -eu

# Cria os diretorios temporarios do Apache
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== MPM ATIVOS NO RUNTIME ==="
ls -l /etc/apache2/mods-enabled/mpm_*

# Valida a configuração do Apache
apache2ctl -t

echo "Apache validado com sucesso!"

exec apache2-foreground
