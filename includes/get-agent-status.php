<?php
// ============================================================
//   includes/get-agent-status.php
//   Batata hai: user "Register as Agent" button dekh sakta hai ya nahi
// ============================================================

// Session aur database ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false, 'show_button' => false]);
    exit();
}

// Current user ki ID aur role session se hasil karo
$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'] ?? '';

// Admin ke liye yeh sawal hi nahi uthta
// Admin ko agent registration button show na karo
if ($role === 'admin') {
    echo json_encode(['logged_in' => true, 'show_button' => false]);
    exit();
}

// Agent ke liye — kabhi apply nahi kiya to state A jaisa treat karo (dikhe)
// Database se current user ki agent application ka approval status check karo
$stmt = mysqli_prepare($conn, "SELECT is_approved FROM agents WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Application ke status ke according button show ya hide karo
if (!$row) {
    // State A: kabhi apply nahi kiya — dikhe
    echo json_encode(['logged_in' => true, 'show_button' => true]);
} elseif ($row['is_approved'] == 0) {
    // State B: pending — chhupa do
    echo json_encode(['logged_in' => true, 'show_button' => false]);
} elseif ($row['is_approved'] == 2) {
    // State C: reject ho chuka — dobara dikhe
    echo json_encode(['logged_in' => true, 'show_button' => true]);
} else {
    // is_approved == 1: approved (yeh case normally index.html pe aata hi nahi)
    echo json_encode(['logged_in' => true, 'show_button' => true]);
}
?>