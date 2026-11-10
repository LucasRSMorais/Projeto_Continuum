#!/bin/sh
set -eu

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

a2dismod -f mpm_event mpm_worker || true
a2enmod mpm_prefork headers rewrite || true

apache2ctl -t
exec apache2-foreground