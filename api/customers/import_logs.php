<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');
header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$limit = max(1, min(100, $limit));

// Latest single upload
if (isset($_GET['latest'])) {
    $stmt = $pdo->prepare("
        SELECT il.*, u.username, u.first_name, u.last_name
        FROM import_logs il
        LEFT JOIN users u ON u.id = il.imported_by
        ORDER BY il.imported_at DESC
        LIMIT 1
    ");
    $stmt->execute();
    $row = $stmt->fetch();
    json_response(['log' => $row ?: null]);
}

// All logs paginated
$stmt = $pdo->prepare("
    SELECT il.*, u.username, u.first_name, u.last_name
    FROM import_logs il
    LEFT JOIN users u ON u.id = il.imported_by
    ORDER BY il.imported_at DESC
    LIMIT ?
");
$stmt->execute([$limit]);
$logs = $stmt->fetchAll();

json_response(['logs' => $logs]);
