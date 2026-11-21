#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE SEGURO =========="
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Força a desativação e limpeza de outros MPMs em tempo de execução
a2dismod -f mpm_event mpm_worker 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
a2enmod -m mpm_prefork 2>/dev/null || a2enmod mpm_prefork

echo "=== INICIANDO APACHE DIRETAMENTE NA PORTA 80 ==="
exec /usr/sbin/apache2 -D FOREGROUND