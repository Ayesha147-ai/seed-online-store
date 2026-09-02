<?php
// ============================================================
//   admin/update-user-status.php
//   Admin kisi bhi user ko block/unblock kar sakta hai
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$userId  = intval($_POST['user_id'] ?? 0);
$status  = $_POST['status'] ?? '';
$allowed = ['active', 'blocked'];

if ($userId <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

// Admin apne aap ko block nahi kar sakta
if ($userId === getUserId()) {
    echo json_encode(['success' => false, 'msg' => 'You cannot change your own status']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
mysqli_stmt_bind_param($stmt, 'si', $status, $userId);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed or user not found']);
}
?>