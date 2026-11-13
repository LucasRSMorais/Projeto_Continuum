FROM php:8.5-apache


# Instala o driver PDO para MySQL
RUN docker-php-ext-install pdo_mysql

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


RUN mkdir -p /tmp/apache2/run \
    /tmp/apache2/lock \
    /tmp/apache2/log

# Configuração dos módulos MPM do Apache feita no BUILD (como root)

# Configuração dos módulos MPM do Apache
# Configuração dos módulos MPM do Apache
RUN set -eux; \
    mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log; \
    a2dismod mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_prefork.load \
          /etc/apache2/mods-enabled/mpm_prefork.conf; \
    a2enmod mpm_prefork rewrite headers; \
    echo "=== MODULOS MPM ATIVOS ==="; \
    ls -la /etc/apache2/mods-enabled/mpm*; \
    apache2ctl -M | grep mpm; \
    apache2ctl -t


# Configuração da porta 80
RUN sed -i 's/Listen 8080/Listen 80/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:8080>/<VirtualHost *:80>/' \
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


RUN echo "=== MODULOS MPM ENCONTRADOS ===" \
    && ls -la /etc/apache2/mods-enabled/mpm* \
    && echo "=== REFERENCIAS A LOADMODULE ===" \
    && grep -RInE '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2


# Configurações de segurança e cabeçalhos HTTP
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf

RUN a2enconf docker-security \
    && apache2ctl -t


RUN echo "=== MODULOS ATIVOS NO FINAL ===" \
    && ls -l /etc/apache2/mods-enabled/mpm* \
    && apache2ctl -M | grep mpm

# Porta utilizada pelo Railway
EXPOSE 80



CMD ["/usr/local/bin/docker-entrypoint.sh"]

###