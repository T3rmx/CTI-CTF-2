<?php require BASE_PATH . '/templates/layout.php'; ?>
<?php $content = ob_start(); ?>
<div class="page-header">
    <h1>404 - Page Not Found</h1>
    <p>The requested page does not exist.</p>
</div>
<div class="card">
    <div class="card-body">
        <p>The page you're looking for might have been moved or doesn't exist.</p>
        <a href="/" class="btn btn-primary" style="margin-top: 1rem;">Return to Home</a>
    </div>
</div>
<?php $content = ob_get_clean(); renderLayout('404', $content, false); ?>
