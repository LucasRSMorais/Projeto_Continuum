#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE =========="

# Garante que as pastas temporárias de execução do Apache existem e pertencem ao usuário correto
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

echo "=== INICIANDO APACHE NA PORTA 80 ==="

# Inicia o Apache em primeiro plano com o usuário seguro
exec apache2-foreground