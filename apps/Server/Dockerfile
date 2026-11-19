FROM php:8.5-apache

# 1. Instala pacotes necessários e o libcap2-bin (essencial para o setcap)
RUN apt-get update \
    && apt-get install -y --only-upgrade --no-install-recommends \
       apache2 \
       apache2-bin \
       apache2-data \
       apache2-utils \
    && apt-get install -y --no-install-recommends libcap2-bin \
    && rm -rf /var/lib/apt/lists/*

# 2. Instala o driver PDO para MySQL
RUN docker-php-ext-install pdo_mysql

# 3. Configurações de segurança e sessão do PHP
RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# 4. Variáveis de ambiente do Apache
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

# 5. Configuração e limpeza dos módulos MPM do Apache (feita no BUILD como root)
RUN set -eux; \
    a2dismod mpm_event mpm_worker || true; \
    rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.*; \
    a2enmod mpm_prefork rewrite headers

# 6. Configuração da porta 80
RUN sed -i 's/Listen 8080/Listen 80/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:8080>/<VirtualHost *:80>/' \
       /etc/apache2/sites-available/000-default.conf

# 7. Copia os arquivos do backend e configurações
COPY . /var/www/html/
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
RUN a2enconf docker-security

# 8. Copia e prepara o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# 9. Permite que o Apache abra a porta 80 sendo www-data e ajusta permissões
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/apache2 \
    && mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log \
    && chown -R www-data:www-data /var/www/html /tmp/apache2 /var/log/apache2 /var/run/apache2

EXPOSE 80

# 10. Mantém o usuário www-data para a execução
USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]