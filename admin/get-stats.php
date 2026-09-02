<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$stats = [];

// Total users
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='farmer'"));
$stats['total_farmers'] = $r['c'];

$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='agent' AND status='active'"));
$stats['total_agents'] = $r['c'];

// Pending agents
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM agents WHERE is_approved=0"));
$stats['pending_agents'] = $r['c'];

// Pending seeds
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products WHERE status='pending'"));
$stats['pending_seeds'] = $r['c'];

// Total orders
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"));
$stats['total_orders'] = $r['c'];

// Total revenue
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(pay_amount),0) as total FROM transactions WHERE pay_status='paid'"));
$stats['total_revenue'] = $r['total'];

// Orders by status
$statuses = ['placed','confirmed','processing','shipped','delivered','cancelled'];
foreach ($statuses as $s) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders WHERE status='$s'"));
    $stats['orders_' . $s] = $r['c'];
}

header('Content-Type: application/json');
echo json_encode($stats);
?>
