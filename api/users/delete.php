<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function delete_user($pdo, $id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(['error' => 'User not found'], 404);
    }

    if ($user['username'] === $_SESSION['username']) {
        json_response(['error' => 'Cannot delete own account'], 400);
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    audit_log($pdo, $_SESSION['user_id'], 'delete_user', "Deleted user: " . $user['username']);

    json_response(['success' => true]);
}

delete_user($pdo, (int)($_GET['id'] ?? 0));
