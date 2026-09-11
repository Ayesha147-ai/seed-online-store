<?php
// ============================================================
//   admin/update-user-status.php
//   Admin kisi bhi user ko block/unblock kar sakta hai
// ============================================================

// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se user ki ID aur new status receive karo
$userId  = intval($_POST['user_id'] ?? 0);
$status  = $_POST['status'] ?? '';

// User ke liye allowed statuses ki list define karo
$allowed = ['active', 'blocked'];

// Check karo ke user ID valid hai aur status allowed list mein hai
if ($userId <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

// Check karo ke admin apne account ko block na kar sake
if ($userId === getUserId()) {
    echo json_encode(['success' => false, 'msg' => 'You cannot change your own status']);
    exit();
}

// User ka status update karne ke liye database query prepare karo
$stmt = mysqli_prepare($conn, "UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
mysqli_stmt_bind_param($stmt, 'si', $status, $userId);

// Check karo ke user ka status successfully update hua hai ya nahi
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    // User status successfully update hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar update fail ho ya user na mile to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed or user not found']);
}
?>