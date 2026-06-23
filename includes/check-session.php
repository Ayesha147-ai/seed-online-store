<?php
// ============================================================
//   includes/check-session.php — Session Status Check
//   JS isko call karke pata lagata hai user login hai ya nahi
// ============================================================
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'name'      => $_SESSION['user_name'],
        'role'      => $_SESSION['user_role']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>