
#!/bin/sh
set -eu

echo "========== DIAGNOSTICO APACHE =========="

echo "=== USUARIO ATUAL ==="
whoami

echo "=== MODULOS MPM ATIVOS ==="
ls -la /etc/apache2/mods-enabled/mpm* || true

echo "=== REFERENCIAS MPM NA CONFIGURACAO ==="
grep -RInE '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2

echo "=== FIM DO DIAGNOSTICO ==="

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
