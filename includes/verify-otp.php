<?php
// ============================================================
//   includes/verify-otp.php
//   User ka entered OTP database wali se compare karta hai
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$email = clean($conn, trim($_POST['email'] ?? ''));
$otp   = clean($conn, trim($_POST['otp']   ?? ''));

if (empty($email) || empty($otp)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and OTP are required.']);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id FROM password_resets
    WHERE email = ? AND otp = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ss', $email, $otp);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reset  = mysqli_fetch_assoc($result);

if ($reset) {
    echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or expired OTP code.']);
}
?>