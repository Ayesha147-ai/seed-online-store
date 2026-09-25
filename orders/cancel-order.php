<?php
// ============================================================
//   orders/cancel-order.php — Order Cancellation & Stock Restoration Logic
//   Yeh file user ke order ko verify karke, agar order shipped ya delivered nahi hai toh usko 'cancelled' status set karti hai aur products ki quantity stock mein wapas restore karti hai
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

$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

if ($order['status'] === 'delivered') {
    echo json_encode(['success' => false, 'msg' => 'Cannot cancel delivered order']);
    exit();
}

if ($order['status'] === 'shipped') {
    echo json_encode(['success' => false, 'msg' => 'Order already shipped, cannot cancel']);
    exit();
}

$cancelStmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled' WHERE id = ?");
mysqli_stmt_bind_param($cancelStmt, 'i', $orderId);
mysqli_stmt_execute($cancelStmt);

$itemsStmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($itemsStmt, 'i', $orderId);
mysqli_stmt_execute($itemsStmt);
// FIX: $items ki jagah $itemsStmt pass kiya, pehle undefined variable pass ho raha tha
$items = mysqli_stmt_get_result($itemsStmt);

$restoreStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
while ($item = mysqli_fetch_assoc($items)) {
    mysqli_stmt_bind_param($restoreStmt, 'ii', $item['quantity'], $item['product_id']);
    mysqli_stmt_execute($restoreStmt);
}

echo json_encode(['success' => true, 'msg' => 'Order cancelled']);
?>