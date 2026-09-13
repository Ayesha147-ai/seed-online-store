<?php
// ============================================================
//   agent/get-my-orders.php — Fetch Agent's Orders Data
//   Yeh file current logged-in agent ke products ke mutaliq tamam orders ki details hasil karti hai
// ============================================================
// Session aur database ki required files load karo aur agent access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

// Current logged-in agent ki ID hasil karo
$agentId = getUserId();

// Agent ke products ke orders
$sql = "SELECT DISTINCT o.*, u.name as farmer_name, u.phone as farmer_phone
        FROM orders o
        JOIN users u      ON o.user_id     = u.id
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p   ON oi.product_id = p.id
        WHERE p.agent_id  = $agentId
        ORDER BY o.created_at DESC";

// Orders ki query execute karo aur empty orders array banao
$result = mysqli_query($conn, $sql);
$orders = [];

// Har order ko process karo
while ($row = mysqli_fetch_assoc($result)) {
    // Get items for this order — SIRF is agent ke items
    $oid   = $row['id'];

    // Is order ke sirf current agent ke products ke items fetch karne ke liye query prepare karo
    $iStmt = mysqli_prepare($conn, "SELECT oi.*, p.name as product_name
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ? AND p.agent_id = ?");
    mysqli_stmt_bind_param($iStmt, 'ii', $oid, $agentId);
    mysqli_stmt_execute($iStmt);
    $iRes = mysqli_stmt_get_result($iStmt);

    // Order ke items store karne ke liye array aur agent ka total initialize karo
    $row['items'] = [];
    $agentTotal   = 0;

    // Har agent item ko order mein add karo
    while ($item = mysqli_fetch_assoc($iRes)) {
        $row['items'][] = $item;
        // total_price column already stores unit_price * quantity
        $agentTotal += (float)$item['total_price'];
    }

    // grand_total ab pura order ka total NAHI, balke SIRF is agent ke items ka total hai
    $row['agent_total'] = $agentTotal;

    // Processed order ko orders array mein add karo
    $orders[] = $row;
}

// Response ko JSON format mein return karo
header('Content-Type: application/json');
echo json_encode($orders);
?>