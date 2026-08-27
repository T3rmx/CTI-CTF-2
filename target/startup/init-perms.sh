#!/bin/bash
set -e

# Web root
chown -R www-data:www-data /var/www
chmod -R 755 /var/www

# Upload directory - writable by www-data (under DocumentRoot)
chown -R www-data:www-data /var/www/public/uploads
chmod 777 /var/www/public/uploads

# App data
chown -R www-data:www-data /app/data
chmod 755 /app/data

# Sessions
chmod 733 /var/www/app/sessions

# Developer home
chown developer:developer /home/developer
chmod 700 /home/developer

# Laour home
chown laour:laour /home/laour
chmod 700 /home/laour

# Maintenance path - the 777 misconfiguration
chown -R root:root /opt/t3rmx/services/maintenance
chmod 755 /opt/t3rmx/services
chmod 755 /opt/t3rmx/services/maintenance
chmod 755 /opt/t3rmx/services/maintenance/runtime
chmod 777 /opt/t3rmx/services/maintenance/runtime/scripts
chmod 755 /opt/t3rmx/services/maintenance/runtime/logs
