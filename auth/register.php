<?php
// ============================================================
//   auth/register.php — Farmer Registration
//   signup.html form action yahan point karta hai
// ============================================================

// Session start karo aur database/helpers ki required files load karo
session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Check karo ke request POST method se aa rahi hai
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.html');
    exit();
}

// Registration form se user ki basic details receive aur clean karo
$name     = clean($conn, $_POST['fullname']         ?? '');
$email    = clean($conn, $_POST['email']             ?? '');
$phone    = clean($conn, $_POST['phone']             ?? '');
$password = trim($_POST['password']         ?? '');
$confirm  = trim($_POST['confirm_password'] ?? '');

// Selected role from signup form — only used to decide WHERE to redirect.
// Actual DB role is always 'farmer' below, regardless of this value,
// so no one can skip the admin-approval process for becoming an agent.
$wantsAgent = (isset($_POST['role']) && $_POST['role'] === 'agent');

// Validation
// Check karo ke required fields empty nahi hain
if (empty($name) || empty($email) || empty($password)) {
    header('Location: ../signup.html?error=empty');
    exit();
}

// Check karo ke password aur confirm password same hain
if ($password !== $confirm) {
    header('Location: ../signup.html?error=mismatch');
    exit();
}

// Check karo ke password ki minimum required length hai
if (strlen($password) < 6) {
    header('Location: ../signup.html?error=weak');
    exit();
}

// Check email exists
// Check karo ke provided email pehle se database mein registered hai ya nahi
$checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkStmt, 's', $email);
mysqli_stmt_execute($checkStmt);
$check = mysqli_stmt_get_result($checkStmt);
if (mysqli_num_rows($check) > 0) {
    header('Location: ../signup.html?error=exists');
    exit();
}

// Hash password & save — role is ALWAYS 'farmer' at signup time.
// Becoming an 'agent' only happens later, through admin approval
// (see includes/get-agent-status.php and the agents table.)
// Password ko securely hash karke farmer user ka record database mein save karo
$hashed = password_hash($password, PASSWORD_DEFAULT);
$insertStmt = mysqli_prepare($conn, "INSERT INTO users (name, email, phone, password, role, status)
           VALUES (?, ?, ?, ?, 'farmer', 'active')");
mysqli_stmt_bind_param($insertStmt, 'ssss', $name, $email, $phone, $hashed);

// User ka registration successfully save hua ya nahi check karo
if (mysqli_stmt_execute($insertStmt)) {

    // Agar user agent banna chahta hai to registration ke baad application page par bhejo
    if ($wantsAgent) {
        // User wants to become an agent — auto-login now so that
        // register-agent.html's apply-form has a valid session,
        // then send them straight to the application form.
        // Naye user ki ID hasil karke session mein basic information save karo
        $newUserId = mysqli_insert_id($conn);
        $_SESSION['user_id']   = $newUserId;
        $_SESSION['user_role'] = 'farmer';
        $_SESSION['user_name'] = $name;

        header('Location: ../register-agent.html');
    } else {
        // Plain farmer signup — same as before, go to login page.
        // Normal farmer registration ke baad login page par redirect karo
        header('Location: ../login.html?registered=success');
    }

} else {
    // Registration fail hone par signup page par error ke saath wapas bhejo
    header('Location: ../signup.html?error=failed');
}

// Script ko yahin terminate karo
exit();
?>