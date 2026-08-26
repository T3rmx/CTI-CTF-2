# T3rmx CTF

Compromise the T3rmx infrastructure and retrieve all flags.

## Quick Start

```bash
docker compose up -d
```

## Target

- HTTP: http://127.0.0.1:8080
- SSH: 127.0.0.1:2222

## Objective

Gain root access and retrieve all flags:

- Developer flag
- Laour flag
- Root flag

## Custom Ports

```bash
CTF_HTTP_PORT=8888 CTF_SSH_PORT=2200 docker compose up -d
```

## Reset

```bash
./scripts/reset.sh
```
