#!/bin/bash
set -e

# Remove forçadamente qualquer vestígio de mpm_event ou mpm_worker antes de subir o Apache
rm -f /etc/apache2/mods-enabled/mpm_event.load
rm -f /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load
rm -f /etc/apache2/mods-enabled/mpm_worker.conf


if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
    ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
fi


mkdir -p /tmp/apache2/run /tmp/apache2/lock /tmp/apache2/log
chown -R www-data:www-data /tmp/apache2


exec apache2-foreground