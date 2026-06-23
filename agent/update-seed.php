<?php
// ============================================================
//   agent/update-seed.php — Algorithm 2: UpdateSeed
//   Sirf price & stock update karta hai (UI yehi 2 fields leti hai)
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();
$price   = floatval($_POST['price'] ?? 0);
$stock   = intval($_POST['stock']   ?? 0);

if ($seedId <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

$sql = "UPDATE products
        SET price = $price, stock = $stock
        WHERE id = $seedId AND agent_id = $agentId";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>