<?php
// ============================================================
//   includes/session.php — Session Management
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.html');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: ../index.html');
        exit();
    }
}

function requireAgent() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'agent') {
        header('Location: ../index.html');
        exit();
    }
}

function requireFarmer() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'farmer') {
        header('Location: ../index.html');
        exit();
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
