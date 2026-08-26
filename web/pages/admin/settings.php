<?php
requireAdmin();
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['setting_key'] ?? '';
    $value = $_POST['setting_value'] ?? '';

    if ($key && $value) {
        $db->query("INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) VALUES (:key, :val, datetime('now'))",
            [':key' => $key, ':val' => $value]);
        header('Location: /admin/settings?updated=1');
        exit;
    }
}

$settings = $db->fetchAll("SELECT * FROM settings ORDER BY setting_key");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>System Settings</h1>
    <p>Configure portal settings</p>
</div>

<?php if (isset($_GET['updated'])): ?>
<div class="alert alert-success">Settings updated successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Current Settings</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settings as $setting): ?>
                <tr>
                    <td><code><?= htmlspecialchars($setting['setting_key']) ?></code></td>
                    <td><?= htmlspecialchars($setting['setting_value']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Update Setting</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/settings" class="form">
            <div class="form-group">
                <label for="setting_key">Setting Key</label>
                <input type="text" id="setting_key" name="setting_key" required placeholder="e.g., maintenance_mode">
            </div>
            <div class="form-group">
                <label for="setting_value">Value</label>
                <input type="text" id="setting_value" name="setting_value" required placeholder="e.g., 1">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Settings', $content);
?>
