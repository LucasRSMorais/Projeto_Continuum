FROM php:8.2-apache

RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

RUN set -eux; \
    mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log; \
    printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf; \
    a2dismod -f mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.* \
          /etc/apache2/mods-enabled/mpm_prefork.*; \
    a2enmod mpm_prefork rewrite headers; \
    a2enconf servername; \
    docker-php-ext-install pdo pdo_mysql mysqli; \
    apache2ctl -t

RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html /tmp/apache2

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
RUN a2enconf docker-security; \
    apache2ctl -t; \
    modules="$(apache2ctl -M 2>&1)"; \
    printf '%s\n' "$modules"; \
    test "$(printf '%s\n' "$modules" | grep -Ec 'mpm_(event|worker|prefork)_module')" -eq 1; \
    printf '%s\n' "$modules" | grep -q 'mpm_prefork_module'

EXPOSE 8080

CMD ["/usr/local/bin/docker-entrypoint.sh"]