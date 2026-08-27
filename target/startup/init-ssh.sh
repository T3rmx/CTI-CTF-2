#!/bin/bash
set -e

# ---------- Host keys (sshd will refuse to start without them) ----------
ssh-keygen -A >/dev/null 2>&1 || true
mkdir -p /run/sshd

# ---------- Developer keypair (stolen via the web app to pivot) ----------
mkdir -p /var/www/app
if [ ! -f /var/www/app/developer_key ]; then
    ssh-keygen -t ed25519 -f /var/www/app/developer_key -N "" -C "developer@t3rmx" >/dev/null 2>&1
fi

chown www-data:www-data /var/www/app/developer_key /var/www/app/developer_key.pub
chmod 600 /var/www/app/developer_key
chmod 644 /var/www/app/developer_key.pub

# ---------- authorized_keys for developer ----------
mkdir -p /home/developer/.ssh
cp -f /var/www/app/developer_key.pub /home/developer/.ssh/authorized_keys
chown -R developer:developer /home/developer/.ssh
chmod 700 /home/developer/.ssh
chmod 600 /home/developer/.ssh/authorized_keys

# ---------- Start sshd and verify it stays up ----------
service ssh start >/dev/null 2>&1 || true
for i in 1 2 3 4 5; do
    if pgrep -x sshd >/dev/null 2>&1; then
        break
    fi
    sleep 1
    service ssh start >/dev/null 2>&1 || true
done

if pgrep -x sshd >/dev/null 2>&1; then
    echo "[+] sshd is running on port 22"
else
    echo "[!] sshd failed to start"
fi