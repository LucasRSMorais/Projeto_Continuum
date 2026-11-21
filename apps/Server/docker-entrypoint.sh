
#!/bin/sh
set -eu

echo "========== INICIANDO AMBIENTE SEGURO (NGINX + PHP-FPM) =========="

# Garante que os diretórios temporários do Nginx existam
mkdir -p /tmp/nginx/client_body_temp \
         /tmp/nginx/proxy_temp \
         /tmp/nginx/fastcgi_temp \
         /tmp/nginx/uwsgi_temp \
         /tmp/nginx/scgi_temp

# Inicia o PHP-FPM em background
php-fpm -D

# Inicia o Nginx em primeiro plano na porta 80
echo "=== INICIANDO NGINX NA PORTA 80 ==="
exec nginx -g "daemon off;"
