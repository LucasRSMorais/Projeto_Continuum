
FROM php:8.2-apache

# Configurações de segurança e sessão do PHP
RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# Variáveis de ambiente do Apache
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

# Configuração dos módulos MPM do Apache
RUN set -eux; \
    mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log; \
    printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf; \
    for module in mpm_event mpm_worker mpm_prefork; do \
        a2dismod "$module" || true; \
    done; \
    rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf; \
    a2enmod mpm_prefork rewrite headers; \
    echo "=== MODULOS MPM ATIVOS ==="; \
    find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*' -ls; \
    echo "=== MODULOS MPM CARREGADOS ==="; \
    apache2ctl -M | grep 'mpm'; \
    a2enconf servername; \
    docker-php-ext-install pdo pdo_mysql mysqli; \
    test "$(find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' | wc -l)" -eq 1; \
    apache2ctl -t

# Configuração da porta 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

# Copia os arquivos do backend para o Apache
COPY . /var/www/html/

# Permissões dos arquivos
RUN chown -R www-data:www-data /var/www/html /tmp/apache2

# Copia e prepara o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurações de segurança e cabeçalhos HTTP para o ZAP
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf

RUN a2enconf docker-security \
    && apache2ctl -t

# Porta utilizada pelo Railway
EXPOSE 8080

# Executa como usuário não-root
USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]
