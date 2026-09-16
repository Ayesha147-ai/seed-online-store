<?php
// ============================================================
//   agent/update-seed.php — Algorithm 2: UpdateSeed
//   Sirf price & stock update karta hai (UI yehi 2 fields leti hai)
// ============================================================

// Session aur database ki required files load karo aur agent access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

// Response ko JSON format mein set karo
header('Content-Type: application/json');

// POST se seed ID, agent ID, price aur stock ki values hasil karo
$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();
$price   = floatval($_POST['price'] ?? 0);
$stock   = intval($_POST['stock']   ?? 0);

// Check karo ke seed ID aur price valid hain ya nahi
if ($seedId <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid data']);
    exit();
}

// Sirf current agent ki seed ka price aur stock update karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "UPDATE products
        SET price = ?, stock = ?
        WHERE id = ? AND agent_id = ?");
mysqli_stmt_bind_param($stmt, 'diii', $price, $stock, $seedId, $agentId);

// Check karo ke update successfully execute hua hai ya nahi
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed']);
}
?>