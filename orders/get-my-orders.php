<?php
// ============================================================
//   orders/get-my-orders.php — Farmer ke apne orders
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
requireLogin();

$userId = getUserId();

$sql = "SELECT o.*
        FROM orders o
        WHERE o.user_id = $userId
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $oid  = $row['id'];

    // Get items
    $iSql = "SELECT oi.*, p.image
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = $oid";
    $iRes = mysqli_query($conn, $iSql);
    $row['items'] = [];
    while ($item = mysqli_fetch_assoc($iRes)) {
        $row['items'][] = $item;
    }

    $orders[] = $row;
}

header('Content-Type: application/json');
echo json_encode($orders);
?>
