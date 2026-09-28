<?php
require_once __DIR__ . '/../../config.php';
require_login();

function my_counter($pdo) {
    if ($_SESSION['user_role'] !== 'counter') {
        json_response(['error' => 'Forbidden'], 403);
    }

    $my_id = (int)$_SESSION['user_id'];
    $counter_id = (int)($_SESSION['counter_id'] ?? 0);

    // ── LIVE counter lookup (session may be stale after admin reassignment) ──
    $counter = null;
    $live_adopted = false;

    $stmt = $pdo->prepare("SELECT id, counter_number, status, staff_id, assigned_by_admin FROM counters WHERE staff_id = ? LIMIT 1");
    $stmt->execute([$my_id]);
    $counter = $stmt->fetch();

    if ($counter) {
        $live_counter_id = (int)$counter['id'];
        // Adopt live id into session if it differs (handles admin reassignments mid-session)
        if ($counter_id !== $live_counter_id) {
            $_SESSION['counter_id'] = $live_counter_id;
            $_SESSION['counter_number'] = (int)$counter['counter_number'];
        }
        $counter_id = $live_counter_id;
        $live_adopted = true;
    } elseif ($counter_id) {
        // Session says I have a counter but DB disagrees — session is stale, drop it
        $_SESSION['counter_id'] = null;
        $_SESSION['counter_number'] = null;
        $counter_id = 0;
    }

    // ── Today's stats for this counter ──
    $stats = ['served' => 0, 'avg_duration' => null, 'no_show' => 0];
    if ($counter_id) {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as served,
                AVG(CASE WHEN t.status = 'completed' THEN TIMESTAMPDIFF(SECOND, t.called_at, t.completed_at) END) as avg_duration,
                COUNT(CASE WHEN t.status = 'no_show' THEN 1 END) as no_show
            FROM tickets t
            WHERE t.served_by_counter = ? AND t.created_date = CURDATE()
        ");
        $stmt->execute([$counter_id]);
        $row = $stmt->fetch();
        $stats = [
            'served'       => (int)$row['served'],
            'avg_duration' => $row['avg_duration'] !== null ? (int)$row['avg_duration'] : null,
            'no_show'      => (int)$row['no_show'],
        ];
    }

    // ── Total waiting right now (whole branch) ──
    $stmt = $pdo->query("SELECT COUNT(*) as c FROM tickets WHERE status = 'waiting'");
    $waiting_total = (int)$stmt->fetch()['c'];

    json_response([
        'counter' => $counter ? [
            'id'                => (int)$counter['id'],
            'counter_number'    => (int)$counter['counter_number'],
            'status'            => $counter['status'],
            'assigned_by_admin' => (bool)$counter['assigned_by_admin'],
        ] : null,
        'staff' => [
            'name'   => $_SESSION['user_name'] ?? '',
            'paused' => $counter ? $counter['status'] === 'paused' : false,
        ],
        'stats'         => $stats,
        'waiting_total' => $waiting_total,
        'live_adopted'  => $live_adopted,
    ]);
}

my_counter($pdo);
