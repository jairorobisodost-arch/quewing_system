<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function daily_report($pdo) {
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

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets t WHERE $dateCond");
    $stmt->execute($dateParams);
    $total = (int)$stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM tickets t WHERE $dateCond AND t.status = 'completed'");
    $stmt->execute($dateParams);
    $completed = (int)$stmt->fetch()['completed'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as no_show FROM tickets t WHERE $dateCond AND t.status = 'no_show'");
    $stmt->execute($dateParams);
    $no_show = (int)$stmt->fetch()['no_show'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM tickets t WHERE $dateCond AND t.status IN ('waiting','called','serving')");
    $stmt->execute($dateParams);
    $pending = (int)$stmt->fetch()['pending'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as serving FROM tickets t WHERE $dateCond AND t.status = 'serving'");
    $stmt->execute($dateParams);
    $serving = (int)$stmt->fetch()['serving'];

    $stmt = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(SECOND, t.created_at, t.called_at)) as avg_wait FROM tickets t WHERE $dateCond AND t.status = 'completed' AND t.called_at IS NOT NULL");
    $stmt->execute($dateParams);
    $avg_wait = (int)($stmt->fetch()['avg_wait'] ?: 0);

    $stmt = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(SECOND, t.called_at, t.completed_at)) as avg_service FROM tickets t WHERE $dateCond AND t.status = 'completed' AND t.called_at IS NOT NULL AND t.completed_at IS NOT NULL");
    $stmt->execute($dateParams);
    $avg_service = (int)($stmt->fetch()['avg_service'] ?: 0);

    // Counter stats (fixed: counters table has no created_date column)
    $stmt = $pdo->prepare("
        SELECT c.id, c.counter_number, c.status,
               CONCAT(u.first_name, ' ', u.last_name) as staff_name,
               COUNT(t.id) as tickets_served
        FROM counters c
        LEFT JOIN users u ON c.staff_id = u.id
        LEFT JOIN tickets t ON c.id = t.served_by_counter
            AND $dateCond AND t.status = 'completed'
        GROUP BY c.id
        ORDER BY c.counter_number
    ");
    $stmt->execute($dateParams);
    $counters = $stmt->fetchAll();

    // Priority breakdown
    $stmt = $pdo->prepare("SELECT priority, COUNT(*) as cnt FROM tickets t WHERE $dateCond GROUP BY priority");
    $stmt->execute($dateParams);
    $priority_breakdown = [];
    foreach ($stmt->fetchAll() as $row) {
        $priority_breakdown[$row['priority']] = (int)$row['cnt'];
    }

    // Hourly ticket inflow (FIX: HOUR() on datetime, not on DATE — old version always returned NULL)
    $stmt = $pdo->prepare("
        SELECT HOUR(t.created_at) as hour,
               COUNT(t.id) as total,
               COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed
        FROM tickets t
        WHERE $dateCond
        GROUP BY HOUR(t.created_at)
        ORDER BY hour
    ");
    $stmt->execute($dateParams);
    $hourly = array_map(function ($r) {
        return [
            'hour'      => (int)$r['hour'],
            'total'     => (int)$r['total'],
            'completed' => (int)$r['completed'],
        ];
    }, $stmt->fetchAll());

    json_response([
        'total_tickets'        => $total,
        'completed'            => $completed,
        'no_show'              => $no_show,
        'pending'              => $pending,
        'serving'              => $serving,
        'avg_wait_seconds'     => $avg_wait,
        'avg_wait_formatted'   => $avg_wait ? sprintf('%dm %ds', floor($avg_wait/60), $avg_wait%60) : '—',
        'avg_service_seconds'  => $avg_service,
        'avg_service_formatted'=> $avg_service ? sprintf('%dm %ds', floor($avg_service/60), $avg_service%60) : '—',
        'completion_rate'      => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
        'priority_breakdown'   => $priority_breakdown,
        'hourly'               => $hourly,
        'date_range'           => ['start' => $start ?? date('Y-m-d'), 'end' => $end ?? date('Y-m-d')],
        'counter_stats'        => $counters,
    ]);
}

daily_report($pdo);
