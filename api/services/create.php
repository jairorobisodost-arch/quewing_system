<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function create_service($pdo) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = trim($input['name'] ?? '');
    $avg_time = (int)($input['avg_time'] ?? 0);

    if (empty($name)) {
        json_response(['error' => 'Service name required'], 400);
    }

    $stmt = $pdo->prepare("INSERT INTO services (name, avg_time) VALUES (?, ?)");
    $stmt->execute([$name, $avg_time]);

    audit_log($pdo, $_SESSION['user_id'], 'create_service', "Created service: $name");

    json_response(['success' => true, 'id' => $pdo->lastInsertId()]);
}

create_service($pdo);
