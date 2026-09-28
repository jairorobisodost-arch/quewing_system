<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}
$log_id = isset($input['log_id']) ? (int)$input['log_id'] : 0;
if (!$log_id) {
    json_response(['error' => 'log_id required'], 400);
}

$log_stmt = $pdo->prepare("SELECT id, file_name FROM import_logs WHERE id = ?");
$log_stmt->execute([$log_id]);
$log = $log_stmt->fetch();
if (!$log) {
    json_response(['error' => 'Import log not found'], 404);
}

$pdo->beginTransaction();
try {
    $del_customers = $pdo->prepare("DELETE FROM customers WHERE import_log_id = ?");
    $del_customers->execute([$log_id]);
    $cust_count = $del_customers->rowCount();

    $del_log = $pdo->prepare("DELETE FROM import_logs WHERE id = ?");
    $del_log->execute([$log_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    json_response(['error' => 'Delete failed: ' . $e->getMessage()], 500);
}

audit_log($pdo, $_SESSION['user_id'], 'delete_import', "Deleted import log #$log_id ({$log['file_name']}) with $cust_count customers");

json_response([
    'success'           => true,
    'deleted_log'       => $log_id,
    'deleted_customers' => $cust_count,
]);