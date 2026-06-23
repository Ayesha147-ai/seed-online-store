<?php
// ============================================================
//   includes/get-profile.php — Logged-in user ki full details
// ============================================================
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false]);
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT name, email, phone, role, location, created_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user) {
    echo json_encode(['logged_in' => false]);
    exit();
}

echo json_encode([
    'logged_in'    => true,
    'name'         => $user['name'],
    'email'        => $user['email'],
    'phone'        => $user['phone'],
    'role'         => $user['role'],
    'location'     => $user['location'],
    'member_since' => date('d M Y', strtotime($user['created_at']))
]);
?>