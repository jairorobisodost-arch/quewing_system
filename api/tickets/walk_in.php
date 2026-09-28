<?php
require_once __DIR__ . '/../../config.php';
require_login();

// Walk-in issuance is for staff at the branch (admins and counter staff)
if (!in_array($_SESSION['user_role'] ?? '', ['admin', 'counter'], true)) {
    json_response(['error' => 'Forbidden'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$service_id     = (int)($input['service_id'] ?? 0);
$priority       = $input['priority'] ?? 'normal';
$customer_name  = trim($input['customer_name'] ?? '');
$customer_phone = trim($input['customer_phone'] ?? '');
$notes          = trim($input['notes'] ?? '');
$customer_id    = (int)($input['customer_id'] ?? 0);

if (!$service_id) {
    json_response(['error' => 'Please select a service'], 400);
}

// Imported customer: pull authoritative name + contact from the customers table
$imported = null;
if ($customer_id) {
    $cstmt = $pdo->prepare("SELECT id, client_id, center_name, client_firstname, client_middlename, client_lastname, contact_no FROM customers WHERE id = ?");
    $cstmt->execute([$customer_id]);
    $imported = $cstmt->fetch();
    if (!$imported) {
        json_response(['error' => 'Selected customer record not found'], 400);
    }
}

$svcChk = $pdo->prepare("SELECT id, name FROM services WHERE id = ? AND is_active = 1");
$svcChk->execute([$service_id]);
$svcRow = $svcChk->fetch();
if (!$svcRow) {
    json_response(['error' => 'Invalid or inactive service'], 400);
}

$allowed_priorities = ['normal', 'senior', 'pwd', 'pregnant'];
if (!in_array($priority, $allowed_priorities, true)) {
    $priority = 'normal';
}

if ($imported) {
    // Authoritative from the import: "LASTNAME, Firstname Middlename"
    $display_name = $imported['client_lastname'] . ', ' . trim($imported['client_firstname'] . ' ' . ($imported['client_middlename'] ?? ''));
    $customer_name  = $display_name;
    $customer_phone = $imported['contact_no'] ?: $customer_phone;
    $notes = trim($notes . ($notes ? ' | ' : '') . 'Member: ' . ($imported['client_id'] ?: '-') . ' · ' . ($imported['center_name'] ?: '-'));
}

try {
    $ticket_number = generate_ticket_number($pdo, get_setting($pdo, 'ticket_prefix', 'SFI'));

    $stmt = $pdo->prepare("
        INSERT INTO tickets (ticket_number, service_id, priority, customer_name, customer_phone, notes, status, created_date)
        VALUES (?, ?, ?, ?, ?, ?, 'waiting', CURDATE())
    ");
    $stmt->execute([$ticket_number, $service_id, $priority, $customer_name ?: null, $customer_phone ?: null, $notes ?: null]);
    $ticket_id = (int)$pdo->lastInsertId();
} catch (PDOException $e) {
    json_response(['error' => 'Could not issue the ticket. Please try again.'], 500);
}

// Position in line for this service (waiting only, in issue order)
$pos = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'waiting' AND service_id = ? AND id <= ?");
$pos->execute([$service_id, $ticket_id]);
$queue_position = (int)$pos->fetchColumn();

// Total waiting across the branch
$stmt = $pdo->query("SELECT COUNT(*) FROM tickets WHERE status = 'waiting'");
$waiting_total = (int)$stmt->fetchColumn();

audit_log(
    $pdo,
    $_SESSION['user_id'],
    'issue_walk_in',
    "Issued walk-in ticket $ticket_number ({$svcRow['name']}, $priority) for " . ($customer_name ?: 'walk-in customer') . ($imported ? ' [imported member #' . $imported['id'] . ']' : '')
);

json_response([
    'success'        => true,
    'id'             => $ticket_id,
    'ticket_number'  => $ticket_number,
    'service_name'   => $svcRow['name'],
    'queue_position' => $queue_position,
    'waiting_total'  => $waiting_total,
    'priority'       => $priority,
    'customer_name'  => $customer_name,
    'issued_by'      => $_SESSION['user_name'] ?? '',
    'issued_at'      => date('M j, Y g:i A'),
]);
