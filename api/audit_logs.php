<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');

function get_audit_logs($pdo) {
    $limit = (int)($_GET['limit'] ?? 100);
    $stmt = $pdo->prepare("SELECT al.*, u.username, u.first_name, u.last_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    $logs = $stmt->fetchAll();
    json_response(['logs' => $logs]);
}

get_audit_logs($pdo);
