<?php
// ============================================================
//   admin/get-all-orders.php — Fetch All Orders Data
//   Yeh file database se tamam orders aur unke items ki details hasil karti hai
// ============================================================
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

// Database se tamam orders aur unke farmer ki details hasil karo
$sql = "SELECT o.*, u.name as farmer_name, u.email as farmer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];

// Har order ko process karke uske items ki details bhi hasil karo
while ($row = mysqli_fetch_assoc($result)) {
    $oid = $row['id'];

    // Current order ke products ki details hasil karne ke liye query prepare karo
    $iStmt = mysqli_prepare($conn, "SELECT oi.*, p.name as product_name
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?");
    mysqli_stmt_bind_param($iStmt, 'i', $oid);
    mysqli_stmt_execute($iStmt);
    $iRes = mysqli_stmt_get_result($iStmt);

    // Current order ke items store karne ke liye empty array banao
    $row['items'] = [];

    // Order ke tamam items ko array mein add karo
    while ($item = mysqli_fetch_assoc($iRes)) {
        $row['items'][] = $item;
    }

    // Complete order ko orders array mein add karo
    $orders[] = $row;
}

// Orders ko JSON format mein convert karke response ke tor par send karo
header('Content-Type: application/json');
echo json_encode($orders);
?>