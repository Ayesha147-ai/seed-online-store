<?php
// ============================================================
//   auth/logout.php — User Logout Handler
//   Yeh file user ka session destroy aur remember me tokens clear karke logout karti hai
// ============================================================
// Session aur database ki required files load karo
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';

// ===== REMEMBER ME — DB token bhi invalidate karo =====
// Session clear karne se pehle user_id nikal lo, warna baad mein nahi milega
if (isset($_SESSION['user_id'])) {
    // Current logged-in user ki ID hasil karo
    $userId = $_SESSION['user_id'];

    // Database mein remember me ke tamam tokens ko invalidate karo
    $stmt = mysqli_prepare($conn, "UPDATE users
        SET remember_selector = NULL, remember_token = NULL, remember_token_expiry = NULL
        WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
}

// "remember_me" cookie ko bhi browser se hata do
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// 1. Saare session variables ko khali kar do
$_SESSION = array();

// 2. Browser ki session cookie ko expire kar do
if (ini_get("session.use_cookies")) {
    // Current session cookie ki settings hasil karo
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Session ko mukammal destroy kar do
session_destroy();

// 4. Wapas home page par bhej do
header("Location: ../index.html");
exit();
?>