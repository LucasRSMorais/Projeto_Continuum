
#!/bin/sh
set -eu

echo "=== INICIANDO BACKEND CONTINUUM ==="

mkdir -p /tmp/apache2/run \
         /tmp/apache2/lock \
         /tmp/apache2/log

echo "=== ARQUIVOS MPM HABILITADOS ==="
ls -la /etc/apache2/mods-enabled/mpm_* || true

echo "=== DECLARACOES LOADMODULE MPM ==="
grep -RniE '^[[:space:]]*LoadModule[[:space:]]+mpm_' \
    /etc/apache2 2>/dev/null || true

echo "=== VALIDANDO APACHE ==="
apache2ctl -t

echo "Apache validado com sucesso!"

exec apache2-foreground
