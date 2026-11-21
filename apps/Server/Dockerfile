# CACHE-BUST: 2026-11-21-NGINX-PID-FIX
FROM php:8.5-fpm

# 1. Instala Nginx e dependências do sistema
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       nginx \
       libcap2-bin \
    && rm -rf /var/lib/apt/lists/*

# Instala o driver PDO para MySQL
RUN docker-php-ext-install pdo_mysql

# Configurações de segurança e sessão do PHP
RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# 2. Configura o Nginx para usar /tmp para o PID, logs e temporários (evitando Permission Denied)
RUN sed -i 's|pid /run/nginx.pid;|pid /tmp/nginx.pid;|' /etc/nginx/nginx.conf \
    && mkdir -p /tmp/nginx/client_body_temp \
                /tmp/nginx/proxy_temp \
                /tmp/nginx/fastcgi_temp \
                /tmp/nginx/uwsgi_temp \
                /tmp/nginx/scgi_temp \
    && chown -R www-data:www-data /tmp/nginx /var/log/nginx /var/lib/nginx

# Copia os arquivos do projeto
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Configura o site padrão do Nginx para a porta 80 apontando para o projeto
RUN echo 'server {\n\
    listen 80;\n\
    root /var/www/html;\n\
    index index.php index.html;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
        include snippets/fastcgi-php.conf;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
    }\n\
}' > /etc/nginx/sites-available/default

# Copia e prepara o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Permite que o usuário www-data abra a porta 80 e 9000 sem privilégios de root
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/nginx \
    && setcap 'cap_net_bind_service=+ep' /usr/local/sbin/php-fpm

# Expõe a porta 80
EXPOSE 80

# Roda a aplicação com o usuário seguro
USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]