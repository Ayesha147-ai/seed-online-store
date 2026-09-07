<?php
// ============================================================
//   includes/get-agent-status.php
//   Batata hai: user "Register as Agent" button dekh sakta hai ya nahi
// ============================================================
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false, 'show_button' => false]);
    exit();
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['user_role'] ?? '';

// Admin ke liye yeh sawal hi nahi uthta
if ($role === 'admin') {
    echo json_encode(['logged_in' => true, 'show_button' => false]);
    exit();
}

// Agent ke liye — kabhi apply nahi kiya to state A jaisa treat karo (dikhe)
$stmt = mysqli_prepare($conn, "SELECT is_approved FROM agents WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

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