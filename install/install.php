<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$host = $input['host'] ?? 'localhost';
$dbname = $input['dbname'] ?? 'quewing_system';
$user = $input['user'] ?? 'root';
$pass = $input['pass'] ?? '';
$admin_user = $input['admin_user'] ?? 'admin';
$admin_pass = $input['admin_pass'] ?? 'admin123';
$admin_first = $input['admin_first'] ?? 'System';
$admin_last = $input['admin_last'] ?? 'Admin';

$logs = [];

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $logs[] = "✓ Connected to MySQL";

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $logs[] = "✓ Database '$dbname' created/verified";

    $pdo->exec("USE `$dbname`");
    
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $stmt) {
        if ($stmt) {
            $pdo->exec($stmt);
        }
    }
    $logs[] = "✓ Tables created";

    $admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$admin_user, $admin_hash, $admin_first, $admin_last]);
    $logs[] = "✓ Admin user '$admin_user' created";

    $stmt = $pdo->query("SELECT id FROM counters WHERE counter_number = 1");
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO counters (counter_number, status) VALUES (1, 'available')");
        $logs[] = "✓ Default counter created";
    }

    echo json_encode(['success' => true, 'logs' => $logs]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'logs' => $logs]);
}