#!/bin/bash
set -e

source /run/secrets.env

# developer user - normal employee
if ! id "developer" &>/dev/null; then
    useradd -m -s /bin/bash developer
    echo "developer:${DEV_OS_PASS}" | chpasswd
fi

# laour user - operations team
if ! id "laour" &>/dev/null; then
    useradd -m -s /bin/bash laour
    echo "laour:${LAOUR_OS_PASS}" | chpasswd
fi

# Ensure www-data exists
id www-data &>/dev/null || true
