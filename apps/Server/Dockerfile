FROM php:8.2-apache

# Define variáveis de diretório do Apache para evitar erros de permissão e PID
ENV APACHE_RUN_DIR=/tmp/apache2/run \
    APACHE_LOCK_DIR=/tmp/apache2/lock \
    APACHE_PID_FILE=/tmp/apache2/run/apache2.pid \
    APACHE_LOG_DIR=/tmp/apache2/log

# Remove forçadamente todos os MPMs conflitantes e ativa o prefork com as extensões essenciais
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2dismod -f mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite headers \
    && docker-php-ext-install pdo pdo_mysql mysqli




# Configura cabeçalhos HTTP de segurança

# Configura cabeçalhos HTTP de segurança
RUN printf '%s\n' \
    'Header always set X-Content-Type-Options "nosniff"' \
    'Header always set X-Frame-Options "SAMEORIGIN"' \
    'Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"' \
    'Header always set Content-Security-Policy "default-src '\''self'\''; script-src '\''self'\''; style-src '\''self'\'' '\''unsafe-inline'\''; img-src '\''self'\'' data: https:; font-src '\''self'\'' data: https:; connect-src '\''self'\'' https://development-continuum.up.railway.app https://dev-projcontinuum.up.railway.app; object-src '\''none'\''; base-uri '\''self'\''; form-action '\''self'\''; frame-ancestors '\''self'\''"' \
    'Header always set Cross-Origin-Opener-Policy "same-origin"' \
    > /etc/apache2/conf-available/security-headers.conf \
    && a2enconf security-headers


# Oculta a versão do PHP no cabeçalho HTTP
RUN printf '%s\n' 'expose_php=Off' \
    > /usr/local/etc/php/conf.d/security.ini

# Reduz informações divulgadas pelo Apache
RUN printf '%s\n' \
    'ServerTokens Prod' \
    'ServerSignature Off' \
    > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening


# Ajusta a porta padrão do Apache para 8080 (exigido pelo Railway)
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Copia os arquivos da aplicação para o diretório web do Apache
COPY . /var/www/html/

EXPOSE 8080

# Configura e prepara o script de inicialização (entrypoint)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]