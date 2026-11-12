FROM php:8.2-apache

# Configurações de segurança e sessão do PHP
RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# Variáveis de ambiente para rodar o Apache em diretórios temporários
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

# Executa como ROOT a configuração dos módulos do Apache


# Mantém o MPM padrão da imagem oficial PHP Apache
RUN set -eux; \
    mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log; \
    printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf; \
    a2enmod rewrite headers; \
    a2enconf servername; \
    docker-php-ext-install pdo pdo_mysql mysqli; \
    apache2ctl -t; \
    apache2ctl -M | grep -q 'mpm_prefork_module'; \
    ! apache2ctl -M | grep -Eq 'mpm_(event|worker)_module'




# Altera a porta padrão do Apache de 80 para 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
       /etc/apache2/sites-available/000-default.conf

# Copia todos os arquivos de dentro da pasta Server para a raiz do Apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html /tmp/apache2

# Copia e prepara o script de entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Copia e habilita as configurações de segurança (ZAP headers)
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
RUN a2enconf docker-security \
    && apache2ctl -t

EXPOSE 8080

# Muda para o usuário não-root apenas no final
USER www-data
CMD ["/usr/local/bin/docker-entrypoint.sh"]