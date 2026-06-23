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
$sql    = "SELECT o.*, u.name as farmer_name
           FROM orders o
           JOIN users u ON o.user_id = u.id
           WHERE o.order_number = '$orderNum'
           LIMIT 1";
$result = mysqli_query($conn, $sql);
$order  = mysqli_fetch_assoc($result);

if (!$order) {
    jsonResponse(['success' => false, 'msg' => 'Order not found']);
}

// Get items
$oid  = $order['id'];
$iSql = "SELECT oi.product_name, oi.quantity, oi.unit_price, oi.total_price
         FROM order_items oi
         WHERE oi.order_id = $oid";
$iRes = mysqli_query($conn, $iSql);
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

jsonResponse(['success' => true, 'order' => $order]);
?>
