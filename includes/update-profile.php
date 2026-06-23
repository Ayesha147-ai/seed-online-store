<?php
// ============================================================
//   includes/update-profile.php
//   Logged-in user (kisi bhi role) ka naam/email/phone update karta hai
// ============================================================
session_start();
require_once 'db.php';
require_once 'helpers.php';
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
$check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $userId");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'msg' => 'This email is already in use']);
    exit();
}

$sql = "UPDATE users SET name = '$name', email = '$email', phone = '$phone' WHERE id = $userId";

if (mysqli_query($conn, $sql)) {
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;
    echo json_encode(['success' => true, 'msg' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>