#!/bin/bash
set -e

echo "[*] Resetting T3rmx CTF..."
docker compose down -v 2>/dev/null || true
docker compose up -d
echo "[*] Waiting for health check..."
sleep 8
docker compose ps
echo "[+] T3rmx CTF reset complete. Flags regenerated, DB recreated."
echo "    HTTP: http://127.0.0.1:${CTF_HTTP_PORT:-8080}"
echo "    SSH:  127.0.0.1:${CTF_SSH_PORT:-2222}"