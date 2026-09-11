<?php
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

// Database se pending seeds aur unke agent aur category ki details hasil karo
$sql = "SELECT p.*, u.name as agent_name, c.name as category_name
        FROM products p
        JOIN users u      ON p.agent_id    = u.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $sql);
$seeds  = [];

// Database se tamam pending seeds ko ek ek karke array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $seeds[] = $row;
}

// Pending seeds ko JSON format mein convert karke response ke tor par send karo
header('Content-Type: application/json');
echo json_encode($seeds);
?>