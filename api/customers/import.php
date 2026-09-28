<?php
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

// Must check auth after setting JSON header so errors are JSON not HTML redirects
if (!isset($_SESSION['user_id'])) {
    json_response(['error' => 'Not authenticated'], 401);
}
if ($_SESSION['user_role'] !== 'admin') {
    json_response(['error' => 'Forbidden'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'No file uploaded or upload error'], 400);
}

$file = $_FILES['csv_file']['tmp_name'];
$original_name = $_FILES['csv_file']['name'];

// Validate file extension
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    json_response(['error' => 'Only CSV files are allowed'], 400);
}

// Open CSV with UTF-8 encoding
$handle = fopen($file, 'r');
if (!$handle) {
    json_response(['error' => 'Cannot read file'], 500);
}

// Strip BOM if present (common in UTF-8 CSV from Excel)
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

// Read header row
$headers = fgetcsv($handle);
if (!$headers) {
    fclose($handle);
    json_response(['error' => 'CSV file is empty'], 400);
}

// Normalize headers: lowercase and trim
$headers = array_map(fn($h) => strtolower(trim($h)), $headers);

// Expected columns mapping (CSV header => DB column)
$expected = [
    'center_name', 'client_id', 'client_lastname', 'client_firstname',
    'client_middlename', 'birthday', 'gender', 'civil_status',
    'contact_no', 'address', 'mother_maiden_lastname',
    'mother_maiden_firstname', 'mother_maiden_middlename',
    'client_subid', 'branch'
];

// Build column index map
$col_map = [];
foreach ($expected as $col) {
    $idx = array_search($col, $headers);
    if ($idx !== false) {
        $col_map[$col] = $idx;
    }
}

if (empty($col_map)) {
    fclose($handle);
    json_response(['error' => 'No matching columns found. Please check your CSV headers.'], 400);
}

// Prepare insert statement
$cols = array_keys($col_map);
$cols[] = 'imported_by';
$cols[] = 'import_log_id';
$placeholders = implode(', ', array_fill(0, count($cols), '?'));
$col_names    = implode(', ', array_map(fn($c) => "`$c`", $cols));
$stmt = $pdo->prepare("INSERT INTO customers ($col_names) VALUES ($placeholders)");

$imported = 0;
$skipped  = 0;
$errors   = [];
$row_num  = 1;
$branch_counts = [];

$pdo->beginTransaction();

try {
    // Create the import log first so every inserted customer can be linked to it exactly
    $log_stmt = $pdo->prepare("
        INSERT INTO import_logs (file_name, imported_by, total_imported, total_skipped)
        VALUES (?, ?, 0, 0)
    ");
    $log_stmt->execute([$original_name, $_SESSION['user_id']]);
    $log_id = (int)$pdo->lastInsertId();
    while (($row = fgetcsv($handle)) !== false) {
        $row_num++;

        // Skip completely empty rows
        if (empty(array_filter($row, fn($v) => trim($v) !== ''))) {
            continue;
        }

        $values = [];
        foreach ($col_map as $col => $idx) {
            $val = isset($row[$idx]) ? trim($row[$idx]) : null;

            // Convert empty strings to null
            if ($val === '') $val = null;

            // Parse birthday — handle various date formats
            if ($col === 'birthday' && $val !== null) {
                $parsed = null;
                $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y', 'm-d-Y', 'd-m-Y', 'Y/m/d'];
                foreach ($formats as $fmt) {
                    $dt = DateTime::createFromFormat($fmt, $val);
                    if ($dt) {
                        $parsed = $dt->format('Y-m-d');
                        break;
                    }
                }
                $val = $parsed; // null if unrecognized
            }

            // Track branch stats from the CSV for the import log metadata
            if ($col === 'branch' && $val !== null && $val !== '') {
                $branch_counts[$val] = ($branch_counts[$val] ?? 0) + 1;
            }

            $values[] = $val;
        }

        // Add imported_by and the exact import log link
        $values[] = $_SESSION['user_id'];
        $values[] = $log_id;

        try {
            $stmt->execute($values);
            $imported++;
        } catch (PDOException $e) {
            $skipped++;
            $errors[] = "Row $row_num: " . $e->getMessage();
        }
    }

    // Resolve branch from the imported data itself
    $branch = null;
    if ($branch_counts) {
        arsort($branch_counts);
        $branch = key($branch_counts);
    }

    // Save final counts/branch on the import log
    $finish_stmt = $pdo->prepare("
        UPDATE import_logs SET total_imported = ?, total_skipped = ?, branch = ? WHERE id = ?
    ");
    $finish_stmt->execute([$imported, $skipped, $branch, $log_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    fclose($handle);
    json_response(['error' => 'Import failed: ' . $e->getMessage()], 500);
}

fclose($handle);

audit_log($pdo, $_SESSION['user_id'], 'csv_import', "Imported $imported customers from $original_name");

json_response([
    'success'  => true,
    'imported' => $imported,
    'skipped'  => $skipped,
    'errors'   => array_slice($errors, 0, 10),
    'message'  => "Successfully imported $imported records." . ($skipped > 0 ? " $skipped rows skipped." : '')
]);
