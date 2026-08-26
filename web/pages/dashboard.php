<?php
requireLogin();
$user = getCurrentUser();
$db = Database::getInstance();

$notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5",
    [':uid' => $user['id']]);

$recent_docs = $db->fetchAll("SELECT * FROM documents ORDER BY created_at DESC LIMIT 5");

$ticket_count = $db->fetch("SELECT COUNT(*) as count FROM support_tickets WHERE status != 'resolved'");

$upload_count = $db->fetch("SELECT COUNT(*) as count FROM uploads");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user['full_name']) ?></p>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3>Quick Stats</h3>
        </div>
        <div class="card-body">
            <div class="stat-grid">
                <div class="stat-item">
                    <span class="stat-value"><?= $ticket_count['count'] ?? 0 ?></span>
                    <span class="stat-desc">Open Tickets</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($recent_docs) ?></span>
                    <span class="stat-desc">Recent Documents</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= $upload_count['count'] ?? 0 ?></span>
                    <span class="stat-desc">Files Uploaded</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">Active</span>
                    <span class="stat-desc">System Status</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Recent Notifications</h3>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
                <p class="text-muted">No new notifications</p>
            <?php else: ?>
                <ul class="notification-list">
                    <?php foreach ($notifications as $notif): ?>
                    <li class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                        <strong><?= htmlspecialchars($notif['title']) ?></strong>
                        <p><?= htmlspecialchars($notif['message']) ?></p>
                        <small><?= htmlspecialchars($notif['created_at']) ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Recent Documents</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recent_docs)): ?>
                <p class="text-muted">No documents available</p>
            <?php else: ?>
                <ul class="document-list">
                    <?php foreach ($recent_docs as $doc): ?>
                    <li>
                        <strong><?= htmlspecialchars($doc['title']) ?></strong>
                        <span class="badge"><?= htmlspecialchars($doc['category']) ?></span>
                        <small><?= htmlspecialchars($doc['created_at']) ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Dashboard', $content);
?>
