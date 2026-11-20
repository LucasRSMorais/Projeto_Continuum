
FROM php:8.5-apache

# Atualiza os pacotes do Apache
RUN apt-get update \
    && apt-get install -y --only-upgrade --no-install-recommends \
       apache2 \
       apache2-bin \
       apache2-data \
       apache2-utils \
    && rm -rf /var/lib/apt/lists/*

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

# Cria os diretórios temporários
RUN mkdir -p /tmp/apache2/run \
    /tmp/apache2/lock \
    /tmp/apache2/log \
    && chown -R www-data:www-data /tmp/apache2

# Configura a porta 80
RUN sed -i 's/Listen 8080/Listen 80/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:8080>/<VirtualHost *:80>/' \
       /etc/apache2/sites-available/000-default.conf

# Copia todos os arquivos do backend
COPY . /var/www/html/

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /tmp/apache2

# Copia o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurações de segurança HTTP
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf

RUN a2enconf docker-security

# Instala ferramenta para permitir porta 80 sem root
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && setcap 'cap_net_bind_service=+ep' /usr/sbin/apache2 \
    && rm -rf /var/lib/apt/lists/*

# Mantém somente o MPM prefork
RUN set -eux; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_prefork.load \
          /etc/apache2/mods-enabled/mpm_prefork.conf; \
    a2enmod mpm_prefork; \
    apache2ctl -M | grep mpm; \
    apache2ctl -t

EXPOSE 80

# Executa como usuario nao-root
USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]
