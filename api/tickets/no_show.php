<?php
require_once __DIR__ . '/../../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    json_response(['error' => 'Ticket ID required'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND status IN ('called', 'serving')");
$stmt->execute([$id]);
$ticket = $stmt->fetch();

// Only the counter holding this ticket may mark it no-show
$my_counter = (int)($_SESSION['counter_id'] ?? 0);
if (!$ticket || (int)$ticket['served_by_counter'] !== $my_counter) {
    json_response(['error' => 'This ticket is not being served by your counter'], 403);
}

$stmt = $pdo->prepare("UPDATE tickets SET status = 'no_show', completed_at = NOW() WHERE id = ?");
$stmt->execute([$id]);

// Free the counter back up (only from busy - never un-pause manually paused counters)
$my_counter = (int)($_SESSION['counter_id'] ?? 0);
if ($my_counter) {
    $stmt = $pdo->prepare("UPDATE counters SET status = 'available' WHERE id = ? AND status = 'busy'");
    $stmt->execute([$my_counter]);
}

audit_log($pdo, $_SESSION['user_id'], 'no_show', "Ticket $ticket[ticket_number] marked as no-show");

json_response(['success' => true]);