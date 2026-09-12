<?php
// ============================================================
//   orders/track-order.php — Order Tracking by Order Number & Status Timeline Logic
//   Yeh file order number ke zariye database se order details aur farmer ka naam fetch karti hai, order items aur status timeline calculate karti hai, aur ek safe JSON response return karti hai
// ============================================================

// Database aur helper functions ki required files load karo
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// GET request se order number hasil karo aur sanitize karo
$orderNum = isset($_GET['order_number'])
    ? trim(mysqli_real_escape_string($conn, $_GET['order_number']))
    : '';

// Agar order number nahi diya gaya to error response return karo
if (empty($orderNum)) {
    jsonResponse(['success' => false, 'msg' => 'Order number required'], 400);
}

// Find order
// Order number ke basis par order aur farmer ka naam database se fetch karo
$stmt = mysqli_prepare($conn, "SELECT o.*, u.name as farmer_name
           FROM orders o
           JOIN users u ON o.user_id = u.id
           WHERE o.order_number = ?
           LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $orderNum);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order  = mysqli_fetch_assoc($result);

// Agar order nahi mila to error response return karo
if (!$order) {
    jsonResponse(['success' => false, 'msg' => 'Order not found']);
}

// Get items
// Current order ke tamam items database se fetch karo
$oid = $order['id'];
$iStmt = mysqli_prepare($conn, "SELECT oi.product_name, oi.quantity, oi.unit_price, oi.total_price
         FROM order_items oi
         WHERE oi.order_id = ?");
mysqli_stmt_bind_param($iStmt, 'i', $oid);
mysqli_stmt_execute($iStmt);
$iRes = mysqli_stmt_get_result($iStmt);

// Order ke items store karne ke liye empty array banao
$order['items'] = [];

// Har order item ko items array mein add karo
while ($item = mysqli_fetch_assoc($iRes)) {
    $order['items'][] = $item;
}

// Build timeline based on status
// Order status ke sequence ke mutabiq tracking timeline prepare karo
$statusOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentIdx  = array_search($order['status'], $statusOrder);
$timeline    = [];

// Har status ke liye check karo ke woh current status tak complete hua hai ya nahi
foreach ($statusOrder as $idx => $step) {
    $timeline[$step] = ($idx <= $currentIdx) ? date('d M Y', strtotime($order['created_at'])) : null;
}

// Prepared timeline ko order ke response mein add karo
$order['timeline'] = $timeline;

// Sirf required order information response mein bhejne ke liye safe array banao
$safeOrder = [
    'order_number' => $order['order_number'],
    'status'       => $order['status'],
    'city'         => $order['city'],
    'created_at'   => $order['created_at'],
    'grand_total'  => $order['grand_total'],
    'items'        => $order['items'],
    'timeline'     => $order['timeline']
];

// Successful order tracking response return karo
jsonResponse(['success' => true, 'order' => $safeOrder]);
?>