<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');
header('Content-Type: application/json');

// Get customers imported within the same minute as the import log
$log_id = isset($_GET['log_id']) ? (int)$_GET['log_id'] : 0;
if (!$log_id) {
    json_response(['error' => 'log_id required'], 400);
}

// Get the import log details
$log_stmt = $pdo->prepare("
    SELECT il.*, u.username, u.first_name, u.last_name
    FROM import_logs il
    LEFT JOIN users u ON u.id = il.imported_by
    WHERE il.id = ?
");
$log_stmt->execute([$log_id]);
$log = $log_stmt->fetch();

if (!$log) {
    json_response(['error' => 'Import log not found'], 404);
}

// Get customers linked to this import log (exact match).
// Fall back to the timestamp window only for legacy rows imported before import_log_id existed.
$customers_stmt = $pdo->prepare("
    SELECT id, client_id, client_lastname, client_firstname, client_middlename,
           birthday, gender, civil_status, contact_no, address,
           mother_maiden_lastname, mother_maiden_firstname, mother_maiden_middlename,
           client_subid, center_name, branch, imported_at
    FROM customers
    WHERE imported_by = ?
      AND (
            import_log_id = ?
            OR (
                import_log_id IS NULL
                AND imported_at BETWEEN DATE_SUB(?, INTERVAL 2 MINUTE) AND DATE_ADD(?, INTERVAL 2 MINUTE)
            )
          )
    ORDER BY id ASC
");
$customers_stmt->execute([$log['imported_by'], $log['id'], $log['imported_at'], $log['imported_at']]);
$customers = $customers_stmt->fetchAll();

json_response([
    'log'       => $log,
    'customers' => $customers,
    'total'     => count($customers),
]);
