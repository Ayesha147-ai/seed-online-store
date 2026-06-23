<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$sql = "SELECT o.*, u.name as farmer_name, u.email as farmer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $oid  = $row['id'];
    $iSql = "SELECT oi.*, p.name as product_name
             FROM order_items oi JOIN products p ON oi.product_id = p.id
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
