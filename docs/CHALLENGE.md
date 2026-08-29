# T3rmx CTF — Challenge Documentation

Single-container Docker Capture-The-Flag challenge. Goal: compromise the fictional
"T3rmx infrastructure" and retrieve **3 flags** (developer, laour, root).

---

## 1. Infrastructure

One container (`t3rmx-ctf`) based on `debian:bookworm-slim` runs everything:

| Service   | Software                          | Port (host)  | Default       |
|-----------|-----------------------------------|--------------|---------------|
| Web app   | Apache 2.4 + PHP 8.2 + SQLite3    | 8080 → 80    | `CTF_HTTP_PORT` |
| SSH       | OpenSSH server                    | 2222 → 22    | `CTF_SSH_PORT`  |

- `host.docker.internal` is mapped to the Docker host (outbound network is open —
  reverse shells work).
- Container is DRY-RUN: **no volumes**. Every full rebuild regenerates passwords
  and flags. `docker compose down -v && up` = full reset.

### Pre-installed team tooling

`curl`, `wget`, `nc` (netcat-openbsd), `nmap`, `socat`, `tcpdump`, `git`, `gcc`,
`gdb`, `make`, `strace`, `ltrace`, `python3` + `pip`, `vim`, `nano`, `tree`,
`htop`, `file`, `unzip`, `ip`, `dig`/`nslookup`, `sqlite3`. A pre-seeded
`linpeas.sh` (if network was available at build time) lives at
`/opt/t3rmx/tools/linpeas.sh`; teams can also fetch it themselves at runtime.

### Directory layout inside the container

```
/var/www/public/            Apache DocumentRoot (front controller)
    index.php               session_start + dispatch
    .htaccess               rewrite: only non-existent files → index.php
    static/                 CSS theme
    uploads/                upload destination (777, writable by www-data)
        .htaccess           intentionally flawed PHP-execution block
/var/www/app/               app code (not served, but readable by www-data)
    .env                    secrets stash (SSH key path, MAIL_PASS, ...)
    developer_key           SSH private key (600, owned www-data → BUG)
    flags/flags.json        HMAC verification values
    config/backup.ini       laour's backup credentials (owned developer)
/var/www/pages/             rendered page handlers (templates + router)
/opt/t3rmx/startup/         init scripts (run at boot)
/opt/t3rmx/services/        maintenance services + decoy files
/opt/t3rmx/verifier/        flag verifier (python3)
/app/data/database.sqlite3  SQLite database (SQLi target)
```

---

## 2. Web portal

Routes (`web/app/router.php`): `/`, `/login`, `/dashboard`, `/documents`, `/support`,
`/profile`, `/about`, `/news`, `/status`, `/verify`, `/api`, and admin panel `/admin`,
`/admin/users`, `/admin/files`, `/admin/uploads`, `/admin/settings`, `/admin/logs`.

`/verify` is the **flag verification page** (public): the team submits up to three
`TCI{...}` flags and the portal validates them via HMAC-SHA256 against
`/opt/t3rmx/verifier/flags.json` (digests only — real flag values are never
exposed). Linked from the header (logged-out) and footer.

### Database users (the intended steal)

Passwords are random hex generated at boot in `target/entrypoint.sh` and injected
into the SQLite seed (`target/startup/init-database.sh`).

| id | username | role  | department    | password source   |
|----|----------|-------|---------------|-------------------|
| 1  | admin    | admin | IT            | `ADMIN_PASS`      |
| 2  | jsmith   | user  | Engineering   | `DEVELOPER_PASS`  |
| 3  | mwilson  | user  | Management    | `MWILSON_PASS`    |
| 4  | rjohnson | user  | Support       | `RJOHNSON_PASS`   |
| 5  | tgarcia  | user  | Intern        | `TGARCIA_PASS`    |

Passwords are stored **in plaintext** — dumping them lets you log in normally.

### OS users

