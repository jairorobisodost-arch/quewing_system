<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function update_user($pdo, $id) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $fields = [];
    $params = [];

    if (isset($input['first_name'])) { $fields[] = 'first_name = ?'; $params[] = trim($input['first_name']); }
    if (isset($input['last_name'])) { $fields[] = 'last_name = ?'; $params[] = trim($input['last_name']); }
    if (isset($input['role'])) { $fields[] = 'role = ?'; $params[] = $input['role']; }
    if (isset($input['is_active'])) { $fields[] = 'is_active = ?'; $params[] = (int)$input['is_active']; }
    if (isset($input['password']) && $input['password']) {
        $fields[] = 'password_hash = ?'; $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }

    if (empty($fields)) {
        json_response(['error' => 'No data to update'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    audit_log($pdo, $_SESSION['user_id'], 'update_user', "Updated user ID $id");

    json_response(['success' => true]);
}

update_user($pdo, (int)($_GET['id'] ?? 0));
