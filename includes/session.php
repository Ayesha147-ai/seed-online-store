<?php
// ============================================================
//   includes/session.php — Session Management
// ============================================================

// Agar session pehle se start nahi hai to secure cookie settings ke saath start karo
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
    // Remember-me session restore karne ke liye database connection load karo
    require_once __DIR__ . '/db.php';

    // Cookie ko selector aur validator ke do parts mein split karo
    $parts = explode(':', $_COOKIE['remember_me']);

    // Sirf valid two-part cookie ko process karo
    if (count($parts) === 2) {
        list($selector, $validator) = $parts;

        // Selector ke through valid aur non-expired remember-me record find karo
        $stmt = mysqli_prepare($conn, "SELECT * FROM users
            WHERE remember_selector = ? AND remember_token_expiry > NOW() LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $selector);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        // Sirf active user ki remember-me session restore karo
        if ($user && $user['status'] === 'active') {
            // Cookie validator ka SHA-256 hash database wale token se compare karne ke liye banao
            $hashedValidator = hash('sha256', $validator);

            // hash_equals() timing-attack se bachata hai
            if (hash_equals($user['remember_token'], $hashedValidator)) {
                // Valid remember-me token hone par user ki session details set karo
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_email'] = $user['email'];
            }
        }
    }

    // Cookie chahe kaam aaye ya na aaye — invalid ho to hata do
    // Agar session restore nahi hui to remember-me cookie browser se remove karo
    if (!isset($_SESSION['user_id'])) {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

// Login required hone par unauthorized response return karo
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Not logged in']));
    }
}

// Sirf admin users ko access allow karo
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

// Sirf agent users ko access allow karo
function requireAgent() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'agent') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

// Sirf farmer users ko access allow karo
function requireFarmer() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'farmer') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['error' => 'Access denied']));
    }
}

// Check karo ke current user logged in hai ya nahi
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Current logged-in user ka role return karo
function getUserRole() {
    return $_SESSION['user_role'] ?? '';
}

// Current logged-in user ki ID return karo
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Current logged-in user ka name return karo, warna Guest return karo
function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}
?>