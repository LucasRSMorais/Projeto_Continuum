#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE SEGURO (NGINX + PHP-FPM) =========="

# Inicia o PHP-FPM em background
php-fpm -D

# Inicia o Nginx apontando para a configuração segura
echo "=== INICIANDO NGINX NA PORTA 80 ==="
exec nginx -g "daemon off;"