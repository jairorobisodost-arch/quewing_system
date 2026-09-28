<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');

function announcement_api($pdo) {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ('announcement_text', 'announcement_active', 'announcement_updated_at')");
        $stmt->execute();
        $ann = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $text = (string)($ann['announcement_text'] ?? '');
        json_response([
            'text'        => $text,
            'active'      => ($ann['announcement_active'] ?? '0') === '1' && trim($text) !== '',
            'updated_at'  => $ann['announcement_updated_at'] ?? null,
        ]);
    }

    if ($method === 'POST' || $method === 'PATCH') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        if (!array_key_exists('text', $input) && !array_key_exists('active', $input)) {
            json_response(['error' => 'Nothing to update'], 400);
        }

        $text   = array_key_exists('text', $input) ? trim((string)$input['text']) : null;
        $active = array_key_exists('active', $input) ? !empty($input['active']) : null;

        if ($text !== null && mb_strlen($text) > 300) {
            json_response(['error' => 'Announcement must be 300 characters or less'], 400);
        }

        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        if ($text !== null) {
            $stmt->execute(['announcement_text', $text]);
        }
        if ($active !== null) {
            // Do not allow turning it on with empty text
            if ($active) {
                $chk = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = 'announcement_text'");
                $chk->execute();
                $row = $chk->fetch();
                if (!$row || trim((string)$row['value']) === '') {
                    json_response(['error' => 'Cannot activate an empty announcement. Add text first.'], 400);
                }
            }
            $stmt->execute(['announcement_active', $active ? '1' : '0']);
        }

        $stmt->execute(['announcement_updated_at', date('Y-m-d H:i:s')]);

        audit_log($pdo, $_SESSION['user_id'], 'update_announcement', $active === false ? 'Announcement turned OFF' : 'Announcement updated: ' . mb_substr($text ?? '(unchanged)', 0, 100));

        json_response(['success' => true]);
    }

    json_response(['error' => 'Method not allowed'], 405);
}

announcement_api($pdo);
