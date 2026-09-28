<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

// SIMPLE TRANSACTION REPORT
// GET params:
//   date   = YYYY-MM-DD  (single day, default: today)
//   branch = branch name (optional)
//   center = center_name (optional)
//
// Report = list of transactions for that day:
//   ticket #, pangalan, centro, branch, service, priority, oras, counter, status

$date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'] ?? '') ? $_GET['date'] : date('Y-m-d');
$branch = trim($_GET['branch'] ?? '');
$center = trim($_GET['center'] ?? '');

// Imported-member tickets carry "Member: <client_id> · <center>" in notes.
// Join the imported member by client_id extracted from notes to get center_name + branch.
$memberJoin = "
    LEFT JOIN customers c
        ON c.client_id = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.notes, 'Member: ', -1), ' ·', 1))
";

$conds  = ["t.created_date = ?"];
$params = [$date];

if ($center !== '') {
    $conds[]  = "c.center_name = ?";
    $params[] = $center;
}
if ($branch !== '') {
    $conds[]  = "c.branch = ?";
    $params[] = $branch;
}
$where = implode(' AND ', $conds);

$stmt = $pdo->prepare("
    SELECT
        t.id,
        t.ticket_number,
        t.customer_name,
        t.priority,
        t.status,
        t.created_at,
        t.called_at,
        t.completed_at,
        t.notes,
        s.name AS service_name,
        CONCAT(u.first_name, ' ', u.last_name) AS staff_name,
        cn.counter_number,
        c.center_name,
        c.branch,
        c.client_id AS member_client_id
    FROM tickets t
    LEFT JOIN services s  ON s.id = t.service_id
    LEFT JOIN counters cn ON cn.id = t.served_by_counter
    LEFT JOIN users u     ON u.id = cn.staff_id
    $memberJoin
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT 2000
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Fallback: pull centro from notes text when the member row no longer exists
foreach ($rows as &$r) {
    if (empty($r['center_name']) && !empty($r['notes']) && strpos($r['notes'], 'Member: ') !== false) {
        $after = substr($r['notes'], strpos($r['notes'], 'Member: ') + 8);
        $parts = explode(' · ', $after, 2);
        if (isset($parts[1]) && trim($parts[1]) !== '') {
            $r['center_name'] = trim($parts[1]);
        } elseif (!empty($parts[0]) && trim($parts[0]) !== '-') {
            $r['center_name'] = trim($parts[0]);
        }
    }
    $r['is_member'] = !empty($r['member_client_id']);
}
unset($r);

// Filter options for the dropdowns
$branches = $pdo->query("
    SELECT DISTINCT branch
    FROM customers
    WHERE branch IS NOT NULL AND branch <> ''
    ORDER BY branch
")->fetchAll(PDO::FETCH_COLUMN);

$centers = $pdo->query("
    SELECT DISTINCT center_name
    FROM customers
    WHERE center_name IS NOT NULL AND center_name <> ''
    ORDER BY center_name
")->fetchAll(PDO::FETCH_COLUMN);

json_response([
    'filters' => ['date' => $date, 'branch' => $branch, 'center' => $center],
    'total'   => count($rows),
    'branches' => $branches,
    'centers'  => $centers,
    'transactions' => $rows,
]);
