<?php
// ============================================================
//   agent/delete-seed.php — Delete Seed Product
//   Yeh file agent ke zariye uski apni add ki hui seed product ko delete karti hai
// ============================================================
// Session aur database ki required files load karo aur agent access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();
header('Content-Type: application/json');

// POST se seed ki ID receive karo aur current agent ki ID hasil karo
$seedId  = intval($_POST['seed_id'] ?? 0);
$agentId = getUserId();

// Check karo ke seed ID valid hai ya nahi
if ($seedId <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
    exit();
}

// Sirf current agent ki apni seed ko delete karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ? AND agent_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $seedId, $agentId);

// Check karo ke seed successfully delete hui hai ya nahi
if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    // Seed successfully delete hone ka response send karo
    echo json_encode(['success' => true]);
} else {
    // Agar seed na mile ya agent authorized na ho to failure response send karo
    echo json_encode(['success' => false, 'msg' => 'Not found or unauthorized']);
}
?>