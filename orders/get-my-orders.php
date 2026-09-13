<?php
// ============================================================
//   orders/get-my-orders.php — Fetch Farmer's Orders & Associated Items Logic
//   Yeh file logged-in farmer ke tamam orders aur unke items (product images ke saath) database se latest date ke hisaab se fetch karke JSON format mein return karti hai
// ============================================================

// Session aur database ki required files load karo
require_once '../includes/session.php';
require_once '../includes/db.php';

// Check karo ke user logged in hai
requireLogin();

// Current logged-in user ki ID hasil karo
$userId = getUserId();

// Current user ke orders database se latest order ke hisaab se fetch karo
$sql = "SELECT o.*
        FROM orders o
        WHERE o.user_id = $userId
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$orders = [];

// Har order ko process karo
while ($row = mysqli_fetch_assoc($result)) {
    // Current order ki ID hasil karo
    $oid  = $row['id'];

    // Get items
    // Current order ke tamam items aur unki product images fetch karo
    $iSql = "SELECT oi.*, p.image
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = $oid";
    $iRes = mysqli_query($conn, $iSql);

    // Order ke items store karne ke liye empty array banao
    $row['items'] = [];

    // Har item ko order ke items array mein add karo
    while ($item = mysqli_fetch_assoc($iRes)) {
        $row['items'][] = $item;
    }

    // Complete order ko orders array mein add karo
    $orders[] = $row;
}

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// Tamam orders ko JSON response mein return karo
echo json_encode($orders);
?>