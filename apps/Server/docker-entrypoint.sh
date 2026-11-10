#!/bin/sh
set -eu

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

rm -f /etc/apache2/mods-enabled/mpm_event.* \
      /etc/apache2/mods-enabled/mpm_worker.* \
      /etc/apache2/mods-enabled/mpm_prefork.*

a2enmod mpm_prefork
apache2ctl -t

exec apache2-foreground