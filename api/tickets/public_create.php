<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$service_id    = $input['service_id'] ?? null;
$priority      = $input['priority'] ?? 'normal';
$customer_name = trim($input['customer_name'] ?? '');
$counter_id    = $input['counter_id'] ?? null;

if (!$service_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Service is required']);
    exit;
}

// Validate that the service actually exists (avoid FK errors -> clean JSON)
$svcChk = $pdo->prepare("SELECT id, name FROM services WHERE id = ? AND is_active = 1");
$svcChk->execute([$service_id]);
$svcRow = $svcChk->fetch();
if (!$svcRow) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid service selected']);
    exit;
}

$allowed_priorities = ['normal', 'senior', 'pwd', 'pregnant'];
if (!in_array($priority, $allowed_priorities)) {
    $priority = 'normal';
}

try {
    $ticket_number = generate_ticket_number($pdo, get_setting($pdo, 'ticket_prefix', 'SFI'));

    $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, service_id, priority, customer_name, status, created_date) VALUES (?, ?, ?, ?, 'waiting', CURDATE())");
    $stmt->execute([$ticket_number, $service_id, $priority, $customer_name]);
    $ticket_id = $pdo->lastInsertId();
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Could not issue the ticket. Please try again.']);
    exit;
}

// Get service name for response
$service_name = $svcRow['name'];

// Count queue position
$pos = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'waiting' AND service_id = ? AND id <= ?");
$pos->execute([$service_id, $ticket_id]);
$queue_position = (int)$pos->fetchColumn();

// Count queue position
$pos = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'waiting' AND service_id = ? AND id <= ?");
$pos->execute([$service_id, $ticket_id]);
$queue_position = (int)$pos->fetchColumn();

echo json_encode([
    'success'        => true,
    'ticket_number'  => $ticket_number,
    'service_name'   => $service_name,
    'queue_position' => $queue_position,
    'priority'       => $priority,
    'customer_name'  => $customer_name,
]);
