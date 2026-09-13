<?php
// ============================================================
//   includes/send-otp.php
//   Email check karta hai, OTP generate karke password_resets
//   table mein save karta hai. Demo mode — OTP email nahi hoti,
//   response mein hi wapas bhej di jati hai (FYP ke liye).
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

// User ka email form data se hasil aur sanitize karo
$email = clean($conn, trim($_POST['email'] ?? ''));

// Email empty hai ya valid format mein nahi hai to error return karo
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    exit();
}

// 1. Check karo user registered hai ya nahi
// Email ke basis par user ka record database se check karo
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
$result = mysqli_stmt_get_result($checkStmt);

// Agar email registered nahi hai to OTP generate na karo
if (mysqli_num_rows($result) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'This email is not registered with us.']);
    exit();
}

// 6 digit ka random OTP generate karo
$otp = rand(100000, 999999);

// 2. Isi email ke purane OTPs hata do
// Naya OTP save karne se pehle purane reset records remove karo
$deleteStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
mysqli_stmt_bind_param($deleteStmt, 's', $email);
mysqli_stmt_execute($deleteStmt);

// 3. Naya OTP save karo — expires_at ko MySQL se hi calculate karwao
// (NOW() + 10 minute), PHP ke date() se nahi — taako timezone mismatch
// se OTP turant "expired" na dikhe
$insertStmt = mysqli_prepare($conn, "INSERT INTO password_resets (email, otp, expires_at, used)
    VALUES (?, ?, NOW() + INTERVAL 10 MINUTE, 0)");
mysqli_stmt_bind_param($insertStmt, 'ss', $email, $otp);

// Check karo ke OTP successfully database mein save hua hai ya nahi
if (mysqli_stmt_execute($insertStmt)) {
    // Demo/FYP mode mein generated OTP response ke andar send karo
    echo json_encode([
        'status'   => 'success',
        'message'  => 'OTP generated successfully.',
        'demo_otp' => $otp   // Sirf demo/FYP ke liye — production mein yeh kabhi frontend ko nahi bhejte
    ]);
} else {
    // OTP save karne ki database error ko log karo
    error_log('OTP insert failed: ' . mysqli_error($conn));
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate OTP. Please try again.']);
}
?>