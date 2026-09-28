<?php
require_once __DIR__ . '/../../config.php';
require_login();

function search_customers($pdo) {
    $q = trim($_GET['q'] ?? '');
    if (mb_strlen($q) < 2) {
        json_response(['customers' => []]);
    }

    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT id, client_id, center_name,
               client_firstname, client_middlename, client_lastname,
               contact_no, birthday, branch
        FROM customers
        WHERE client_lastname LIKE ? OR client_firstname LIKE ? 
           OR client_id LIKE ? OR center_name LIKE ? OR contact_no LIKE ?
        ORDER BY client_lastname, client_firstname
        LIMIT 10
    ");
    $stmt->execute([$like, $like, $like, $like, $like]);
    $customers = $stmt->fetchAll();

    json_response(['customers' => $customers]);
}

search_customers($pdo);
