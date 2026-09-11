<?php
// ============================================================
//   admin/reply-message.php
//   Admin ka reply save karta hai aur status 'replied' kar deta hai
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se message ki ID aur admin ka reply receive karo
$msgId = intval($_POST['msg_id'] ?? 0);
$reply = trim($_POST['reply'] ?? '');

// Check karo ke message ID valid hai aur reply empty nahi hai
if ($msgId <= 0 || $reply === '') {
    echo json_encode(['success' => false, 'msg' => 'Reply cannot be empty']);
    exit();
}

// Admin ka reply save karo aur message ka status replied set karo
$stmt = mysqli_prepare($conn, "UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), status = 'replied' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $reply, $msgId);

// Check karo ke reply successfully save hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Reply successfully save hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar reply save na ho to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>