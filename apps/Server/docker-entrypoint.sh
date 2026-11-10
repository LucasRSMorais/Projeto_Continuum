#!/bin/sh
set -eu

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

# Garantir que não reste um segundo MPM no ambiente do runtime
a2dismod -f mpm_event mpm_worker || true
rm -f /etc/apache2/mods-enabled/mpm_event.* \
      /etc/apache2/mods-enabled/mpm_worker.* \
      /etc/apache2/mods-enabled/mpm_prefork.* || true
a2enmod mpm_prefork || true

apache2ctl -t || exit 1

exec apache2-foreground