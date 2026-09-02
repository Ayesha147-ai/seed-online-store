<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();
header('Content-Type: application/json');

$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();

if ($seedId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ? AND agent_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $seedId, $agentId);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Not found or unauthorized']);
}
?>
