FROM php:8.2-apache

# ... (suas configurações de php.ini e variáveis de ambiente) ...

# Executa como ROOT a configuração dos módulos do Apache
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
RUN a2enconf docker-security \
    && apache2ctl -t

EXPOSE 8080

# SÓ MUDA PARA O USUÁRIO NÃO-ROOT NO FINAL DE TUDO
USER www-data
CMD ["/usr/local/bin/docker-entrypoint.sh"]