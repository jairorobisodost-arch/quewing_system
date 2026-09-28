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

// Only the counter holding this ticket may complete it
$my_counter = (int)($_SESSION['counter_id'] ?? 0);
if (!$ticket || (int)$ticket['served_by_counter'] !== $my_counter) {
    json_response(['error' => 'This ticket is not being served by your counter'], 403);
}

if (!$ticket) {
    json_response(['error' => 'Ticket not found'], 404);
}

// Compute duration inside MySQL so PHP/MySQL timezone mismatches can't produce negative values
$stmt = $pdo->prepare("UPDATE tickets SET status = 'completed', completed_at = NOW(), service_duration = TIMESTAMPDIFF(SECOND, called_at, NOW()) WHERE id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("SELECT service_duration FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$duration = (int)$stmt->fetch()['service_duration'];

// Free the counter back up (only from busy - never un-pause manually paused counters)
$my_counter = (int)($_SESSION['counter_id'] ?? 0);
if ($my_counter) {
    $stmt = $pdo->prepare("UPDATE counters SET status = 'available' WHERE id = ? AND status = 'busy'");
    $stmt->execute([$my_counter]);
}

audit_log($pdo, $_SESSION['user_id'], 'complete_ticket', "Completed ticket $ticket[ticket_number], duration: {$duration}s");

json_response(['success' => true, 'duration' => $duration]);