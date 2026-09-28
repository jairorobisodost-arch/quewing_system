<?php
require_once __DIR__ . '/../../config.php';
require_login();

function get_services($pdo) {
    $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY name");
    $services = $stmt->fetchAll();
    json_response(['services' => $services]);
}

get_services($pdo);
