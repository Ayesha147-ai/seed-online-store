<?php
// ============================================================
//   includes/db.php — Database Connection
//   trackseed_db mein connect karta hai
// ============================================================

// Database connection ke liye server, database, username aur password define karo
$host     = 'localhost';
$dbname   = 'trackseed_db';
$username = 'root';
$password = '';

// MySQLi errors ko exceptions ki form mein report karne ke liye enable karo
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection establish karo aur UTF-8 charset set karo
try {
    $conn = mysqli_connect($host, $username, $password, $dbname);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Database connection error ko log karo aur user ko generic error response do
    error_log('DB connection failed: ' . $e->getMessage());
    die(json_encode(['error' => 'Database connection failed. Please try again later.']));
}
?>