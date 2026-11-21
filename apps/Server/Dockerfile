FROM php:8.5-apache

# 1. Instalação limpa do Apache e remoção prévia de pacotes MPM conflitantes
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       apache2 \
       apache2-bin \
       apache2-data \
       apache2-utils \
       libcap2-bin \
    && apt-get purge -y apache2-mpm-event apache2-mpm-worker || true \
    && rm -rf /var/lib/apt/lists/*

# Instala o driver PDO para MySQL
RUN docker-php-ext-install pdo_mysql

# Configurações de segurança e sessão do PHP
RUN printf '%s\n' \
    'expose_php=Off' \
    'session.save_path=/tmp' \
    > /usr/local/etc/php/conf.d/zzzz-security.ini

# Variáveis de ambiente do Apache apontando para diretórios temporários (/tmp)
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

RUN mkdir -p /tmp/apache2/run \
    /tmp/apache2/lock \
    /tmp/apache2/log

# 2. Assegura que apenas o mpm_prefork está ativo
RUN rm -f /etc/apache2/mods-enabled/mpm_event.* \
          /etc/apache2/mods-enabled/mpm_worker.* \
          /etc/apache2/mods-available/mpm_event.* \
          /etc/apache2/mods-available/mpm_worker.* \
    && a2enmod mpm_prefork rewrite headers

# Configuração explícita da porta 80
RUN sed -i 's/Listen 8080/Listen 80/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:8080>/<VirtualHost *:80>/' \
       /etc/apache2/sites-available/000-default.conf

# Copia os arquivos do backend e ajusta as permissões para o www-data
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log \
    && chown -R www-data:www-data /tmp/apache2

# Copia e prepara o entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurações de segurança e cabeçalhos HTTP
COPY docker-security.conf /etc/apache2/conf-available/docker-security.conf
RUN a2enconf docker-security

# Permite que o usuário www-data abra a porta 80 sem privilégios de root
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/apache2

# Expõe a porta 80
EXPOSE 80

# Ativa o usuário seguro para todo o ciclo de vida do container
USER www-data

CMD ["/usr/local/bin/docker-entrypoint.sh"]