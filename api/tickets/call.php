<?php
require_once __DIR__ . '/../../config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    json_response(['error' => 'Ticket ID required'], 400);
}

$staff_counter_id = (int)($_SESSION['counter_id'] ?? 0);
if (!$staff_counter_id) {
    json_response(['error' => 'No counter assigned to you. Please contact admin.'], 409);
}

// Check the staff's counter status (paused counters cannot call)
$stmt = $pdo->prepare("SELECT status FROM counters WHERE id = ?");
$stmt->execute([$staff_counter_id]);
$counter_row = $stmt->fetch();
if (!$counter_row) {
    json_response(['error' => 'Your counter no longer exists. Please contact admin.'], 409);
}
if ($counter_row['status'] === 'paused') {
    json_response(['error' => 'Your counter is paused. Resume it first.'], 409);
}

// Atomic claim: only succeeds if the ticket is still waiting/called
$stmt = $pdo->prepare("
    UPDATE tickets
    SET status = 'serving', called_at = NOW(), served_by_counter = ?
    WHERE id = ? AND status IN ('waiting', 'called')
");
$stmt->execute([$staff_counter_id, $id]);
if ($stmt->rowCount() === 0) {
    json_response(['error' => 'Ticket was already taken or is no longer callable'], 409);
}

// Set the counter to busy while serving
$stmt = $pdo->prepare("UPDATE counters SET status = 'busy' WHERE id = ? AND status = 'available'");
$stmt->execute([$staff_counter_id]);

$stmt = $pdo->prepare("SELECT ticket_number FROM tickets WHERE id = ?");
$stmt->execute([$id]);
$ticket_number = $stmt->fetch()['ticket_number'];

audit_log($pdo, $_SESSION['user_id'], 'call_ticket', "Called ticket $ticket_number at counter $staff_counter_id");

json_response(['success' => true, 'ticket_number' => $ticket_number]);
