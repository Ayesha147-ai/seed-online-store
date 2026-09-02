<?php
// ============================================================
//   agent/update-seed.php — Algorithm 2: UpdateSeed
//   Sirf price & stock update karta hai (UI yehi 2 fields leti hai)
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();
header('Content-Type: application/json');

$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();
$price   = floatval($_POST['price'] ?? 0);
$stock   = intval($_POST['stock']   ?? 0);

if ($seedId <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE products
        SET price = ?, stock = ?
        WHERE id = ? AND agent_id = ?");
mysqli_stmt_bind_param($stmt, 'diii', $price, $stock, $seedId, $agentId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>