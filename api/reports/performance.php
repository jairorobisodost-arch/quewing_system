<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function performance_report($pdo) {
    $start = $_GET['start_date'] ?? null;
    $end   = $_GET['end_date']   ?? null;

    if ($start && $end) {
        $dateCond   = "t.created_date BETWEEN ? AND ?";
        $dateParams = [$start, $end];
    } else {
        $today = $start ?? date('Y-m-d');
        $dateCond   = "t.created_date = ?";
        $dateParams = [$today];
    }

    // Service stats (fixed: services table has no color column)
    $stmt = $pdo->prepare("
        SELECT s.id, s.name as service_name,
               COUNT(t.id) as total,
               COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
               COUNT(CASE WHEN t.status = 'no_show' THEN 1 END) as no_show,
               COUNT(CASE WHEN t.status IN ('waiting','called','serving') THEN 1 END) as active,
               AVG(CASE WHEN t.status = 'completed' THEN TIMESTAMPDIFF(SECOND, t.called_at, t.completed_at) END) as avg_duration
        FROM services s
        LEFT JOIN tickets t ON s.id = t.service_id AND $dateCond
        GROUP BY s.id, s.name
        ORDER BY total DESC, s.name ASC
    ");
    $stmt->execute($dateParams);
    $services = array_map(function ($r) {
        $r['total']        = (int)$r['total'];
        $r['completed']    = (int)$r['completed'];
        $r['no_show']      = (int)$r['no_show'];
        $r['active']       = (int)$r['active'];
        $r['avg_duration'] = $r['avg_duration'] !== null ? (int)$r['avg_duration'] : null;
        return $r;
    }, $stmt->fetchAll());

    // Counter performance
    $stmt = $pdo->prepare("
        SELECT c.id, c.counter_number, c.status,
               u.first_name, u.last_name,
               COUNT(t.id) as served,
               COUNT(CASE WHEN t.priority = 'senior' THEN 1 END) as senior_served,
               COUNT(CASE WHEN t.priority IN ('pwd','pregnant') THEN 1 END) as pwd_pregnant_served,
               AVG(CASE WHEN t.status = 'completed' THEN TIMESTAMPDIFF(SECOND, t.called_at, t.completed_at) END) as avg_duration
        FROM counters c
        LEFT JOIN users u ON c.staff_id = u.id
        LEFT JOIN tickets t ON c.id = t.served_by_counter AND $dateCond AND t.status = 'completed'
        GROUP BY c.id, c.counter_number, c.status, u.first_name, u.last_name
        ORDER BY c.counter_number
    ");
    $stmt->execute($dateParams);
    $counters = array_map(function ($r) {
        $r['served']       = (int)$r['served'];
        $r['senior_served'] = (int)$r['senior_served'];
        $r['pwd_pregnant_served'] = (int)$r['pwd_pregnant_served'];
        $r['avg_duration'] = $r['avg_duration'] !== null ? (int)$r['avg_duration'] : null;
        return $r;
    }, $stmt->fetchAll());

    json_response([
        'service_stats'       => $services,
        'counter_performance' => $counters
    ]);
}

performance_report($pdo);
