<?php
requireAdmin();
$db = Database::getInstance();

$logs = $db->fetchAll("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 50");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Audit Logs</h1>
    <p>System activity audit trail</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Activity</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log['id'] ?></td>
                    <td><?= htmlspecialchars($log['username'] ?? 'system') ?></td>
                    <td><span class="badge"><?= htmlspecialchars($log['action']) ?></span></td>
                    <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '') ?></code></td>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Audit Logs', $content);
?>
