#!/bin/bash
set -e

# Application directories
mkdir -p /app/data
mkdir -p /var/www/public
mkdir -p /var/www/app
mkdir -p /var/www/templates
mkdir -p /var/www/static/css
mkdir -p /var/www/static/js
mkdir -p /var/www/static/images
mkdir -p /var/www/uploads
mkdir -p /var/www/app/sessions

# Developer home
mkdir -p /home/developer
chown developer:developer /home/developer

# Laour home
mkdir -p /home/laour
chown laour:laour /home/laour

# Maintenance path (for privesc)
mkdir -p /opt/t3rmx/services/maintenance/runtime/scripts
mkdir -p /opt/t3rmx/services/maintenance/runtime/logs
