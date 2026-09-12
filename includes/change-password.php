<?php
// ============================================================
//   includes/change-password.php
//   Logged-in user (kisi bhi role) ka password change karta hai
// ============================================================

// Session aur database ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not logged in']);
    exit();
}

// Current user ki ID aur password fields ki values hasil karo
$userId  = $_SESSION['user_id'];
$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password']     ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

// Check karo ke tamam password fields fill ki gayi hain
if (empty($current) || empty($new) || empty($confirm)) {
    echo json_encode(['success' => false, 'msg' => 'All password fields are required']);
    exit();
}

// Check karo ke new password minimum 6 characters ka hai
if (strlen($new) < 6) {
    echo json_encode(['success' => false, 'msg' => 'New password must be at least 6 characters']);
    exit();
}

// Check karo ke new password aur confirm password match karte hain
if ($new !== $confirm) {
    echo json_encode(['success' => false, 'msg' => 'New password and confirm password do not match']);
    exit();
}

// Current user ka existing hashed password database se fetch karo
$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

// Current password ko stored hashed password ke against verify karo
if (!$user || !password_verify($current, $user['password'])) {
    echo json_encode(['success' => false, 'msg' => 'Current password is incorrect']);
    exit();
}

// New password ko securely hash karo aur database mein update karo
$hashed = password_hash($new, PASSWORD_DEFAULT);
$updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'si', $hashed, $userId);
mysqli_stmt_execute($updateStmt);

// Password successfully update hone ka response send karo
echo json_encode(['success' => true, 'msg' => 'Password updated successfully!']);
?>