<?php
if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? '/admin' : '/dashboard'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = login($username, $password);
        if ($result === 'ok') {
            $role = $_SESSION['role'] ?? 'user';
            session_write_close();
            header('Location: ' . ($role === 'admin' ? '/admin' : '/dashboard'));
            exit;
        } elseif ($result === 'multiple') {
            $error = 'Multiple accounts matched this login. Access denied for security reasons.';
        } else {
            $error = 'Invalid username or password';
        }
    }

require BASE_PATH . '/templates/layout.php';
$content = ob_start();
?>
<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h2>T3rmx Portal Login</h2>
            <p>Sign in to access your account</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username" placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <div class="login-footer">
            <p>Forgot password? <a href="/support">Contact Support</a></p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
renderLayout('Login', $content, false);
?>
