<?php
// ============================================================
//   includes/check-session.php — Session Status Check
//   JS isko call karke pata lagata hai user login hai ya nahi
// ============================================================

// Session start karo aur session related functions ki file load karo
session_start(); 
require_once __DIR__ . '/session.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user session mein logged in hai ya nahi
if (isset($_SESSION['user_id'])) {
    // Agar user logged in hai to uski basic session information return karo
    echo json_encode([
        'logged_in' => true,
        'name'      => $_SESSION['user_name'],
        'role'      => $_SESSION['user_role']
    ]);
} else {
    // Agar user logged in nahi hai to false response return karo
    echo json_encode(['logged_in' => false]);
}
?>