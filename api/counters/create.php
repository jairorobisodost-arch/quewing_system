<?php
require_once __DIR__ . '/../../config.php';
require_login();
require_role('admin');

function create_counter($pdo) {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $number   = (int)($input['counter_number'] ?? 0);
    $staff_id = $input['staff_id'] ?? null;

    if ($number <= 0) {
        json_response(['error' => 'Counter number must be a positive number'], 400);
    }

    $stmt = $pdo->prepare("SELECT id FROM counters WHERE counter_number = ?");
    $stmt->execute([$number]);
    if ($stmt->fetch()) {
        json_response(['error' => "Counter $number already exists"], 409);
    }

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
        $stmt = $pdo->prepare("SELECT counter_number FROM counters WHERE staff_id = ?");
        $stmt->execute([$new_staff]);
        if ($other = $stmt->fetch()) {
            json_response(['error' => 'This user is already assigned to Counter ' . $other['counter_number']], 409);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO counters (counter_number, status, staff_id, assigned_by_admin) VALUES (?, 'available', ?, ?)");
    $stmt->execute([$number, $new_staff, $new_staff !== null ? 1 : 0]);
    $id = (int)$pdo->lastInsertId();

    audit_log($pdo, $_SESSION['user_id'], 'create_counter', "Counter $number created" . ($new_staff ? " and assigned to staff $new_staff" : ''));

    json_response(['success' => true, 'id' => $id, 'counter_number' => $number], 201);
}

create_counter($pdo);
