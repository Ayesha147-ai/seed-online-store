<?php
// ============================================================
//   includes/update-profile.php
//   Logged-in user (kisi bhi role) ka naam/email/phone update karta hai
// ============================================================
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not logged in']);
    exit();
}

$userId = $_SESSION['user_id'];
$name   = clean($conn, $_POST['name']  ?? '');
$email  = clean($conn, $_POST['email'] ?? '');
$phone  = clean($conn, $_POST['phone'] ?? '');

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'msg' => 'Name and email are required']);
    exit();
}

// Email kisi aur user ka to nahi hai (apne ko chhod kar)
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
mysqli_stmt_bind_param($checkStmt, 'si', $email, $userId);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'msg' => 'This email is already in use']);
    exit();
}

$updateStmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'sssi', $name, $email, $phone, $userId);

if (mysqli_stmt_execute($updateStmt)) {
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;
    echo json_encode(['success' => true, 'msg' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>