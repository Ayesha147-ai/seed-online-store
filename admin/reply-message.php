<?php
// ============================================================
//   admin/reply-message.php
//   Admin ka reply save karta hai aur status 'replied' kar deta hai
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$msgId = intval($_POST['msg_id'] ?? 0);
$reply = trim($_POST['reply'] ?? '');

if ($msgId <= 0 || $reply === '') {
    echo json_encode(['success' => false, 'msg' => 'Reply cannot be empty']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), status = 'replied' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $reply, $msgId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>