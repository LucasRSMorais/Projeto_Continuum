#!/bin/sh
set -eu

echo "========== CORRIGINDO MODULOS APACHE =========="

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Remove os MPMs conflitantes de forma segura (ignora se der erro de permissão se já estiver limpo)
rm -f /etc/apache2/mods-enabled/mpm_event.load || true
rm -f /etc/apache2/mods-enabled/mpm_event.conf || true
rm -f /etc/apache2/mods-enabled/mpm_worker.load || true
rm -f /etc/apache2/mods-enabled/mpm_worker.conf || true

echo "=== MODULOS MPM RESTANTES ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== VALIDANDO APACHE ==="
apache2ctl -M
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground