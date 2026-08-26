#!/bin/bash
set -e

echo "[*] Starting T3rmx CTF..."
docker compose up -d
echo "[*] Waiting for health check..."
sleep 5
docker compose ps
echo "[+] T3rmx CTF started."
echo "    HTTP: http://127.0.0.1:${CTF_HTTP_PORT:-8080}"
echo "    SSH:  127.0.0.1:${CTF_SSH_PORT:-2222}"
