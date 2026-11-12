
#!/bin/sh
set -eu

# Cria os diretorios temporarios do Apache
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Remove qualquer MPM diferente do prefork
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf

# Garante que o prefork esteja habilitado
a2enmod mpm_prefork

echo "=== MPM ATIVOS NO RUNTIME ==="
ls -l /etc/apache2/mods-enabled/mpm_*

# Valida a configuração do Apache
apache2ctl -t

echo "Apache validado com sucesso!"

exec apache2-foreground
