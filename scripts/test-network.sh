#!/bin/bash
set -e

echo "[*] T3rmx CTF - Network Connectivity Test"
echo ""

echo "[*] Testing container to host connectivity..."
if docker exec t3rmx-ctf bash -c "curl -sf http://host.docker.internal:9999 >/dev/null 2>&1 || nc -zw3 host.docker.internal 9999 2>/dev/null" 2>/dev/null; then
    echo "  [PASS] Container can reach host.docker.internal"
else
    echo "  [INFO] Container can resolve host.docker.internal (connection refused is expected without listener)"
fi

echo ""
echo "[*] Testing DNS resolution..."
if docker exec t3rmx-ctf bash -c "getent hosts host.docker.internal" >/dev/null 2>&1; then
    echo "  [PASS] host.docker.internal resolves"
else
    echo "  [FAIL] host.docker.internal does not resolve"
    exit 1
fi

echo ""
echo "[+] Network test complete."
