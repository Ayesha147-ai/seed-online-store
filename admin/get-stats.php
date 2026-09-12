<?php
// ============================================================
//   admin/get-stats.php — Fetch Admin Dashboard Statistics
//   Yeh file admin dashboard ke liye mukhtalif stats aur counts calculate karke bhejti hai
// ============================================================
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

// Dashboard ki tamam statistics store karne ke liye empty array banao
$stats = [];

// Total users
// Database se total farmers ki count hasil karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='farmer'"));
$stats['total_farmers'] = $r['c'];

// Database se active agents ki total count hasil karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='agent' AND status='active'"));
$stats['total_agents'] = $r['c'];

// Pending agents
// Database se pending agent applications ki count hasil karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM agents WHERE is_approved=0"));
$stats['pending_agents'] = $r['c'];

// Pending seeds
// Database se pending seeds ki total count hasil karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE status='pending'"));
$stats['pending_seeds'] = $r['c'];

// Total orders
// Database se tamam orders ki total count hasil karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"));
$stats['total_orders'] = $r['c'];


// Total revenue — sirf admin ka commission (3%), un orders ka jinka agent-payment ho chuka hai
// Paid orders se admin ki total commission calculate karo
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(admin_commission),0) as total FROM orders WHERE payment_status='Paid'"));
$stats['total_revenue'] = $r['total'];

// Orders by status
// Mukhtalif order statuses ki count calculate karo
$statuses = ['placed','confirmed','processing','shipped','delivered','cancelled'];
foreach ($statuses as $s) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='$s'"));
    $stats['orders_' . $s] = $r['c'];
}

// Dashboard ki tamam statistics ko JSON format mein convert karke response ke tor par send karo
header('Content-Type: application/json');
echo json_encode($stats);
?>