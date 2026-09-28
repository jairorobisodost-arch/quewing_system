<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function trend_report($pdo) {
    // days: 1..365, default 30. Optional end_date anchor (defaults to today).
    $days = (int)($_GET['days'] ?? 30);
    if ($days < 1) $days = 1;
    if ($days > 365) $days = 365;

    $end = $_GET['end_date'] ?? null;
    if (!$end || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        $end = date('Y-m-d');
    }
    $start = date('Y-m-d', strtotime("$end -" . ($days - 1) . " day"));

    $stmt = $pdo->prepare("
        SELECT t.created_date as d,
               COUNT(t.id) as total,
               COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed
        FROM tickets t
        WHERE t.created_date BETWEEN ? AND ?
        GROUP BY t.created_date
    ");
    $stmt->execute([$start, $end]);

    $map = [];
    foreach ($stmt->fetchAll() as $r) {
        $map[$r['d']] = ['total' => (int)$r['total'], 'completed' => (int)$r['completed']];
    }

    // Zero-fill every day in the range so the chart has a continuous line
    $trend = [];
    $grand_total = 0;
    $best_day = null;
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("$end -$i day"));
        $total = $map[$d]['total'] ?? 0;
        $grand_total += $total;
        if ($best_day === null || $total > $best_day['total']) {
            $best_day = ['date' => $d, 'label' => date('M j', strtotime($d)), 'total' => $total];
        }
        $trend[] = [
            'date'      => $d,
            'label'     => date('M j', strtotime($d)),
            'total'     => $total,
            'completed' => $map[$d]['completed'] ?? 0,
        ];
    }

    json_response([
        'days'         => $days,
        'start'        => $start,
        'end'          => $end,
        'grand_total'  => $grand_total,
        'avg_per_day'  => round($grand_total / $days, 1),
        'best_day'     => $best_day,
        'trend'        => $trend,
    ]);
}

trend_report($pdo);
