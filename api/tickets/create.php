<?php
require_once __DIR__ . '/../../config.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$service_id = $input['service_id'] ?? null;
$priority = $input['priority'] ?? 'normal';
$customer_name = trim($input['customer_name'] ?? '');
$customer_phone = trim($input['customer_phone'] ?? '');
$notes = trim($input['notes'] ?? '');

if (!$service_id) {
    json_response(['error' => 'Service ID required'], 400);
}

$ticket_number = generate_ticket_number($pdo, get_setting($pdo, 'ticket_prefix', 'SFI'));

$stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, service_id, priority, customer_name, customer_phone, notes, status, created_date) VALUES (?, ?, ?, ?, ?, ?, 'waiting', CURDATE())");
$stmt->execute([$ticket_number, $service_id, $priority, $customer_name, $customer_phone, $notes]);

audit_log($pdo, $_SESSION['user_id'], 'create_ticket', "Created ticket $ticket_number for service ID $service_id");

json_response(['success' => true, 'ticket_number' => $ticket_number, 'id' => $pdo->lastInsertId()]);
