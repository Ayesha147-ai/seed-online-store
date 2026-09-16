<?php
// ============================================================
//   includes/get-my-replies.php
//   Logged-in user ke replied messages wapas deta hai
// ============================================================

// Session aur database ki required files load karo
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Check karo ke user logged in hai ya nahi
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

// Current logged-in user ki ID hasil karo
$userId = $_SESSION['user_id'];

// Current user ke sirf replied contact messages database se fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT message, admin_reply, replied_at FROM contact_messages
                                WHERE user_id = ? AND admin_reply IS NOT NULL
                                ORDER BY replied_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Replies store karne ke liye empty array banao
$replies = [];

// Har reply ko replies array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $replies[] = $row;
}

// Replies ka JSON response return karo
echo json_encode($replies);
?>