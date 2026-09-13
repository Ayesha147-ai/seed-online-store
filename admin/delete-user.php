<?php
// ============================================================
//   admin/delete-user.php
//   Admin kisi bhi user ko permanently delete kar sakta hai
//   (foreign keys ON DELETE CASCADE hain, related data khud saaf ho jata hai)
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se user ki ID receive karo
$userId = intval($_POST['user_id'] ?? 0);

// Check karo ke user ID valid hai ya nahi
if ($userId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

// Check karo ke admin apne account ko delete na kar sake
// Admin apne aap ko ya kisi aur admin ko delete nahi kar sakta
if ($userId === getUserId()) {
    echo json_encode(['success' => false, 'msg' => 'You cannot delete your own account']);
    exit();
}

// User ko delete karne ke liye database query prepare karo
$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ? AND role != 'admin'");
mysqli_stmt_bind_param($stmt, 'i', $userId);

// Check karo ke user successfully delete hua hai ya nahi
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    // User successfully delete hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar user delete na ho to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Delete failed or user not found']);
}
?>