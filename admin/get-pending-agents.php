<?php
// Session aur database ki required files load karo aur admin access verify karo
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

// Database se pending agent applications aur unki details hasil karo
$sql = "SELECT u.id, u.name, u.email, u.phone, u.created_at,
               a.agency_name, a.cnic, a.city, a.province, a.is_approved
        FROM users u
        JOIN agents a ON a.user_id = u.id
        WHERE a.is_approved = 0
        ORDER BY u.created_at DESC";

$result = mysqli_query($conn, $sql);
$agents = [];

// Database se tamam pending agents ko ek ek karke array mein add karo
while ($row = mysqli_fetch_assoc($result)) {
    $agents[] = $row;
}

// Pending agents ko JSON format mein convert karke response ke tor par send karo
header('Content-Type: application/json');
echo json_encode($agents);
?>