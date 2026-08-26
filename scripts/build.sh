#!/bin/bash
set -e

echo "[*] Building T3rmx CTF..."
docker compose build --no-cache
echo "[+] Build complete."
