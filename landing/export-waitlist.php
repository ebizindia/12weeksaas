<?php
/**
 * Export Waitlist to CSV
 */

session_start();

// Check authentication
if (!isset($_SESSION['waitlist_admin'])) {
    header('Location: admin.php');
    exit;
}

// Connect to database
$config_file = dirname(__DIR__) . '/config.php';
if (file_exists($config_file)) {
    require_once $config_file;
    require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';
    \eBizIndia\PDOConn::connectToDB('mysql');
    $db = \eBizIndia\PDOConn::getConnection();
    $table_prefix = CONST_TBL_PREFIX ?? '';
} else {
    $dsn = "mysql:host=localhost;dbname=your_database;charset=utf8mb4";
    $db = new PDO($dsn, 'your_username', 'your_password', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $table_prefix = '';
}

// Get all waitlist entries
$sql = "SELECT * FROM `{$table_prefix}waitlist` ORDER BY created_at DESC";
$stmt = $db->query($sql);
$entries = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="waitlist_export_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, [
    'ID',
    'Name',
    'Email',
    'Company',
    'Title',
    'Status',
    'IP Address',
    'Joined Date',
    'Invited Date',
    'Converted Date',
    'Notes'
]);

// Write data rows
foreach ($entries as $entry) {
    fputcsv($output, [
        $entry['id'],
        $entry['name'],
        $entry['email'],
        $entry['company'],
        $entry['title'],
        $entry['status'],
        $entry['ip_address'],
        $entry['created_at'],
        $entry['invited_at'],
        $entry['converted_at'],
        $entry['notes']
    ]);
}

fclose($output);
exit;
