<?php
function renderLayout($title, $content, $showSidebar = true) {
    $user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - T3rmx Portal</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <link rel="icon" href="/static/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="header">
        <div class="header-left">
            <a href="/" class="logo">
                <span class="logo-icon">&#9881;</span>
                T3rmx
            </a>
        </div>
        <nav class="header-nav">
            <?php if ($user): ?>
                <a href="/dashboard">Dashboard</a>
                <a href="/documents">Documents</a>
                <a href="/support">Support</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin" class="nav-admin">Admin</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/about">About</a>
                <a href="/news">News</a>
                <a href="/status">Status</a>
                <a href="/verify" class="nav-verify">Verify Flags</a>
            <?php endif; ?>
        </nav>
        <div class="header-right">
            <?php if ($user): ?>
                <span class="user-info"><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="/profile" class="btn btn-sm">Profile</a>
                <a href="/logout" class="btn btn-sm btn-outline">Logout</a>
            <?php else: ?>
                <a href="/login" class="btn btn-sm btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <?php if ($showSidebar && $user): ?>
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="/dashboard" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9632;</span> Dashboard
                </a>
                <a href="/documents" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/documents' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9998;</span> Documents
                </a>
                <a href="/support" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/support' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9742;</span> Support
                </a>
                <a href="/profile" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/profile' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9786;</span> Profile
                </a>
                <?php if ($user['role'] === 'admin'): ?>
                <div class="sidebar-divider"></div>
                <a href="/admin" class="sidebar-item <?= strpos($_SERVER['REQUEST_URI'], '/admin') === 0 ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9881;</span> Admin Panel
                </a>
                <a href="/admin/users" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/admin/users' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9787;</span> Users
                </a>
                <a href="/admin/files" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/admin/files' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#128193;</span> Files
                </a>
                <a href="/admin/uploads" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/admin/uploads' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#8682;</span> Uploads
                </a>
                <a href="/admin/settings" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/admin/settings' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#9881;</span> Settings
                </a>
                <a href="/admin/logs" class="sidebar-item <?= $_SERVER['REQUEST_URI'] === '/admin/logs' ? 'active' : '' ?>">
                    <span class="sidebar-icon">&#128196;</span> Audit Logs
                </a>
                <?php endif; ?>
            </nav>
        </aside>
        <?php endif; ?>

        <main class="main-content <?= (!$showSidebar || !$user) ? 'full-width' : '' ?>">
            <?= $content ?>
        </main>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <span>&copy; 2024 T3rmx. All rights reserved.</span>
            <span class="footer-links">
                <a href="/about">About</a>
                <a href="/support">Support</a>
                <a href="/status">System Status</a>
                <a href="/verify">Verify Flags</a>
            </span>
        </div>
    </footer>
</body>
</html>
<?php
}
