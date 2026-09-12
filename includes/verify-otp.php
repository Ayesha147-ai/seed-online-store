<?php
// ============================================================
//   includes/verify-otp.php
//   User ka entered OTP database wali se compare karta hai
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

// User ka email aur entered OTP form data se hasil karo
$email = clean($conn, trim($_POST['email'] ?? ''));
$otp   = clean($conn, trim($_POST['otp']   ?? ''));

// Check karo ke email aur OTP dono provide kiye gaye hain
if (empty($email) || empty($otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required.']);
    exit();
}

// Database mein email, OTP, unused status aur expiry ko verify karo
$stmt = mysqli_prepare($conn, "SELECT id FROM password_resets
    WHERE email = ? AND otp = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $email, $otp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reset  = mysqli_fetch_assoc($result);

// Agar valid OTP mil gaya hai to verification successful response do
if ($reset) {
    echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully.']);
} else {
    // Agar OTP wrong ya expired hai to error response do
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP code.']);
}
?>