<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

$agentId = getUserId();
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.agent_id = $agentId
        ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $sql);
$seeds  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $seeds[] = $row;
}

header('Content-Type: application/json');
echo json_encode($seeds);
?>
