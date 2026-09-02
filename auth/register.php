<?php
// ============================================================
//   auth/register.php — Farmer Registration
//   signup.html form action yahan point karta hai
// ============================================================

session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.html');
    exit();
}

$name     = clean($conn, $_POST['fullname']         ?? '');
$email    = clean($conn, $_POST['email']             ?? '');
$phone    = clean($conn, $_POST['phone']             ?? '');
$password = trim($_POST['password']         ?? '');
$confirm  = trim($_POST['confirm_password'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($password)) {
    header('Location: ../signup.html?error=empty');
    exit();
}

if ($password !== $confirm) {
    header('Location: ../signup.html?error=mismatch');
    exit();
}

if (strlen($password) < 6) {
    header('Location: ../signup.html?error=weak');
    exit();
}

// Check email exists
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);
if (mysqli_num_rows($check) > 0) {
    header('Location: ../signup.html?error=exists');
    exit();
}

// Hash password & save
$hashed = password_hash($password, PASSWORD_DEFAULT);
$insertStmt = mysqli_prepare($conn, "INSERT INTO users (name, email, phone, password, role, status)
           VALUES (?, ?, ?, ?, 'farmer', 'active')");
mysqli_stmt_bind_param($insertStmt, 'ssss', $name, $email, $phone, $hashed);

if (mysqli_stmt_execute($insertStmt)) {
    header('Location: ../login.html?registered=success');
} else {
    header('Location: ../signup.html?error=failed');
}

exit();
?>
