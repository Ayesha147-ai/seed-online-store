<?php
// ============================================================
//   orders/track-order.php — Order Track karo by Order Number
// ============================================================

require_once '../includes/db.php';
require_once '../includes/helpers.php';

$orderNum = isset($_GET['order_number'])
    ? trim(mysqli_real_escape_string($conn, $_GET['order_number']))
    : '';

if (empty($orderNum)) {
    jsonResponse(['success' => false, 'msg' => 'Order number required'], 400);
}

// Find order
$stmt = mysqli_prepare($conn, "SELECT o.*, u.name as farmer_name
           FROM orders o
           JOIN users u ON o.user_id = u.id
           WHERE o.order_number = ?
           LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $orderNum);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order  = mysqli_fetch_assoc($result);

if (!$order) {
    jsonResponse(['success' => false, 'msg' => 'Order not found']);
}

// Get items
$oid = $order['id'];
$iStmt = mysqli_prepare($conn, "SELECT oi.product_name, oi.quantity, oi.unit_price, oi.total_price
         FROM order_items oi
         WHERE oi.order_id = ?");
mysqli_stmt_bind_param($iStmt, 'i', $oid);
mysqli_stmt_execute($iStmt);
$iRes = mysqli_stmt_get_result($iStmt);
$order['items'] = [];
while ($item = mysqli_fetch_assoc($iRes)) {
    $order['items'][] = $item;
}

// Build timeline based on status
$statusOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];
$currentIdx  = array_search($order['status'], $statusOrder);
$timeline    = [];
foreach ($statusOrder as $idx => $step) {
    $timeline[$step] = ($idx <= $currentIdx) ? date('d M Y', strtotime($order['created_at'])) : null;
}

$order['timeline'] = $timeline;

$safeOrder = [
    'order_number' => $order['order_number'],
    'status'       => $order['status'],
    'city'         => $order['city'],
    'created_at'   => $order['created_at'],
    'grand_total'  => $order['grand_total'],
    'items'        => $order['items'],
    'timeline'     => $order['timeline']
];

jsonResponse(['success' => true, 'order' => $safeOrder]);
?>
