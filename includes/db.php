<?php
// ============================================================
//   includes/db.php — Database Connection
//   trackseed_db mein connect karta hai
// ============================================================

$host     = 'localhost';
$dbname   = 'trackseed_db';
$username = 'root';
$password = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $username, $password, $dbname);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    die(json_encode(['error' => 'Database connection failed. Please try again later.']));
}
?>
