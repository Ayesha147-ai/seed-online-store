<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

$agentId = getUserId();
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.agent_id = ?
        ORDER BY p.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $agentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$seeds  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $seeds[] = $row;
}

header('Content-Type: application/json');
echo json_encode($seeds);
?>
