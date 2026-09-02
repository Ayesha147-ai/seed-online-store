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
