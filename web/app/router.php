<?php
function dispatch($route) {
    $routes = [
        '/' => 'pages/home.php',
        '/login' => 'pages/login.php',
        '/dashboard' => 'pages/dashboard.php',
        '/documents' => 'pages/documents.php',
        '/support' => 'pages/support.php',
        '/profile' => 'pages/profile.php',
        '/about' => 'pages/about.php',
        '/news' => 'pages/news.php',
        '/status' => 'pages/status.php',
        '/api' => 'pages/api.php',
        '/admin' => 'pages/admin/dashboard.php',
        '/admin/users' => 'pages/admin/users.php',
        '/admin/files' => 'pages/admin/files.php',
        '/admin/uploads' => 'pages/admin/uploads.php',
        '/admin/settings' => 'pages/admin/settings.php',
        '/admin/logs' => 'pages/admin/logs.php',
    ];

    if ($route === '/logout') {
        logout();
    }

    if (isset($routes[$route])) {
        $file = BASE_PATH . '/' . $routes[$route];
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    http_response_code(404);
    require BASE_PATH . '/templates/404.php';
}
