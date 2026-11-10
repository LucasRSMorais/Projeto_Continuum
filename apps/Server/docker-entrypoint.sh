#!/bin/sh
set -eu

echo "=== APACHE_CONFDIR ==="
printf '%s\n' "${APACHE_CONFDIR:-não definido}"

echo "=== Links MPM habilitados ==="
ls -l /etc/apache2/mods-enabled/mpm* 2>&1 || true

echo "=== Diretivas LoadModule MPM encontradas ==="
grep -R -n -E '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2 2>&1 || true

echo "=== Teste de configuração Apache ==="
apache2ctl -t

exec apache2-foreground