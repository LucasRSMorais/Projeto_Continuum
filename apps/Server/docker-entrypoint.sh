#!/bin/sh
set -eu

# Garante que os diretórios do Apache existam e pertençam ao usuário www-data
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log
chown -R www-data:www-data /tmp/apache2

# Testa a configuração do Apache
apache2ctl -t

# Inicia o servidor em primeiro plano
exec apache2-foreground