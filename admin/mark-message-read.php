<?php
// ============================================================
//   admin/mark-message-read.php
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se message ki ID receive karo
$msgId = intval($_POST['msg_id'] ?? 0);

// Check karo ke message ID valid hai ya nahi
if ($msgId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

// Message ka status read mein update karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "UPDATE contact_messages SET status = 'read' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $msgId);

// Check karo ke message successfully read mark hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    // Message successfully update hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar update fail ho jaye to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>