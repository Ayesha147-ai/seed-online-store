<?php
// ============================================================
//   includes/send-otp.php
//   Email check karta hai, OTP generate karke password_resets
//   table mein save karta hai. Demo mode — OTP email nahi hoti,
//   response mein hi wapas bhej di jati hai (FYP ke liye).
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$email = clean($conn, trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit();
}

// 1. Check karo user registered hai ya nahi
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'This email is not registered with us.']);
    exit();
}

$otp = rand(100000, 999999);

// 2. Isi email ke purane OTPs hata do
$deleteStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
mysqli_stmt_bind_param($deleteStmt, 's', $email);
mysqli_stmt_execute($deleteStmt);

// 3. Naya OTP save karo — expires_at ko MySQL se hi calculate karwao
// (NOW() + 10 minute), PHP ke date() se nahi — taako timezone mismatch
// se OTP turant "expired" na dikhe
$insertStmt = mysqli_prepare($conn, "INSERT INTO password_resets (email, otp, expires_at, used)
    VALUES (?, ?, NOW() + INTERVAL 10 MINUTE, 0)");
mysqli_stmt_bind_param($insertStmt, 'ss', $email, $otp);

if (mysqli_stmt_execute($insertStmt)) {
    echo json_encode([
        'status'   => 'success',
        'message'  => 'OTP generated successfully.',
        'demo_otp' => $otp   // Sirf demo/FYP ke liye — production mein yeh kabhi frontend ko nahi bhejte
    ]);
} else {
    error_log('OTP insert failed: ' . mysqli_error($conn));
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate OTP. Please try again.']);
}
?>