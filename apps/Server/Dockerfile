FROM php:8.5-apache

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

# Configuração dos módulos MPM do Apache feita no BUILD (como root)
RUN set -eux; \
    mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_prefork.load \
          /etc/apache2/mods-enabled/mpm_prefork.conf; \
    a2enmod mpm_prefork rewrite headers; \
    echo "=== MODULOS MPM ATIVOS ==="; \
    ls -l /etc/apache2/mods-enabled/mpm_*; \
    apache2ctl -M | grep mpm; \
    apache2ctl -t

# Configuração da porta 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

# Copia os arquivos do backend
COPY . /var/www/html/

# Permissões dos arquivos e pastas temporárias
RUN mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log \
    && chown -R www-data:www-data /var/www/html /tmp/apache2 \
    && chmod -R 755 /tmp/apache2

# Copia e prepara o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurações de segurança e cabeçalhos HTTP
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf

RUN a2enconf docker-security \
    && apache2ctl -t

# Porta utilizada pelo Railway
EXPOSE 8080

CMD ["/usr/local/bin/docker-entrypoint.sh"]