<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function update_service($pdo, $id) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($input['name'] ?? '');
    $avg_time = $input['avg_time'] ?? null;
    $is_active = isset($input['is_active']) ? (int)$input['is_active'] : null;

    $fields = [];
    $params = [];
    if ($name) { $fields[] = 'name = ?'; $params[] = $name; }
    if ($avg_time !== null) { $fields[] = 'avg_time = ?'; $params[] = (int)$avg_time; }
    if ($is_active !== null) { $fields[] = 'is_active = ?'; $params[] = $is_active; }

    if (empty($fields)) {
        json_response(['error' => 'No data to update'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE services SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    audit_log($pdo, $_SESSION['user_id'], 'update_service', "Updated service ID $id");

    json_response(['success' => true]);
}

update_service($pdo, (int)($_GET['id'] ?? 0));
