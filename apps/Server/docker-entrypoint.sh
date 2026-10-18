#!/bin/sh
set -eu


PORT="${PORT:-8080}"


sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/Listen 8080/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/<VirtualHost \*:8080>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf




mkdir -p "$APACHE_RUN_DIR" "$APACHE_LOCK_DIR" "$APACHE_LOG_DIR"
exec apache2-foreground

