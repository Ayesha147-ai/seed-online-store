<?php
// ============================================================
//   includes/db.php — Database Connection
//   trackseed_db mein connect karta hai
// ============================================================

$host     = 'localhost';
$dbname   = 'trackseed_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die(json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

mysqli_set_charset($conn, 'utf8');
?>
