<?php
// ============================================================
//   admin/update-order-status.php — Update Order Status Action
//   Yeh file admin ke zariye kisi bhi order ka status update karti hai
// ============================================================
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// POST se order ki ID aur new status receive karo
$orderId = intval($_POST['order_id'] ?? 0);
$status  = $_POST['status'] ?? '';

// Order ke liye allowed statuses ki list define karo
$allowed = ['placed','confirmed','processing','shipped','delivered','cancelled'];

// Check karo ke order ID valid hai aur status allowed list mein hai
if ($orderId <= 0 || !in_array($status, $allowed)) {
    echo json_encode(['success' => false]);
    exit();
}

// FIX: cancelled order ka status aage badalne se rokne ke liye current status check karo
$checkStmt = mysqli_prepare($conn, "SELECT status FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, 'i', $orderId);
mysqli_stmt_execute($checkStmt);
$current = mysqli_fetch_assoc(mysqli_stmt_get_result($checkStmt));

// FIX: order nahi mila to yahin rok do
if (!$current) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

// FIX: agar order pehle se cancelled hai to status update na hone do
if ($current['status'] === 'cancelled') {
    echo json_encode(['success' => false, 'msg' => 'Cannot change status of a cancelled order']);
    exit();
}

// Order ka status update karne ke liye database query prepare karo
$stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);

// Check karo ke order status successfully update hua hai ya nahi
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    // Order status successfully update hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar update fail ho ya order na mile to failure ka response send karo
    echo json_encode(['success' => false, 'msg' => 'Update failed or order not found']);
}
?>