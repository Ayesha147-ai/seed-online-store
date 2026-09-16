<?php
// ============================================================
//   auth/login.php — Login Handler
//   Algorithm 1 from documentation
// ============================================================

// Session start karo aur database/helpers ki required files load karo
session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Check karo ke request POST method se aa rahi hai
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit();
}

// User ki email, password aur remember option ki values hasil karo
$email    = clean($conn, $_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

// Check karo ke email aur password empty nahi hain
if (empty($email) || empty($password)) {
    header('Location: ../login.html?error=empty');
    exit();
}

// Find user (status check alag se karenge for better messages)
// Email ke through user ka record database se fetch karo
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

// Agar user account nahi mila to error ke saath login page par redirect karo
if (!$user) {
    header('Location: ../login.html?error=no_account');
    exit();
}

// User ka account active hai ya nahi check karo
if ($user['status'] !== 'active') {
    header('Location: ../login.html?error=blocked');
    exit();
}

// Verify password (bcrypt)
// Enter ki hui password ko stored hashed password ke against verify karo
if (!password_verify($password, $user['password'])) {
    header('Location: ../login.html?error=wrong_password');
    exit();
}

// If agent — check is_approved
// Agent user ke approval status ko database se check karo
if ($user['role'] === 'agent') {
    $agentStmt = mysqli_prepare($conn, "SELECT is_approved FROM agents WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($agentStmt, 'i', $user['id']);
mysqli_stmt_execute($agentStmt);
$agentResult = mysqli_stmt_get_result($agentStmt);
$agent       = mysqli_fetch_assoc($agentResult);

    // Agar agent approve nahi hai to login allow na karo
    if (!$agent || $agent['is_approved'] != 1) {
        header('Location: ../login.html?error=not_approved');
        exit();
    }
}

// Set session
// Login ke baad user ki basic information session mein save karo
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['user_email'] = $user['email'];

// ===== REMEMBER ME =====
// "Remember Me" checked hai to ek secure token bana kar DB + cookie mein save karo,
// taako browser band karne ke baad bhi 30 din tak login yaad rahe.
if ($remember) {
    // Browser ke liye selector aur secret validator generate karo
    $selector  = bin2hex(random_bytes(8));      // DB row dhoondne ke liye (plain)
    $validator = bin2hex(random_bytes(32));     // asal secret (sirf cookie mein plain jayega)
    $hashed    = hash('sha256', $validator);    // DB mein sirf hash save hoga, raw kabhi nahi
    $expiry    = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Remember token ka selector, hash aur expiry database mein save karo
    $rtStmt = mysqli_prepare($conn, "UPDATE users
        SET remember_selector = ?, remember_token = ?, remember_token_expiry = ?
        WHERE id = ?");
    mysqli_stmt_bind_param($rtStmt, 'sssi', $selector, $hashed, $expiry, $user['id']);
    mysqli_stmt_execute($rtStmt);

    // Remember me token ko secure cookie mein save karo
    setcookie('remember_me', $selector . ':' . $validator, [
        'expires'  => strtotime('+30 days'),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Redirect by role
// User ke role ke according relevant dashboard par redirect karo
if ($user['role'] === 'admin') {
    header('Location: ../admin-dashboard.html');
} elseif ($user['role'] === 'agent') {
    header('Location: ../agent-dashboard.html');
} else {
    header('Location: ../index.html');
}
exit();
?>