#!/bin/sh
set -eu

# Cria os diretorios temporarios do Apache
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Os módulos são configurados durante o build; aqui apenas validamos a configuração.
mpm_modules=$(apache2ctl -M | grep ' mpm_')
if [ "$mpm_modules" != " mpm_prefork_module (shared)" ]; then
    echo "Configuracao MPM inesperada: $mpm_modules" >&2
    exit 1
fi

echo "MPM ativo: $mpm_modules"
apache2ctl -t

echo "Iniciando o Apache..."

exec apache2-foreground