#!/bin/sh
set -eu

echo "========== CORRIGINDO MODULOS APACHE =========="

# Remove os links dos módulos MPM que não devem estar ativos
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf

echo "=== MODULOS MPM RESTANTES ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== VALIDANDO APACHE ==="
apache2ctl -M
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground