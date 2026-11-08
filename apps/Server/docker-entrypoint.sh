#!/bin/bash
mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log
chown -R www-data:www-data /tmp/apache2

exec apache2-foreground