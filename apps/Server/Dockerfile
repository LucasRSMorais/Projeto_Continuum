
#FROM php:8.2-apache

FROM php:8.2-apache

RUN printf '%s\n' 'expose_php=Off' > /usr/local/etc/php/conf.d/zzzz-security.ini

RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# Define variáveis de diretório do Apache para evitar erros de permissão e PID
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data



RUN set -eux; \
    a2dismod -f mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_worker.conf; \
    a2enmod mpm_prefork rewrite headers; \
    apache2ctl -M 2>&1 | tee /tmp/apache-modules.txt; \
    test "$(grep -Ec 'mpm_(event|worker|prefork)_module' /tmp/apache-modules.txt)" -eq 1; \
    grep -q 'mpm_prefork_module' /tmp/apache-modules.txt; \
    docker-php-ext-install pdo pdo_mysql mysqli


# Remove forçadamente todos os MPMs conflitantes e ativa o prefork com as extensões essenciais
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2dismod -f mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite headers \
    && docker-php-ext-install pdo pdo_mysql mysqli

# Ajusta a porta padrão do Apache para 8080 (exigido pelo Railway)
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Copia os arquivos da aplicação para o diretório web do Apache
COPY . /var/www/html/

# Ajusta permissões sem elevar o processo do container
RUN mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log \
    && chown -R www-data:www-data /var/www/html /tmp/apache2

EXPOSE 8080

# Configura e prepara o script de inicialização (entrypoint)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
RUN a2enconf docker-security

USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]
