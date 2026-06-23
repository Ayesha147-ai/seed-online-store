<?php
// ============================================================
//   auth/login.php — Login Handler
//   Algorithm 1 from documentation
// ============================================================

session_start();
require_once '../includes/db.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit();
}

$email    = clean($conn, $_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header('Location: ../login.html?error=empty');
    exit();
}

// Find user (status check alag se karenge for better messages)
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);

if (!$user) {
    header('Location: ../login.html?error=no_account');
    exit();
}

if ($user['status'] !== 'active') {
    header('Location: ../login.html?error=blocked');
    exit();
}

// Verify password (bcrypt)
if (!password_verify($password, $user['password'])) {
    header('Location: ../login.html?error=wrong_password');
    exit();
}

// If agent — check is_approved
if ($user['role'] === 'agent') {
    $agentSql    = "SELECT is_approved FROM agents WHERE user_id = {$user['id']} LIMIT 1";
    $agentResult = mysqli_query($conn, $agentSql);
    $agent       = mysqli_fetch_assoc($agentResult);

    if (!$agent || $agent['is_approved'] != 1) {
        header('Location: ../login.html?error=not_approved');
        exit();
    }
}

// Set session
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['user_email'] = $user['email'];

// Redirect by role
if ($user['role'] === 'admin') {
    header('Location: ../admin-dashboard.html');
} elseif ($user['role'] === 'agent') {
    header('Location: ../agent-dashboard.html');
} else {
    header('Location: ../index.html');
}
exit();
?>
