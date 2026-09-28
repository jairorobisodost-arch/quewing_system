<?php
date_default_timezone_set('Asia/Manila');

$DB_HOST = 'localhost';
$DB_NAME = 'quewing_system';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

define('SESSION_TIMEOUT', 1800); // 30 minutes

session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Server-side session timeout check
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        $is_api = strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
        if ($is_api) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Session expired']);
            exit;
        } else {
            header('Location: /quewing_system/landing.php?expired=1');
            exit;
        }
    }
}
// Update last activity on every request
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

function audit_log($pdo, $user_id, $action, $details = '') {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $details]);
}

function is_api_request() {
    return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        if (is_api_request()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        } else {
            header('Location: /quewing_system/landing.php');
            exit;
        }
    }
}

function require_role($role) {
    if (!isset($_SESSION['user_id'])) {
        require_login();
        return;
    }
    if ($_SESSION['user_role'] !== $role) {
        if (is_api_request()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit;
        } else {
            $redirect = $_SESSION['user_role'] === 'admin'
                ? '/quewing_system/admin/dashboard.php'
                : '/quewing_system/counter/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        }
    }
}

function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function get_setting($pdo, $key, $default = '') {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function generate_ticket_number($pdo, $prefix = 'SFI') {
    $today = date('md');
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM tickets WHERE created_date = CURDATE()");
    $stmt->execute();
    $count = (int)$stmt->fetch()['cnt'];
    $chk = $pdo->prepare("SELECT id FROM tickets WHERE ticket_number = ?");
    do {
        $count++;
        $num = $prefix . '-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $chk->execute([$num]);
    } while ($chk->fetch());
    return $num;
}
