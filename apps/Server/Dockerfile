# Usar a imagem oficial do PHP com Apache

#FROM php:8.2-apache

# Habilitar o mod_rewrite do Apache (essencial se você usa rotas na sua API)
#RUN a2enmod rewrite

# Instalar as extensões do PHP necessárias para conectar ao MySQL
#RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copiar todos os arquivos da pasta Server para o diretório padrão do Apache
#COPY . /var/www/html/

# Ajustar as permissões para que o Apache consiga ler os arquivos
#RUN chown -R www-data:www-data /var/www/html/

FROM php:8.2-apache



RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql mysqli

COPY . /var/www/html/


RUN chown -R www-data:www-data /var/www/html/


COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh



CMD ["/usr/local/bin/docker-entrypoint.sh"]

