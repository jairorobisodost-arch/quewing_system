<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function create_user($pdo) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $role = $input['role'] ?? 'counter';

    if (empty($username) || empty($password)) {
        json_response(['error' => 'Username and password required'], 400);
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password_hash, $first_name, $last_name, $role]);

        audit_log($pdo, $_SESSION['user_id'], 'create_user', "Created user: $username ($role)");

        json_response(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        json_response(['error' => 'Username already exists'], 409);
    }
}

create_user($pdo);
