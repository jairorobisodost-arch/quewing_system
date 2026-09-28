<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    json_response(['error' => 'Username and password required'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response(['error' => 'Invalid credentials'], 401);
}

$counter_id = null;
$counter_number = null;

if ($user['role'] === 'counter') {
    // 1) Admin-assigned counter always wins (regardless of its current status)
    $stmt = $pdo->prepare("SELECT id, counter_number FROM counters WHERE staff_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $counter = $stmt->fetch();

    if (!$counter) {
        // 2) Fallback: grab any free counter that has no staff assigned
        $stmt = $pdo->prepare("SELECT id, counter_number FROM counters WHERE status = 'available' AND staff_id IS NULL ORDER BY counter_number LIMIT 1");
        $stmt->execute();
        $counter = $stmt->fetch();

        if ($counter) {
            $stmt = $pdo->prepare("UPDATE counters SET staff_id = ?, status = 'available', assigned_by_admin = 0 WHERE id = ?");
            $stmt->execute([$user['id'], $counter['id']]);
        }
    }

    if ($counter) {
        $counter_id = (int)$counter['id'];
        $counter_number = (int)$counter['counter_number'];
    }
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['counter_id'] = $counter_id;
$_SESSION['counter_number'] = $counter_number;
$_SESSION['last_activity'] = time();

audit_log($pdo, $user['id'], 'login', 'User logged in');

$redirect = $user['role'] === 'admin' ? '/quewing_system/admin/dashboard.php' : '/quewing_system/counter/dashboard.php';
json_response(['success' => true, 'redirect' => $redirect, 'role' => $user['role'], 'name' => $_SESSION['user_name'], 'counter' => $counter_number]);