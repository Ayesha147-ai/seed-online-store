<?php
// ============================================================
//   orders/cancel-order.php — Algorithm 4: CancelOrder
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

$orderId = intval($_POST['order_id'] ?? 0);
$userId  = getUserId();

if ($orderId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid order']);
    exit();
}

// Find order — must belong to this user
$sql   = "SELECT * FROM orders WHERE id = $orderId AND user_id = $userId LIMIT 1";
$order = mysqli_fetch_assoc(mysqli_query($conn, $sql));

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
mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = $orderId");

// Restore stock
$items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = $orderId");
while ($item = mysqli_fetch_assoc($items)) {
    mysqli_query($conn, "UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['product_id']}");
}

echo json_encode(['success' => true, 'msg' => 'Order cancelled']);
?>
