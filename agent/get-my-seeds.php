<?php
// ============================================================
//   agent/get-my-seeds.php — Fetch Agent's Seed Products
//   Yeh file current logged-in agent ke add kiye gaye tamam seed products ki list hasil karti hai
// ============================================================
// Session aur database ki required files load karo aur agent access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAgent();

// Current logged-in agent ki ID hasil karo
$agentId = getUserId();

// Current agent ke products aur unki category ka naam fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name as category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.agent_id = ?
        ORDER BY p.created_at DESC");

// Agent ID ko query ke parameter ke saath bind karo
mysqli_stmt_bind_param($stmt, 'i', $agentId);

// Query execute karo aur result hasil karo
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Seeds store karne ke liye empty array banao
$seeds  = [];

// Har product ko seeds array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $seeds[] = $row;
}

// Response ko JSON format mein return karo
header('Content-Type: application/json');
echo json_encode($seeds);
?>