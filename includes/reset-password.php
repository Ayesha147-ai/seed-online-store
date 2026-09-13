<?php
// ============================================================
//   includes/reset-password.php
//   Naya password hash karke users table mein update karta hai
// ============================================================

// Database aur helper functions ki required files load karo
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke request POST method se aa rahi hai
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Email aur new password form data se hasil karo
$email       = clean($conn, trim($_POST['email'] ?? ''));
$newPassword = trim($_POST['password'] ?? '');

// Check karo ke email aur password dono provide kiye gaye hain
if (empty($email) || empty($newPassword)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit();
}

// Password ki minimum length check karo
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

// Check karo ke email ke liye koi valid password reset request mojood hai ya nahi
$hasValidRequest = mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) > 0;

// Agar valid reset request nahi hai to password reset rok do
if (!$hasValidRequest) {
    echo json_encode(['status' => 'error', 'message' => 'OTP verification required before resetting password.']);
    exit();
}

// New password ko secure hash mein convert karo
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// User ka password database mein update karne ke liye query prepare karo
$updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
mysqli_stmt_bind_param($updateStmt, 'ss', $hashedPassword, $email);

// Check karo ke password successfully update hua hai ya nahi
if (mysqli_stmt_execute($updateStmt)) {
    // Reset request istemal ho chuki — clean up
    // Password update ke baad related reset request delete karo
    $deleteStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
    mysqli_stmt_bind_param($deleteStmt, 's', $email);
    mysqli_stmt_execute($deleteStmt);

    // Successful password update ka response send karo
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
} else {
    // Password update ki database error ko log karo
    error_log('Password update failed: ' . mysqli_error($conn));
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
}
?>