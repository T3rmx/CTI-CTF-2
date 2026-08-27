<?php
requireAdmin();
$db = Database::getInstance();
$user = getCurrentUser();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $originalName = $file['name'];
        $mimeType = $file['type'];
        $size = $file['size'];

        // Intentionally flawed validation:
        // Checks MIME type from request (client-controlled) instead of file content
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'application/pdf'];

        if (in_array($mimeType, $allowedMimeTypes)) {
            $filename = basename($originalName);
            $uploadPath = PUBLIC_PATH . '/uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                try {
                    $db->query("INSERT INTO uploads (filename, original_name, mime_type, size, uploader_id) VALUES (:fn, :on, :mt, :sz, :uid)",
                        [':fn' => $filename, ':on' => $originalName, ':mt' => $mimeType, ':sz' => $size, ':uid' => $user['id']]);

                    $db->query("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (:uid, 'upload', :details, :ip)",
                        [':uid' => $user['id'], ':details' => "Uploaded: $originalName", ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
                } catch (Exception $e) {
                    // File saved but DB insert failed
                }

                $message = "File uploaded successfully: $filename";
            } else {
                $error = "Failed to save file";
            }
        } else {
            $error = "File type not allowed. Accepted: PNG, JPG, PDF";
        }
    } else {
        $error = "No file uploaded or upload error";
    }
}

try {
    $uploads = $db->fetchAll("SELECT u.*, usr.full_name as uploader FROM uploads u LEFT JOIN users usr ON u.uploader_id = usr.id ORDER BY u.created_at DESC");
} catch (Exception $e) {
    $uploads = [];
}

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
        <?php if (empty($uploads)): ?>
        <p class="text-muted">No files uploaded yet.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Filename</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uploads as $upload):
                    $isImage = in_array($upload['mime_type'], ['image/png', 'image/jpeg', 'image/jpg']) || in_array(strtolower(pathinfo($upload['filename'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']);
                    $fileUrl = '/uploads/' . urlencode($upload['filename']);
                ?>
                <tr>
                    <td>
                        <?php if ($isImage): ?>
                        <a href="<?= $fileUrl ?>" target="_blank">
                            <img src="<?= $fileUrl ?>" alt="<?= htmlspecialchars($upload['original_name']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:4px;">
                        </a>
                        <?php else: ?>
                        <span style="display:inline-block;width:48px;height:48px;line-height:48px;text-align:center;background:var(--bg);border-radius:4px;">PDF</span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= $fileUrl ?>" target="_blank" title="Open file"><?= htmlspecialchars($upload['original_name']) ?></a></td>
                    <td><?= htmlspecialchars($upload['mime_type']) ?></td>
                    <td><?= number_format($upload['size']) ?> bytes</td>
                    <td><?= htmlspecialchars($upload['uploader'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($upload['created_at']) ?></td>
                    <td><a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary">Open</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('File Uploads', $content);
?>
