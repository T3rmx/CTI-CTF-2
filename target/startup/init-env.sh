#!/bin/bash
set -e

source /run/secrets.env

# Create .env file with realistic configuration
cat > /var/www/app/.env <<EOF
APP_NAME=T3rmx Portal
APP_ENV=production
APP_DEBUG=false
APP_URL=http://127.0.0.1:8080
APP_KEY=$(openssl rand -hex 32)

DB_DRIVER=sqlite
DB_PATH=/app/data/database.sqlite3

MAIL_HOST=mail.t3rmx.local
MAIL_PORT=587
MAIL_USER=noreply@t3rmx.com
MAIL_PASS=${MAIL_PASS}

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

SESSION_DRIVER=file
SESSION_LIFETIME=60

CACHE_DRIVER=file
CACHE_TTL=3600

LOG_CHANNEL=daily
LOG_PATH=/var/www/app/logs

# SSH deployment key for automated syncing
# DO NOT COMMIT OR SHARE THIS KEY
SSH_KEY_PATH=/var/www/app/developer_key
DEPLOY_USER=developer
DEPLOY_HOST=buildserver.t3rmx.local
DEPLOY_PORT=22

BACKUP_SCHEDULE=0 2 * * *
BACKUP_RETENTION=30
BACKUP_PATH=/opt/t3rmx/backups
EOF

chown www-data:www-data /var/www/app/.env
chmod 644 /var/www/app/.env

# developer key permissions are handled by init-ssh.sh
