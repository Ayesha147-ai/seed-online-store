<?php
// ============================================================
//   orders/cancel-order.php — Order Cancellation & Stock Restoration Logic
//   Yeh file user ke order ko verify karke, agar order shipped ya delivered nahi hai toh usko 'cancelled' status set karti hai aur products ki quantity stock mein wapas restore karti hai
// ============================================================

// Session aur database ki required files load karo
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check karo ke user logged in hai
requireLogin();

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Order ID aur current user ki ID hasil karo
$orderId = intval($_POST['order_id'] ?? 0);
$userId  = getUserId();

// Check karo ke order ID valid hai
if ($orderId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid order']);
    exit();
}

// Find order — must belong to this user
// Sirf current user ka order database se find karo
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Agar order nahi mila to error response do
if (!$order) {
    echo json_encode(['success' => false, 'msg' => 'Order not found']);
    exit();
}

// Cannot cancel delivered order
// Delivered order ko cancel karne ki permission nahi hai
if ($order['status'] === 'delivered') {
    echo json_encode(['success' => false, 'msg' => 'Cannot cancel delivered order']);
    exit();
}

// Cannot cancel shipped order
// Shipped order ko bhi cancel karne ki permission nahi hai
if ($order['status'] === 'shipped') {
    echo json_encode(['success' => false, 'msg' => 'Order already shipped, cannot cancel']);
    exit();
}

// Cancel order
// Order ka status cancelled set karo
$cancelStmt = mysqli_prepare($conn, "UPDATE orders SET status = 'cancelled' WHERE id = ?");
mysqli_stmt_bind_param($cancelStmt, 'i', $orderId);
mysqli_stmt_execute($cancelStmt);

// Restore stock
// Cancel hone wale order ke products ka stock wapas add karo
$itemsStmt = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($itemsStmt, 'i', $orderId);
mysqli_stmt_execute($itemsStmt);
$items = mysqli_stmt_get_result($items);

// Har ordered product ki quantity stock mein restore karo
$restoreStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock + ? WHERE id = ?");
while ($item = mysqli_fetch_assoc($items)) {
    mysqli_stmt_bind_param($restoreStmt, 'ii', $item['quantity'], $item['product_id']);
    mysqli_stmt_execute($restoreStmt);
}

// Successful cancellation ka response send karo
echo json_encode(['success' => true, 'msg' => 'Order cancelled']);
?>