<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function get_users($pdo) {
    $stmt = $pdo->query("SELECT id, username, first_name, last_name, role, is_active, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    json_response(['users' => $users]);
}

get_users($pdo);
