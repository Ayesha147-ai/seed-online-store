<?php
// ============================================================
//   includes/get-profile.php — Logged-in user ki full details
// ============================================================

// Session aur database ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false]);
    exit();
}

// Current logged-in user ki ID hasil karo
$userId = $_SESSION['user_id'];

// Current user ki profile details database se fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT name, email, phone, role, location, created_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

// Agar user ka record nahi mila to logged out response return karo
if (!$user) {
    echo json_encode(['logged_in' => false]);
    exit();
}

// User ki profile details JSON response mein return karo
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