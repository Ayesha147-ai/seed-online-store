<?php
// ============================================================
//   orders/cancel-order.php — Algorithm 4: CancelOrder
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();
header('Content-Type: application/json');
$orderId = intval($_POST['order_id'] ?? 0);
$userId  = getUserId();

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid order']);
    exit();
}

// Find order — must belong to this user
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

// Cannot cancel delivered order
if ($order['status'] === 'delivered') {
    echo json_encode(['success' => false, 'msg' => 'Cannot cancel delivered order']);
    exit();
}

// Cannot cancel shipped order
if ($order['status'] === 'shipped') {
    echo json_encode(['success' => false, 'msg' => 'Order already shipped, cannot cancel']);
    exit();
}

// Cancel order
$cancelStmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled' WHERE id = ?");
mysqli_stmt_bind_param($cancelStmt, 'i', $orderId);
mysqli_stmt_execute($cancelStmt);

// Restore stock
$itemsStmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($itemsStmt, 'i', $orderId);
mysqli_stmt_execute($itemsStmt);
$items = mysqli_stmt_get_result($itemsStmt);

$restoreStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
while ($item = mysqli_fetch_assoc($items)) {
    mysqli_stmt_bind_param($restoreStmt, 'ii', $item['quantity'], $item['product_id']);
    mysqli_stmt_execute($restoreStmt);
}

echo json_encode(['success' => true, 'msg' => 'Order cancelled']);
?>
