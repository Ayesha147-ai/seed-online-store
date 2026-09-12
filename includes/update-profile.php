<?php
// ============================================================
//   includes/update-profile.php
//   Logged-in user (kisi bhi role) ka naam/email/phone update karta hai
// ============================================================

// Session, database aur helper functions ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Not logged in']);
    exit();
}

// Current user ki ID aur profile form ki values hasil karo
$userId = $_SESSION['user_id'];
$name   = clean($conn, $_POST['name']  ?? '');
$email  = clean($conn, $_POST['email'] ?? '');
$phone  = clean($conn, $_POST['phone'] ?? '');

// Name aur email required fields hain, isliye unki validation karo
if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'msg' => 'Name and email are required']);
    exit();
}

// Email kisi aur user ka to nahi hai (apne ko chhod kar)
// Check karo ke same email kisi doosre user ke account mein already use to nahi ho rahi
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
mysqli_stmt_bind_param($checkStmt, 'si', $email, $userId);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);

// Agar email kisi doosre user ke paas hai to update rok do
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'msg' => 'This email is already in use']);
    exit();
}

// User ki profile details database mein update karne ke liye query prepare karo
$updateStmt = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
mysqli_stmt_bind_param($updateStmt, 'sssi', $name, $email, $phone, $userId);

// Check karo ke profile successfully update hui hai ya nahi
if (mysqli_stmt_execute($updateStmt)) {
    // Session mein bhi updated name aur email save karo
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;

    // Successful profile update ka response send karo
    echo json_encode(['success' => true, 'msg' => 'Profile updated successfully!']);
} else {
    // Agar database update fail ho to error response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>