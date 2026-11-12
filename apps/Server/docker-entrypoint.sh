
#!/bin/sh
set -eu

echo "=== CORRIGINDO MODULOS MPM ==="

# Desabilita os MPMs conflitantes
a2dismod mpm_event || true
a2dismod mpm_worker || true

# Remove links residuais dos MPMs
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf

# Garante que somente o prefork esteja habilitado
a2enmod mpm_prefork

echo "=== MPM ATIVOS ==="
ls -la /etc/apache2/mods-enabled/mpm_*

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

exec apache2-foreground
