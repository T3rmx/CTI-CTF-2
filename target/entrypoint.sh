#!/bin/bash
set -e

echo "[*] T3rmx CTF - Starting..."

# Generate all passwords at runtime — never hardcoded in source
SECRETS=/run/secrets.env
cat > "$SECRETS" <<EOF
ADMIN_PASS=$(openssl rand -hex 12)
DEVELOPER_PASS=$(openssl rand -hex 12)
MWILSON_PASS=$(openssl rand -hex 12)
RJOHNSON_PASS=$(openssl rand -hex 12)
TGARCIA_PASS=$(openssl rand -hex 12)
DEV_OS_PASS=$(openssl rand -hex 12)
LAOUR_OS_PASS=$(openssl rand -hex 12)
MAIL_PASS=$(openssl rand -hex 12)
EOF
chmod 600 "$SECRETS"
source "$SECRETS"

echo "[*] Step 1: Initializing users..."
/opt/t3rmx/startup/init-users.sh

echo "[*] Step 2: Setting up directories..."
/opt/t3rmx/startup/init-dirs.sh

echo "[*] Step 3: Setting permissions..."
/opt/t3rmx/startup/init-perms.sh

echo "[*] Step 4: Initializing database..."
/opt/t3rmx/startup/init-database.sh

echo "[*] Step 5: Generating flags..."
/opt/t3rmx/startup/init-flags.sh

echo "[*] Step 6: Configuring environment..."
/opt/t3rmx/startup/init-env.sh

echo "[*] Step 7: Setting up SSH keys and starting SSH..."
/opt/t3rmx/startup/init-ssh.sh

echo "[*] Step 8: Starting Apache..."
service apache2 start

echo "[*] Step 9: Starting background services..."
/opt/t3rmx/startup/init-services.sh

echo "[*] T3rmx CTF is ready."
tail -f /dev/null
