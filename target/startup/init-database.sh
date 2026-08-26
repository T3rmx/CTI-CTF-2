#!/bin/bash
set -e

source /run/secrets.env

DB_PATH="/app/data/database.sqlite3"

if [ ! -f "$DB_PATH" ]; then
    sqlite3 "$DB_PATH" <<SCHEMA
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    email TEXT,
    full_name TEXT,
    department TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    is_active INTEGER DEFAULT 1
);

CREATE TABLE profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    avatar TEXT,
    bio TEXT,
    phone TEXT,
    office TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT,
    owner_id INTEGER,
    category TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE uploads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    original_name TEXT,
    mime_type TEXT,
    size INTEGER,
    uploader_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    message TEXT,
    is_read INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE support_tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    subject TEXT NOT NULL,
    description TEXT,
    status TEXT DEFAULT 'open',
    priority TEXT DEFAULT 'medium',
    creator_id INTEGER,
    assigned_to INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

CREATE TABLE settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (username, password, role, email, full_name, department) VALUES
('admin', '${ADMIN_PASS}', 'admin', 'admin@t3rmx.com', 'System Administrator', 'IT'),
('jsmith', '${DEVELOPER_PASS}', 'user', 'jsmith@t3rmx.com', 'John Smith', 'Engineering'),
('mwilson', '${MWILSON_PASS}', 'user', 'mwilson@t3rmx.com', 'Maria Wilson', 'Management'),
('rjohnson', '${RJOHNSON_PASS}', 'user', 'rjohnson@t3rmx.com', 'Rachel Johnson', 'Support'),
('tgarcia', '${TGARCIA_PASS}', 'user', 'tgarcia@t3rmx.com', 'Tom Garcia', 'Intern');

INSERT INTO profiles (user_id, bio, phone, office) VALUES
(1, 'System administrator managing T3rmx infrastructure', '+1-555-0100', 'Building A, Room 101'),
(2, 'Full-stack developer working on the T3rmx Portal', '+1-555-0101', 'Building B, Room 204'),
(3, 'Project manager overseeing client deployments', '+1-555-0102', 'Building A, Room 305'),
(4, 'Support specialist handling client issues', '+1-555-0103', 'Building C, Room 102'),
(5, 'Summer intern assisting with documentation', '+1-555-0104', 'Building B, Room 110');

INSERT INTO documents (title, content, owner_id, category) VALUES
('Network Architecture Overview', 'T3rmx operates a distributed network infrastructure serving over 200 enterprise clients across North America. Our core platform handles device management, monitoring, and automated incident response.', 1, 'internal'),
('Portal Deployment Guide', 'The T3rmx Portal is built on PHP with SQLite3 for lightweight deployments. It provides client-facing dashboards for network monitoring and support ticket management.', 2, 'engineering'),
('Q4 Security Audit Report', 'Annual security assessment completed. All critical vulnerabilities addressed. Next review scheduled for Q1 2025. Note: Legacy authentication module flagged for update.', 1, 'security'),
('Employee Handbook 2024', 'Welcome to T3rmx. This handbook covers company policies, benefits, and operational procedures.', 3, 'hr'),
('Support Escalation Matrix', 'Level 1: General support (rjohnson). Level 2: Technical issues (jsmith). Level 3: Infrastructure (admin). Emergency: Contact on-call rotation.', 4, 'support');

INSERT INTO notifications (user_id, title, message) VALUES
(1, 'System Update Scheduled', 'Server maintenance window: Saturday 2AM-4AM EST'),
(2, 'Code Review Required', 'Please review PR #847 for the portal authentication module'),
(1, 'Security Alert', 'Failed login attempts detected from IP 192.168.5.120'),
(3, 'Client Meeting', 'Reminder: Quarterly review with Acme Corp at 3PM'),
(4, 'Ticket Queue', '12 new tickets awaiting assignment');

INSERT INTO support_tickets (subject, description, status, priority, creator_id, assigned_to) VALUES
('Portal login issues', 'Multiple clients reporting intermittent login failures on the portal', 'open', 'high', 4, 2),
('Dashboard loading slow', 'Response times exceeding 5s on the main dashboard', 'in_progress', 'medium', 4, 1),
('Export feature broken', 'CSV export generating empty files since last update', 'open', 'low', 4, 2),
('API rate limiting', 'Clients hitting rate limits during peak hours', 'resolved', 'medium', 2, 1);

INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'T3rmx'),
('portal_name', 'T3rmx Portal'),
('support_email', 'support@t3rmx.com'),
('max_upload_size', '10485760'),
('session_timeout', '3600'),
('maintenance_mode', '0');

SCHEMA

    echo "[+] Database initialized with seed data"
else
    echo "[*] Database already exists, skipping initialization"
fi
