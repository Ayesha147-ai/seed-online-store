<?php
require_once __DIR__ . '/../includes/session.php';

// 1. Saare session variables ko khali kar do
$_SESSION = array();

// 2. Browser ki session cookie ko expire kar do
if (ini_get("session.use_cookies")) {
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