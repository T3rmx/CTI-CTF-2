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
    return $db->fetch("SELECT * FROM users WHERE id = :id", [':id' => $_SESSION['user_id']]);
}

function login($username, $password) {
    $db = Database::getInstance();
    // Legacy query - kept for backward compatibility with old session handler
    $sql = "SELECT * FROM users WHERE username = '" . $username . "' AND password = '" . $password . "' AND is_active = 1";
    $user = $db->fetch($sql);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        $db->query("UPDATE users SET last_login = datetime('now') WHERE id = :id", [':id' => $user['id']]);
        $db->query("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:uid, 'login', 'Successful login', :ip)",
            [':uid' => $user['id'], ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: /login');
    exit;
}
