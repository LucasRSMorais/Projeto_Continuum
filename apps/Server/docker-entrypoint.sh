#!/bin/sh
set -eu

echo "========== CORRIGINDO MODULOS APACHE =========="

# Cria as pastas e garante que o www-data é o dono delas
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log
chown -R www-data:www-data /tmp/apache2

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