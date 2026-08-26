<?php
requireLogin();
$db = Database::getInstance();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';

    if ($subject && $description) {
        $db->query("INSERT INTO support_tickets (subject, description, priority, creator_id) VALUES (:sub, :desc, :pri, :uid)",
            [':sub' => $subject, ':desc' => $description, ':pri' => $priority, ':uid' => $user['id']]);
        header('Location: /support?created=1');
        exit;
    }
}

$tickets = $db->fetchAll("SELECT t.*, u.full_name as creator FROM support_tickets t LEFT JOIN users u ON t.creator_id = u.id ORDER BY t.created_at DESC");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Support</h1>
    <p>Submit and track support requests</p>
</div>

<?php if (isset($_GET['created'])): ?>
<div class="alert alert-success">Support ticket created successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>New Ticket</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/support" class="form">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Brief description of the issue">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required placeholder="Detailed description..."></textarea>
            </div>
            <div class="form-group">
                <label for="priority">Priority</label>
                <select id="priority" name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Submit Ticket</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Your Tickets</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?= $ticket['id'] ?></td>
                    <td><?= htmlspecialchars($ticket['subject']) ?></td>
                    <td><span class="badge badge-<?= $ticket['status'] ?>"><?= htmlspecialchars($ticket['status']) ?></span></td>
                    <td><span class="badge badge-priority-<?= $ticket['priority'] ?>"><?= htmlspecialchars($ticket['priority']) ?></span></td>
                    <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Support', $content);
?>
