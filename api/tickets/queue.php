<?php
require_once __DIR__ . '/../../config.php';

$stmt = $pdo->prepare("
    SELECT t.*, s.name as service_name, c.counter_number
    FROM tickets t
    LEFT JOIN services s ON t.service_id = s.id
    LEFT JOIN counters c ON t.served_by_counter = c.id
    WHERE t.status IN ('waiting', 'called', 'serving')
    ORDER BY 
        CASE t.priority 
            WHEN 'senior' THEN 1 WHEN 'pregnant' THEN 2 WHEN 'pwd' THEN 3 ELSE 4 
        END,
        t.created_at ASC
    LIMIT 5
");
$stmt->execute();
$tickets = $stmt->fetchAll();

$currentStmt = $pdo->prepare("
    SELECT t.*, s.name as service_name, c.counter_number
    FROM tickets t
    LEFT JOIN services s ON t.service_id = s.id
    LEFT JOIN counters c ON t.served_by_counter = c.id
    WHERE t.status = 'serving'
    LIMIT 1
");
$currentStmt->execute();
$current = $currentStmt->fetch();

// TV announcement (admin-managed via settings)
$announcement = ['text' => '', 'active' => false];
try {
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ('announcement_text', 'announcement_active')");
    $stmt->execute();
    $ann = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $announcement = [
        'text'   => (string)($ann['announcement_text'] ?? ''),
        'active' => ($ann['announcement_active'] ?? '0') === '1' && trim((string)($ann['announcement_text'] ?? '')) !== '',
    ];
} catch (PDOException $e) {
    // settings table unavailable - keep defaults
}

json_response(['tickets' => $tickets, 'current' => $current, 'announcement' => $announcement]);
