<?php
require_once __DIR__ . '/../../config.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['counter_id']) && $_SESSION['counter_id']) {
        // Only release counters that were auto-grabbed at login.
        // Admin-made assignments survive logout so the staff keeps their counter.
        $stmt = $pdo->prepare("SELECT assigned_by_admin FROM counters WHERE id = ?");
        $stmt->execute([$_SESSION['counter_id']]);
        $counter = $stmt->fetch();

        if ($counter && !(int)$counter['assigned_by_admin']) {
            $stmt = $pdo->prepare("UPDATE counters SET staff_id = NULL, status = 'available', assigned_by_admin = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['counter_id']]);
        }
    }
    audit_log($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
    session_unset();
    session_destroy();
}

header('Location: /quewing_system/landing.php');
exit;