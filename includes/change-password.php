<?php
// ============================================================
//   includes/change-password.php
//   Logged-in user (kisi bhi role) ka password change karta hai
// ============================================================
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not logged in']);
    exit();
}

$userId  = $_SESSION['user_id'];
$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password']     ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

if (empty($current) || empty($new) || empty($confirm)) {
    echo json_encode(['success' => false, 'msg' => 'All password fields are required']);
    exit();
}

if (strlen($new) < 6) {
    echo json_encode(['success' => false, 'msg' => 'New password must be at least 6 characters']);
    exit();
}

if ($new !== $confirm) {
    echo json_encode(['success' => false, 'msg' => 'New password and confirm password do not match']);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user || !password_verify($current, $user['password'])) {
    echo json_encode(['success' => false, 'msg' => 'Current password is incorrect']);
    exit();
}

$hashed = password_hash($new, PASSWORD_DEFAULT);
mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = $userId");

echo json_encode(['success' => true, 'msg' => 'Password updated successfully!']);
?>