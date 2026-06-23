<?php
// ============================================================
//   admin/delete-user.php
//   Admin kisi bhi user ko permanently delete kar sakta hai
//   (foreign keys ON DELETE CASCADE hain, related data khud saaf ho jata hai)
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$userId = intval($_POST['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

// Admin apne aap ko ya kisi aur admin ko delete nahi kar sakta
if ($userId === getUserId()) {
    echo json_encode(['success' => false, 'msg' => 'You cannot delete your own account']);
    exit();
}

$sql = "DELETE FROM users WHERE id = $userId AND role != 'admin'";

if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Delete failed or user not found']);
}
?>