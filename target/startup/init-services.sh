#!/bin/bash

source /run/secrets.env

# Create backup cron job - runs as root, executes backup script
cat > /etc/cron.d/t3rmx-maintenance << 'CRON'
# T3rmx nightly maintenance - do not disable
0 2 * * * root /opt/t3rmx/services/maintenance/runtime/scripts/cleanup.sh >/dev/null 2>&1
CRON
chmod 644 /etc/cron.d/t3rmx-maintenance

# The cleanup script that root executes
cat > /opt/t3rmx/services/maintenance/runtime/scripts/cleanup.sh << 'SCRIPT'
#!/bin/bash
# T3rmx Maintenance - Temporary file cleanup
LOGDIR="/opt/t3rmx/services/maintenance/runtime/logs"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
echo "[$TIMESTAMP] Maintenance run started" >> "$LOGDIR/maintenance.log"
find /tmp -name "t3rmx_*" -mtime +7 -delete 2>/dev/null || true
if [ -f "$LOGDIR/maintenance.log" ]; then
    tail -1000 "$LOGDIR/maintenance.log" > "$LOGDIR/maintenance.log.tmp"
    mv "$LOGDIR/maintenance.log.tmp" "$LOGDIR/maintenance.log"
fi
echo "[$TIMESTAMP] Maintenance run completed" >> "$LOGDIR/maintenance.log"
SCRIPT
chmod +x /opt/t3rmx/services/maintenance/runtime/scripts/cleanup.sh

# Backup configuration accessible to developer (lateral movement clue)
mkdir -p /var/www/app/config
cat > /var/www/app/config/backup.ini <<EOF
; T3rmx Backup Configuration
; Last updated by: jsmith (developer)
; Contact: laour@t3rmx for ops-related changes

[backup_server]
host = buildserver.t3rmx.local
port = 22
user = laour
; laour's credentials for backup sync
password = ${LAOUR_OS_PASS}

[schedule]
full_backup = 0 2 * * 0
incremental = 0 2 * * 1-6
retention_days = 30

[storage]
local_path = /opt/t3rmx/backups
remote_path = /backup/t3rmx
EOF

chown developer:developer /var/www/app/config/backup.ini
chmod 640 /var/www/app/config/backup.ini

# decoy files
echo "# T3rmx Internal - Development Notes" > /opt/t3rmx/services/maintenance/runtime/logs/notes.txt
echo "# Remember to update the backup rotation policy" >> /opt/t3rmx/services/maintenance/runtime/logs/notes.txt
echo "# Contact laour for ops-related changes" >> /opt/t3rmx/services/maintenance/runtime/logs/notes.txt

echo "# Old development configuration - deprecated" > /opt/t3rmx/services/deprecated.conf
echo "# This file is no longer used" >> /opt/t3rmx/services/deprecated.conf
echo "# Last modified: 2023-11-15" >> /opt/t3rmx/services/deprecated.conf
