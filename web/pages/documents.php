<?php
requireLogin();
$db = Database::getInstance();

$documents = $db->fetchAll("SELECT d.*, u.full_name as author FROM documents d LEFT JOIN users u ON d.owner_id = u.id ORDER BY d.created_at DESC");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Documents</h1>
    <p>Internal documentation and reports</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Documents</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc): ?>
                <tr>
                    <td><?= htmlspecialchars($doc['title']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($doc['category']) ?></span></td>
                    <td><?= htmlspecialchars($doc['author'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($doc['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Documents', $content);
?>
