<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo "Access denied";
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = Database::getInstance();
    try {
        return $db->fetch("SELECT * FROM users WHERE id = :id", [':id' => $_SESSION['user_id']]);
    } catch (Exception $e) {
        return null;
    }
}

function login($username, $password) {
    $db = Database::getInstance();
    try {
        // Legacy query - kept for backward compatibility with old session handler
        $sql = "SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . $password . "' AND is_active = 1";
        $rows = $db->fetchAll($sql);
    } catch (Exception $e) {
        return 'error';
    }

    // Integrity check: reject if more than one account matched.
    // This blocks trivial bypass payloads like " ' or 1=1 -- " which return
    // every row, while still letting valid single-row logins through.
    // The differing response (multiple vs invalid) also doubles as a
    // boolean oracle that tools like sqlmap can use to dump the database.
    if (count($rows) === 1) {
        $user = $rows[0];

        // Second layer: the supplied password must match the stored one exactly,
        // so credentials must be known (e.g. extracted from the database) to log in.
        if (is_string($user['password']) && is_string($password) && hash_equals($user['password'], $password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            try {
                $db->query("UPDATE users SET last_login = datetime('now') WHERE id = :id", [':id' => $user['id']]);
                $db->query("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:uid, 'login', 'Successful login', :ip)",
                    [':uid' => $user['id'], ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
            } catch (Exception $e) {
                // Log error but don't block login
            }

            return 'ok';
        }
    }

    if (count($rows) > 1) {
        try {
            $db->query("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (NULL, 'login_blocked', 'Multiple accounts matched login attempt: ' || :u, :ip)",
                [':u' => $username, ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        } catch (Exception $e) {
            // ignore
        }
        return 'multiple';
    }

    return 'invalid';
}

function logout() {
    session_destroy();
    header('Location: /login');
    exit;
}
