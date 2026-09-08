<?php
// ============================================================
//   includes/get-my-replies.php
//   Logged-in user ke replied messages wapas deta hai
// ============================================================
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT message, admin_reply, replied_at FROM contact_messages
                                WHERE user_id = ? AND admin_reply IS NOT NULL
                                ORDER BY replied_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$replies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $replies[] = $row;
}

echo json_encode($replies);
?>