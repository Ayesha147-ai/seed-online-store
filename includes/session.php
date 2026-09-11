<?php
// ============================================================
//   includes/session.php — Session Management
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
} 

// ===== REMEMBER ME — auto-restore session from cookie =====
// Agar normal PHP session khatam ho chuki hai (browser band ho gaya tha),
// lekin "remember_me" cookie maujood hai — us se dobara login kar do.
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    require_once __DIR__ . '/db.php';

    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) === 2) {
        list($selector, $validator) = $parts;

        $stmt = mysqli_prepare($conn, "SELECT * FROM users
            WHERE remember_selector = ? AND remember_token_expiry > NOW() LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $selector);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && $user['status'] === 'active') {
            $hashedValidator = hash('sha256', $validator);

            // hash_equals() timing-attack se bachata hai
            if (hash_equals($user['remember_token'], $hashedValidator)) {
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_email'] = $user['email'];
            }
        }
    }

    // Cookie chahe kaam aaye ya na aaye — invalid ho to hata do
    if (!isset($_SESSION['user_id'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Not logged in']));
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

function requireAgent() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'agent') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

function requireFarmer() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'farmer') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? '';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}
?>