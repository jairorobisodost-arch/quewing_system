<?php
require_once __DIR__ . '/../../config.php';
require_login();

function get_counters($pdo) {
    $stmt = $pdo->query("SELECT c.*, u.first_name, u.last_name FROM counters c LEFT JOIN users u ON c.staff_id = u.id ORDER BY c.counter_number");
    $counters = $stmt->fetchAll();
    json_response(['counters' => $counters]);
}

get_counters($pdo);
