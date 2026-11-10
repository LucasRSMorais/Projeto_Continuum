#!/bin/sh
set -eu

mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log

apache2ctl -t
exec apache2-foreground