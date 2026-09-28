<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function delete_service($pdo, $id) {
    $stmt = $pdo->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();

    if (!$service) {
        json_response(['error' => 'Service not found'], 404);
    }

    $stmt = $pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?");
    $stmt->execute([$id]);

    audit_log($pdo, $_SESSION['user_id'], 'delete_service', "Deleted service: " . $service['name']);

    json_response(['success' => true]);
}

delete_service($pdo, (int)($_GET['id'] ?? 0));
