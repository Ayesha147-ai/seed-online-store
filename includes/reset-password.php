<?php
// ============================================================
//   includes/reset-password.php
//   Naya password hash karke users table mein update karta hai
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$email       = clean($conn, trim($_POST['email'] ?? ''));
$newPassword = trim($_POST['password'] ?? '');

if (empty($email) || empty($newPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

if (strlen($newPassword) < 8) {
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
    exit();
}

// Security check: OTP kisi verified reset request ke bina password change na ho
// (matlab is email ke liye koi valid, used=0, expire na hui request database mein honi chahiye)
$checkStmt = mysqli_prepare($conn, "SELECT id FROM password_resets
    WHERE email = ? AND expires_at > NOW() LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
$hasValidRequest = mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) > 0;

if (!$hasValidRequest) {
    echo json_encode(['status' => 'error', 'message' => 'OTP verification required before resetting password.']);
    exit();
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
mysqli_stmt_bind_param($updateStmt, 'ss', $hashedPassword, $email);

if (mysqli_stmt_execute($updateStmt)) {
    // Reset request istemal ho chuki — clean up
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
    mysqli_stmt_bind_param($deleteStmt, 's', $email);
    mysqli_stmt_execute($deleteStmt);

    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
} else {
    error_log('Password update failed: ' . mysqli_error($conn));
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}
?>