| user      | created by      | notes                                   |
|-----------|-----------------|-----------------------------------------|
| root      | base image      | root flag at `/root/root.txt`           |
| developer | init-users.sh   | SSH key auth configured                  |
| laour     | init-users.sh   | password from backup.ini discovery      |
| www-data  | Apache          | runs the web app; may also log in (shell set via SSH key attack) |

---

## 3. Intended vulnerabilities (attack surface)

1. **SQL injection** in the login form (`web/app/auth.php`) — both `username` and
   `password` fields are concatenated into SQL. Hardened so trivial bypasses
   (`' or 1=1 --`) are **rejected** (a "Multiple accounts matched" guard + exact
   password re-check `hash_equals`), but the *difference in the response message*
   (`multiple` vs `invalid`) is a **boolean oracle** that sqlmap can use to dump
   the entire database.
2. **File upload RCE** in the admin panel (`web/pages/admin/uploads.php`) —
   validation only checks the client-controlled `Content-Type` header
   (`image/png`, `image/jpeg`, `application/pdf`). Worse, the `uploads/.htaccess`
   runs the whole uploads directory through the **PHP engine**
   (`SetHandler application/x-httpd-php`) — a PHP payload executes **regardless of
   its file extension** (`.png`, `.jpg`, `.txt`, …). Non-PHP content (real images)
   is output verbatim, so previews still work. Upload a PHP webshell with a spoofed
   MIME type → code execution as `www-data`.
3. **Exposed secrets**:
   - `/var/www/app/.env` → `SSH_KEY_PATH=/var/www/app/developer_key` (private key
     readable by `www-data`).
   - `/var/www/app/config/backup.ini` → laour's OS password (file owned by
     `developer`, 640).
4. **SSH key misconfiguration** — `/var/www/app/developer_key` is created owned by
   `www-data` (init-env.sh) → web compromise = SSH key for `developer`.
5. **Privilege escalation (777 + cron)** — `/opt/t3rmx/services/maintenance/runtime/scripts`
   is world-writable (777). A root cron job (`/etc/cron.d/t3rmx-maintenance`,
   `* * * * *`) executes `cleanup.sh` from that directory every minute → overwrite
   it and wait ≤60s for root to run your payload.

### Flags

| flag        | location              | read needs            |
|-------------|-----------------------|-----------------------|
| developer   | `/home/developer/user.txt` | access as `developer`    |
| laour       | `/home/laour/user.txt`     | access as `laour`        |
| root        | `/root/root.txt`           | access as `root`         |

Format: `TCI{<16 hex chars>}` — regenerated every container boot.
Verify captured flags inside the container:

```bash
python3 /opt/t3rmx/verifier/verifier.py 'TCI{...}' 'TCI{...}' 'TCI{...}'
```

---

## 4. Deployment & control

From the repo root:

| task            | command                                    |
|-----------------|--------------------------------------------|
| build + run     | `sudo docker compose up -d --build`        |
| full reset      | `sudo docker compose down -v && sudo docker compose up -d` |
| stop            | `sudo docker compose down`                 |
| logs            | `sudo docker logs -f t3rmx-ctf`            |
| shell           | `sudo docker exec -it t3rmx-ctf bash`      |
| file browse     | `sudo docker cp t3rmx-ctf:/path ./out`     |
| self-test       | `./scripts/test.sh`                        |

Custom ports: `CTF_HTTP_PORT=8888 CTF_SSH_PORT=2200 docker compose up -d`.

### Important build gotchas

- Build context is the **repo root** (`context: .`, `dockerfile: target/Dockerfile`).
- No user-init scripts may print secrets to the console during boot.
- `web/public/uploads/` must ship the flawed `.htaccess` (it is git-tracked).
- If the web app misbehaves after edits, the container locks the changing image —
  always `docker compose down && up --build`.

---

## 5. Verifier behavior

`verifier/verifier.py` compares an HMAC-SHA256 of each submitted flag against the
values regenerated at boot (never showing real flags). It prints `VALID`/`INVALID`
per flag and exits non-zero if any fail. The verification data lives in
`/var/www/app/flags/flags.json` (mode 600) and a copy in `/opt/t3rmx/verifier/flags.json`.