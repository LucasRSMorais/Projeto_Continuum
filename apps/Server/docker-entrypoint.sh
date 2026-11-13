
#!/bin/sh
set -eu

echo "========== DIAGNOSTICO APACHE =========="

echo "=== MODULOS MPM EM mods-enabled ==="
ls -la /etc/apache2/mods-enabled/ | grep mpm || true

echo "=== TODOS OS LOADMODULE MPM ==="
grep -RInE '^[[:space:]]*LoadModule[[:space:]]+mpm_' \
    /etc/apache2 || true

echo "=== CONFIGURACAO DOS MODULOS ==="
ls -la /etc/apache2/mods-available/mpm*

echo "=== VALIDACAO DOS DIRETORIOS ==="
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== TESTE DO APACHE ==="
apache2ctl -M

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
