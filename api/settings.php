<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');

function get_settings($pdo) {
    $stmt = $pdo->query("SELECT * FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    json_response(['settings' => $settings]);
}

function update_settings($pdo) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    foreach ($input as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $stmt->execute([$key, $value]);
    }
    audit_log($pdo, $_SESSION['user_id'], 'update_settings', 'System settings updated');
    json_response(['success' => true]);
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'PATCH' || $method === 'POST') {
    update_settings($pdo);
} else {
    get_settings($pdo);
}