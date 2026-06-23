<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();

if ($seedId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

$sql = "DELETE FROM products WHERE id = $seedId AND agent_id = $agentId";
if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Not found or unauthorized']);
}
?>
