<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$sql = "SELECT p.*, u.name as agent_name, c.name as category_name
        FROM products p
        JOIN users u      ON p.agent_id    = u.id
        JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $sql);
$seeds  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $seeds[] = $row;
}

header('Content-Type: application/json');
echo json_encode($seeds);
?>
