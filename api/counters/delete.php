<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function delete_counter($pdo, $id) {
    $id = (int)$id;
    if ($id <= 0) {
        json_response(['error' => 'Valid counter id required'], 400);
    }

    $stmt = $pdo->prepare("SELECT counter_number FROM counters WHERE id = ?");
    $stmt->execute([$id]);
    if (!$row = $stmt->fetch()) {
        json_response(['error' => 'Counter not found'], 404);
    }
    $number = $row['counter_number'];

    // Refuse to delete a counter with ticket history (FK would null out served_by_counter)
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM tickets WHERE served_by_counter = ?");
    $stmt->execute([$id]);
    if ((int)$stmt->fetch()['cnt'] > 0) {
        json_response(['error' => 'Cannot delete: this counter has served tickets. Set it to paused instead.'], 409);
    }

    $stmt = $pdo->prepare("DELETE FROM counters WHERE id = ?");
    $stmt->execute([$id]);

    audit_log($pdo, $_SESSION['user_id'], 'delete_counter', "Counter $number deleted");

    json_response(['success' => true]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    json_response(['error' => 'Method not allowed'], 405);
}

delete_counter($pdo, (int)($_GET['id'] ?? 0));
