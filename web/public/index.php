<?php
session_start();

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

require_once BASE_PATH . '/app/config.php';
require_once BASE_PATH . '/app/database.php';
require_once BASE_PATH . '/app/auth.php';
require_once BASE_PATH . '/app/router.php';

$route = $_GET['route'] ?? '/';
$route = '/' . trim($route, '/');

dispatch($route);
