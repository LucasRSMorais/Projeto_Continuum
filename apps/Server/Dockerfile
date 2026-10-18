FROM php:8.2-apache

ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log

RUN a2dismod mpm_prefork mpm_worker \
    && a2enmod mpm_event rewrite \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' 'session.save_path=/tmp' > /usr/local/etc/php/conf.d/zzz-session-path.ini

COPY api/ /var/www/html/api/
COPY config/ /var/www/html/config/
COPY vendor/ /var/www/html/vendor/
COPY index.php /var/www/html/index.php
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh




RUN a2dismod mpm_prefork mpm_worker && a2enmod mpm_event





RUN a2enconf docker-security \
    && sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html


EXPOSE 8080

CMD ["/usr/local/bin/docker-entrypoint.sh"]
