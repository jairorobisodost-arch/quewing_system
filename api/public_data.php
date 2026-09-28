<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$services = $pdo->query("SELECT id, name FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();
$counters = $pdo->query("SELECT id, counter_number FROM counters ORDER BY counter_number")->fetchAll();

// Customers — handle gracefully if table doesn't exist yet
$customers = [];
try {
    $customers = $pdo->query("
        SELECT id, client_id, client_firstname, client_lastname, client_middlename, center_name
        FROM customers
        ORDER BY client_lastname, client_firstname
    ")->fetchAll();
} catch (PDOException $e) {
    // Table may not exist yet — return empty array
    $customers = [];
}

echo json_encode([
    'services'  => $services,
    'counters'  => $counters,
    'customers' => $customers,
]); 
