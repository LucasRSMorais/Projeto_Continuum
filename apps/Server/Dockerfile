FROM php:8.2-apache

# Define variáveis de diretório do Apache
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log


RUN a2dismod -f mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite headers \
    && docker-php-ext-install pdo pdo_mysql mysqli


RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf


COPY . /var/www/html/


RUN mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log \
    && chown -R www-data:www-data /var/www/html /tmp/apache2

EXPOSE 8080


COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]