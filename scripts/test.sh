#!/bin/bash
set -e

HTTP_PORT="${CTF_HTTP_PORT:-8080}"
SSH_PORT="${CTF_SSH_PORT:-2222}"

echo "========================================="
echo "  T3rmx CTF - Test Suite"
echo "========================================="
echo ""

PASS=0
FAIL=0

test_check() {
    local name="$1"
    shift
    if "$@" >/dev/null 2>&1; then
        echo "  [PASS] $name"
        PASS=$((PASS + 1))
    else
        echo "  [FAIL] $name"
        FAIL=$((FAIL + 1))
    fi
}

echo "[1] Infrastructure Tests"
test_check "Docker Compose up" docker compose up -d
sleep 5
test_check "Container is running" docker compose ps | grep -q "Up"
test_check "HTTP port responds" curl -sf "http://127.0.0.1:$HTTP_PORT/" >/dev/null
test_check "SSH port responds" nc -zw3 127.0.0.1 "$SSH_PORT"

echo ""
echo "[2] Web Application Tests"
test_check "Home page loads" curl -sf "http://127.0.0.1:$HTTP_PORT/" >/dev/null
test_check "Login page loads" curl -sf "http://127.0.0.1:$HTTP_PORT/login" >/dev/null
test_check "About page loads" curl -sf "http://127.0.0.1:$HTTP_PORT/about" >/dev/null
test_check "robots.txt accessible" curl -sf "http://127.0.0.1:$HTTP_PORT/robots.txt" >/dev/null

echo ""
echo "[3] Database Tests"
test_check "SQLite database exists" docker exec t3rmx-ctf test -f /app/data/database.sqlite3
test_check "Database has users" docker exec t3rmx-ctf sqlite3 /app/data/database.sqlite3 "SELECT COUNT(*) FROM users;" | grep -q "[1-9]"

echo ""
echo "[4] User Tests"
test_check "developer user exists" docker exec t3rmx-ctf id developer
test_check "laour user exists" docker exec t3rmx-ctf id laour

echo ""
echo "[5] File System Tests"
test_check ".env file exists" docker exec t3rmx-ctf test -f /var/www/app/.env
test_check "Developer key exists" docker exec t3rmx-ctf test -f /var/www/app/developer_key
test_check "Upload directory writable" docker exec t3rmx-ctf test -w /var/www/uploads
test_check "777 path exists" docker exec t3rmx-ctf test -d /opt/t3rmx/services/maintenance/runtime/scripts
test_check "777 permission set" docker exec t3rmx-ctf stat -c "%a" /opt/t3rmx/services/maintenance/runtime/scripts | grep -q "777"
test_check "Maintenance cron exists" docker exec t3rmx-ctf test -f /etc/cron.d/t3rmx-maintenance

echo ""
echo "========================================="
echo "  Results: $PASS passed, $FAIL failed"
echo "========================================="

if [ "$FAIL" -gt 0 ]; then
    exit 1
fi
