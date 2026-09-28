<?php
require_once __DIR__ . '/config.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = trim($request_uri, '/');

if ($request_uri === '' || $request_uri === 'quewing_system' || $request_uri === 'quewing_system/') {
    header('Location: /quewing_system/landing.php');
    exit;
}

$api_path = '';
if (strpos($request_uri, 'quewing_system/api/') === 0) {
    $api_path = substr($request_uri, strlen('quewing_system/'));
} else {
    $api_path = '';
}

if ($api_path) {
    header('Content-Type: application/json');

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path, '/');
    $path = str_replace('quewing_system/', '', $path);

    if ($path === 'api/auth/login' && $method === 'POST') {
        require_once __DIR__ . '/api/auth/login.php';
    } elseif ($path === 'api/public-data' && $method === 'GET') {
        require_once __DIR__ . '/api/public_data.php';
    } elseif ($path === 'api/tickets/public' && $method === 'POST') {
        require_once __DIR__ . '/api/tickets/public_create.php';
    } elseif ($path === 'api/auth/logout' && $method === 'POST') {
        require_once __DIR__ . '/api/auth/logout.php';
    } elseif ($path === 'api/tickets' && $method === 'POST') {
        require_once __DIR__ . '/api/tickets/create.php';
    } elseif ($path === 'api/tickets/queue' && $method === 'GET') {
        require_once __DIR__ . '/api/tickets/queue.php';
    } elseif ($path === 'api/tickets/walk-in' && $method === 'POST') {
        require_once __DIR__ . '/api/tickets/walk_in.php';
    } elseif (preg_match('#^api/tickets/(\d+)/call$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/tickets/call.php';
    } elseif (preg_match('#^api/tickets/(\d+)/complete$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/tickets/complete.php';
    } elseif (preg_match('#^api/tickets/(\d+)/no-show$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/tickets/no_show.php';
    } elseif ($path === 'api/counters' && $method === 'GET') {
        require_once __DIR__ . '/api/counters/index.php';
    } elseif ($path === 'api/counters/my' && $method === 'GET') {
        require_once __DIR__ . '/api/counters/my.php';
    } elseif ($path === 'api/counters/update' && $method === 'PATCH') {
        require_once __DIR__ . '/api/counters/update.php';
    } elseif (preg_match('#^api/counters/(\d+)$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/counters/update.php';
    } elseif ($path === 'api/services' && $method === 'GET') {
        require_once __DIR__ . '/api/services/index.php';
    } elseif ($path === 'api/services' && $method === 'POST') {
        require_once __DIR__ . '/api/services/create.php';
    } elseif (preg_match('#^api/services/(\d+)$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/services/update.php';
    } elseif (preg_match('#^api/services/(\d+)$#', $path, $m) && $method === 'DELETE') {
        require_once __DIR__ . '/api/services/delete.php';
    } elseif ($path === 'api/users' && $method === 'GET') {
        require_once __DIR__ . '/api/users/index.php';
    } elseif ($path === 'api/users' && $method === 'POST') {
        require_once __DIR__ . '/api/users/create.php';
    } elseif (preg_match('#^api/users/(\d+)$#', $path, $m) && $method === 'PATCH') {
        require_once __DIR__ . '/api/users/update.php';
    } elseif (preg_match('#^api/users/(\d+)$#', $path, $m) && $method === 'DELETE') {
        require_once __DIR__ . '/api/users/delete.php';
    } elseif ($path === 'api/reports/daily' && $method === 'GET') {
        require_once __DIR__ . '/api/reports/daily.php';
    } elseif ($path === 'api/reports/performance' && $method === 'GET') {
        require_once __DIR__ . '/api/reports/performance.php';
    } elseif ($path === 'api/reports/trend' && $method === 'GET') {
        require_once __DIR__ . '/api/reports/trend.php';
    } elseif ($path === 'api/audit-logs' && $method === 'GET') {
        require_once __DIR__ . '/api/audit_logs.php';
    } elseif ($path === 'api/announcement' && $method === 'GET') {
        require_once __DIR__ . '/api/announcement.php';
    } elseif ($path === 'api/settings' && $method === 'GET') {
        require_once __DIR__ . '/api/settings.php';
        get_settings($pdo);
    } elseif ($path === 'api/settings' && $method === 'PATCH') {
        require_once __DIR__ . '/api/settings.php';
        update_settings($pdo);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }
    exit;
}

$page = $request_uri;
$page = str_replace('quewing_system/', '', $page);
$page = trim($page, '/');

$allowed_pages = [
    'landing.php' => 'landing.php',
    'login.php' => 'login.php',
    'counter/dashboard.php' => 'counter/dashboard.php',
    'queue-display/index.php' => 'queue-display/index.php',
    'admin/dashboard.php' => 'admin/dashboard.php',
    'admin/services.php' => 'admin/services.php',
    'admin/counters.php' => 'admin/counters.php',
    'admin/users.php' => 'admin/users.php',
    'admin/reports.php' => 'admin/reports.php',
    'admin/import_clients.php' => 'admin/import_clients.php',
    'admin/settings.php' => 'admin/settings.php',
];

if (array_key_exists($page, $allowed_pages)) {
    require_once __DIR__ . '/' . $allowed_pages[$page];
} elseif ($page === '' || $page === 'index.php') {
    require_once __DIR__ . '/login.php';
} else {
    http_response_code(404);
    echo "Page not found";
}