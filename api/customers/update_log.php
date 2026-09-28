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
$file_name = trim($input['file_name'] ?? '');
$branch = trim($input['branch'] ?? '');
if (!$log_id) {
    json_response(['error' => 'log_id required'], 400);
}

$row = $pdo->prepare("SELECT file_name, branch FROM import_logs WHERE id = ?");
$row->execute([$log_id]);
$existing = $row->fetch();
if (!$existing) {
    json_response(['error' => 'Import log not found'], 404);
}
if ($file_name === '') {
    $file_name = $existing['file_name'];
}

$stmt = $pdo->prepare("UPDATE import_logs SET file_name = ?, branch = ? WHERE id = ?");
$stmt->execute([$file_name, $branch === '' ? null : $branch, $log_id]);

audit_log($pdo, $_SESSION['user_id'], 'edit_import', "Updated import log #$log_id (file: $file_name, branch: " . ($branch === '' ? 'none' : $branch) . ')');

json_response(['success' => true, 'branch' => $branch === '' ? null : $branch]);