<?php
require_once __DIR__ . '/../../config.php';
require_login();

function update_counter($pdo, $id) {
    $id = (int)$id;
    if ($id <= 0) {
        json_response(['error' => 'Valid counter id required'], 400);
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $status   = $input['status'] ?? null;
    $staff_id = array_key_exists('staff_id', $input) ? $input['staff_id'] : null;

    if ($status === null && !array_key_exists('staff_id', $input)) {
        json_response(['error' => 'Nothing to update'], 400);
    }

    // Permission rules:
    // - staff_id changes: admin only
    // - status changes: admin, OR the counter staff toggling their OWN counter
    if (array_key_exists('staff_id', $input) && $_SESSION['user_role'] !== 'admin') {
        json_response(['error' => 'Only admins can change counter assignments'], 403);
    }
    if ($status !== null && $_SESSION['user_role'] !== 'admin' && (int)($_SESSION['counter_id'] ?? 0) !== $id) {
        json_response(['error' => 'You can only change the status of your own counter'], 403);
    }

    $stmt = $pdo->prepare("SELECT id FROM counters WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        json_response(['error' => 'Counter not found'], 404);
    }

    if ($status !== null) {
        $allowed = ['available', 'paused', 'busy'];
        if (!in_array($status, $allowed, true)) {
            json_response(['error' => 'Invalid status'], 400);
        }
        $stmt = $pdo->prepare("UPDATE counters SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        audit_log($pdo, $_SESSION['user_id'], 'update_counter', "Counter $id status changed to $status");
    }

    if (array_key_exists('staff_id', $input)) {
        $new_staff = ($staff_id === '' || $staff_id === null || $staff_id === 0) ? null : (int)$staff_id;

        if ($new_staff !== null) {
            $stmt = $pdo->prepare("SELECT id, role FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$new_staff]);
            $staff = $stmt->fetch();
            if (!$staff) {
                json_response(['error' => 'Staff user not found or inactive'], 400);
            }
            if ($staff['role'] !== 'counter') {
                json_response(['error' => 'Only counter-role users can be assigned'], 400);
            }

            // Prevent the same staff being assigned to two counters
            $stmt = $pdo->prepare("SELECT c.counter_number FROM counters c WHERE c.staff_id = ? AND c.id != ?");
            $stmt->execute([$new_staff, $id]);
            if ($other = $stmt->fetch()) {
                json_response(['error' => 'This user is already assigned to Counter ' . $other['counter_number']], 409);
            }
        }

        $stmt = $pdo->prepare("UPDATE counters SET staff_id = ?, assigned_by_admin = ? WHERE id = ?");
        $stmt->execute([$new_staff, $new_staff !== null ? 1 : 0, $id]);
        audit_log($pdo, $_SESSION['user_id'], 'assign_staff', "Counter $id staff set to " . ($new_staff ?? 'none'));
    }

    json_response(['success' => true]);
}

update_counter($pdo, (int)($_GET['id'] ?? 0));
