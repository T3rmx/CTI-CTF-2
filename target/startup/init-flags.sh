#!/bin/bash
set -e

FLAG_DIR="/var/www/app/flags"
mkdir -p "$FLAG_DIR"

# Generate secure random flags — TCI format, 16 hex chars
DEVELOPER_FLAG="TCI{$(openssl rand -hex 8)}"
LAOUR_FLAG="TCI{$(openssl rand -hex 8)}"
ROOT_FLAG="TCI{$(openssl rand -hex 8)}"

# Write flags
echo "$DEVELOPER_FLAG" > /home/developer/user.txt
echo "$LAOUR_FLAG" > /home/laour/user.txt
echo "$ROOT_FLAG" > /root/root.txt

# Set ownership
chown developer:developer /home/developer/user.txt
chown laour:laour /home/laour/user.txt
chmod 644 /home/developer/user.txt /home/laour/user.txt /root/root.txt

# Generate HMAC verification values
HMAC_SECRET=$(openssl rand -hex 32)
DEVELOPER_HMAC=$(echo -n "$DEVELOPER_FLAG" | openssl dgst -sha256 -hmac "$HMAC_SECRET" | awk '{print $2}')
LAOUR_HMAC=$(echo -n "$LAOUR_FLAG" | openssl dgst -sha256 -hmac "$HMAC_SECRET" | awk '{print $2}')
ROOT_HMAC=$(echo -n "$ROOT_FLAG" | openssl dgst -sha256 -hmac "$HMAC_SECRET" | awk '{print $2}')

# Save verification data
cat > "$FLAG_DIR/flags.json" <<EOF
{
    "hmac_secret": "$HMAC_SECRET",
    "developer": "$DEVELOPER_HMAC",
    "laour": "$LAOUR_HMAC",
    "root": "$ROOT_HMAC"
}
EOF

chmod 600 "$FLAG_DIR/flags.json"

# Also save for verifier (and the web verify page) - readable copy
mkdir -p /opt/t3rmx/verifier
cp "$FLAG_DIR/flags.json" /opt/t3rmx/verifier/flags.json
chmod 644 /opt/t3rmx/verifier/flags.json

echo "[+] Flags generated and verification values created"
