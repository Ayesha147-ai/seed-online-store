<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

$agentId = getUserId();

// Agent ke products ke orders
$sql = "SELECT DISTINCT o.*, u.name as farmer_name, u.phone as farmer_phone
        FROM orders o
        JOIN users u      ON o.user_id     = u.id
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p   ON oi.product_id = p.id
        WHERE p.agent_id  = $agentId
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Get items for this order
    $oid   = $row['id'];
    $iStmt = mysqli_prepare($conn, "SELECT oi.*, p.name as product_name
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ? AND p.agent_id = ?");
    mysqli_stmt_bind_param($iStmt, 'ii', $oid, $agentId);
    mysqli_stmt_execute($iStmt);
    $iRes = mysqli_stmt_get_result($iStmt);
    $row['items'] = [];
    while ($item = mysqli_fetch_assoc($iRes)) {
        $row['items'][] = $item;
    }
    $orders[] = $row;
}

header('Content-Type: application/json');
echo json_encode($orders);
?>
