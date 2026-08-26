<?php
requireAdmin();
$db = Database::getInstance();

$users = $db->fetchAll("SELECT * FROM users ORDER BY id");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>User Management</h1>
    <p>Manage system users and permissions</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Users</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['department']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($u['role']) ?></span></td>
                    <td><?= $u['is_active'] ? 'Active' : 'Inactive' ?></td>
                    <td><?= htmlspecialchars($u['last_login'] ?? 'Never') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('User Management', $content);
?>
