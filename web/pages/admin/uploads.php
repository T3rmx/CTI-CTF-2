<?php
requireAdmin();
$db = Database::getInstance();
$user = getCurrentUser();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $originalName = $file['name'];
        $mimeType = $file['type'];
        $size = $file['size'];

        // Intentionally flawed validation:
        // Checks MIME type from request (client-controlled) instead of file content
        // Also has a bypass: doesn't properly check file extension
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'application/pdf'];

        // The flaw: we check the MIME type sent by the browser, but don't verify
        // the actual file content. Also, we don't block PHP files properly.
        // The .htaccess in uploads/ is supposed to block PHP but it's misconfigured.
        if (in_array($mimeType, $allowedMimeTypes)) {
            // Generate unique filename but preserve original extension
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $filename = uniqid('upload_', true) . '.' . $ext;
            $uploadPath = BASE_PATH . '/uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $db->query("INSERT INTO uploads (filename, original_name, mime_type, size, uploader_id) VALUES (:fn, :on, :mt, :sz, :uid)",
                    [':fn' => $filename, ':on' => $originalName, ':mt' => $mimeType, ':sz' => $size, ':uid' => $user['id']]);

                $db->query("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:uid, 'upload', :details, :ip)",
                    [':uid' => $user['id'], ':details' => "Uploaded: $originalName", ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);

                $message = "File uploaded successfully: $filename";
            } else {
                $error = "Failed to save file";
            }
        } else {
            $error = "File type not allowed. Accepted: PNG, JPG, PDF";
        }
    }
}

$uploads = $db->fetchAll("SELECT u.*, usr.full_name as uploader FROM uploads u LEFT JOIN users usr ON u.uploader_id = usr.id ORDER BY u.created_at DESC");

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>File Uploads</h1>
    <p>Upload and manage files</p>
</div>

<?php if ($message): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Upload New File</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/uploads" enctype="multipart/form-data" class="form">
            <div class="form-group">
                <label for="file">Select File</label>
                <input type="file" id="file" name="file" required accept=".png,.jpg,.jpeg,.pdf">
                <small class="text-muted">Accepted formats: PNG, JPG, PDF (Max 10MB)</small>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Uploaded Files</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Filename</th>
                    <th>Original Name</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uploads as $upload): ?>
                <tr>
                    <td><?= $upload['id'] ?></td>
                    <td><a href="/uploads/<?= htmlspecialchars($upload['filename']) ?>"><?= htmlspecialchars($upload['filename']) ?></a></td>
                    <td><?= htmlspecialchars($upload['original_name']) ?></td>
                    <td><?= htmlspecialchars($upload['mime_type']) ?></td>
                    <td><?= number_format($upload['size']) ?> bytes</td>
                    <td><?= htmlspecialchars($upload['uploader'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($upload['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('File Uploads', $content);
?>
