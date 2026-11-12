
#!/bin/sh
set -eu

echo "=== VALIDANDO MODULOS MPM ==="

echo "=== MODULOS HABILITADOS ==="
ls -la /etc/apache2/mods-enabled/mpm_*

echo "=== TESTANDO CONFIGURACAO ==="
apache2ctl -t

echo "=== VERIFICANDO MPM PREFORK ==="
if ! apache2ctl -M | grep -q 'mpm_prefork_module'; then
    echo "ERRO: mpm_prefork nao esta carregado."
    exit 1
fi

echo "=== INICIANDO APACHE ==="
exec apache2-foreground
