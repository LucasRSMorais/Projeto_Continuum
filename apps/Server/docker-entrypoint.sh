
#!/bin/sh
set -eu

echo "=== VALIDANDO MODULOS MPM ==="

ls -la /etc/apache2/mods-enabled/mpm_*

apache2ctl -t

if ! apache2ctl -M | grep -q 'mpm_prefork_module'; then
    echo "ERRO: mpm_prefork nao esta carregado."
    exit 1
fi

exec apache2-foreground
