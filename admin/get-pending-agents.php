<?php
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();

$sql = "SELECT u.id, u.name, u.email, u.phone, u.created_at,
               a.agency_name, a.cnic, a.city, a.province, a.is_approved
        FROM users u
        JOIN agents a ON a.user_id = u.id
        WHERE a.is_approved = 0
        ORDER BY u.created_at DESC";

$result = mysqli_query($conn, $sql);
$agents = [];
while ($row = mysqli_fetch_assoc($result)) {
    $agents[] = $row;
}

header('Content-Type: application/json');
echo json_encode($agents);
?>
