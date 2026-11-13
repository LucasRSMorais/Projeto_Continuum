#!/bin/sh
set -eu

# Cria os diretorios temporarios do Apache
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

exec apache2-foreground