<?php
requireAdmin();
$user = getCurrentUser();
$db = Database::getInstance();

$stats = [
    'users' => $db->fetch("SELECT COUNT(*) as c FROM users")['c'],
    'documents' => $db->fetch("SELECT COUNT(*) as c FROM documents")['c'],
    'uploads' => $db->fetch("SELECT COUNT(*) as c FROM uploads")['c'],
    'tickets' => $db->fetch("SELECT COUNT(*) as c FROM support_tickets WHERE status != 'resolved'")['c'],
    'logs' => $db->fetch("SELECT COUNT(*) as c FROM audit_logs")['c'],
];

$recent_logs = $db->fetchAll("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>System administration and management</p>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3>System Overview</h3>
        </div>
        <div class="card-body">
            <div class="stat-grid">
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['users'] ?></span>
                    <span class="stat-desc">Users</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['documents'] ?></span>
                    <span class="stat-desc">Documents</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['uploads'] ?></span>
                    <span class="stat-desc">Uploads</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['tickets'] ?></span>
                    <span class="stat-desc">Open Tickets</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>System Information</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Platform</label>
                    <span>T3rmx Portal v4.2.1</span>
                </div>
                <div class="info-item">
                    <label>Server</label>
                    <span>Apache/2.4 (Debian)</span>
                </div>
                <div class="info-item">
                    <label>PHP Version</label>
                    <span><?= phpversion() ?></span>
                </div>
                <div class="info-item">
                    <label>Database</label>
                    <span>SQLite3 <?= SQLite3::version()['versionString'] ?></span>
                </div>
                <div class="info-item">
                    <label>Uptime</label>
                    <span><?= php_uname('n') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Recent Activity</h3>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['username'] ?? 'system') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Admin Dashboard', $content);
?>
