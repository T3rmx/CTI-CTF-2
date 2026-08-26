<?php
requireLogin();
$user = getCurrentUser();
$db = Database::getInstance();

$profile = $db->fetch("SELECT * FROM profiles WHERE user_id = :uid", [':uid' => $user['id']]);

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="page-header">
    <h1>Profile</h1>
    <p>Your account information</p>
</div>

<div class="card">
    <div class="card-header">
        <h3>Account Details</h3>
    </div>
    <div class="card-body">
        <div class="profile-grid">
            <div class="profile-field">
                <label>Username</label>
                <span><?= htmlspecialchars($user['username']) ?></span>
            </div>
            <div class="profile-field">
                <label>Full Name</label>
                <span><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
            <div class="profile-field">
                <label>Email</label>
                <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="profile-field">
                <label>Department</label>
                <span><?= htmlspecialchars($user['department']) ?></span>
            </div>
            <div class="profile-field">
                <label>Role</label>
                <span class="badge"><?= htmlspecialchars($user['role']) ?></span>
            </div>
            <div class="profile-field">
                <label>Last Login</label>
                <span><?= htmlspecialchars($user['last_login'] ?? 'Never') ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($profile): ?>
<div class="card">
    <div class="card-header">
        <h3>Additional Information</h3>
    </div>
    <div class="card-body">
        <div class="profile-grid">
            <?php if ($profile['bio']): ?>
            <div class="profile-field full-width">
                <label>Bio</label>
                <p><?= htmlspecialchars($profile['bio']) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($profile['phone']): ?>
            <div class="profile-field">
                <label>Phone</label>
                <span><?= htmlspecialchars($profile['phone']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($profile['office']): ?>
            <div class="profile-field">
                <label>Office</label>
                <span><?= htmlspecialchars($profile['office']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
renderLayout('Profile', $content);
?>
