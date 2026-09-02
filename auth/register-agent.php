<?php
// ============================================================
//   auth/register-agent.php — Agent Application (Account Upgrade Model)
//   Naya account NAHI banta — existing logged-in user apply karta hai.
//   Role 'farmer' hi rehta hai jab tak admin approve na kare.
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

$userId = getUserId();

// Pehle role check karo
$stmt1 = mysqli_prepare($conn, "SELECT role, phone FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt1, 'i', $userId);
mysqli_stmt_execute($stmt1);
$userRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt1));

if (!$userRow) {
    echo json_encode(['success' => false, 'msg' => 'User not found.']);
    exit();
}

if ($userRow['role'] === 'agent') {
    echo json_encode(['success' => false, 'msg' => 'You are already a registered agent.']);
    exit();
}

if ($userRow['role'] === 'admin') {
    echo json_encode(['success' => false, 'msg' => 'Admin accounts cannot apply as agents.']);
    exit();
}

$city       = clean($conn, $_POST['city']         ?? '');
$province   = clean($conn, $_POST['province']     ?? '');
$agencyName = clean($conn, $_POST['businessName'] ?? '');
$cnic       = clean($conn, $_POST['cnic']         ?? '');

if (empty($city) || empty($province) || empty($agencyName) || empty($cnic)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields.']);
    exit();
}

$userPhone = clean($conn, $userRow['phone'] ?? '');

// Kya pehle se koi application/record hai?
$stmt2 = mysqli_prepare($conn, "SELECT id, is_approved FROM agents WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt2, 'i', $userId);
mysqli_stmt_execute($stmt2);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));

if ($existing) {
    if ($existing['is_approved'] == 0) {
        echo json_encode(['success' => false, 'msg' => 'Your agent application is already under review.']);
        exit();
    }
    if ($existing['is_approved'] == 1) {
        echo json_encode(['success' => false, 'msg' => 'You are already an approved agent.']);
        exit();
    }
    // is_approved == 2 (pehle reject hua tha) — dobara apply karne dete hain
    $stmt3 = mysqli_prepare($conn, "UPDATE agents
            SET agency_name = ?, contact_no = ?, cnic = ?,
                province = ?, city = ?, is_approved = 0, approved_at = NULL
            WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt3, 'sssssi', $agencyName, $userPhone, $cnic, $province, $city, $userId);
    mysqli_stmt_execute($stmt3);
} else {
    $stmt4 = mysqli_prepare($conn, "INSERT INTO agents (user_id, agency_name, contact_no, cnic, province, city, is_approved)
            VALUES (?, ?, ?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt4, 'isssss', $userId, $agencyName, $userPhone, $cnic, $province, $city);
    mysqli_stmt_execute($stmt4);
}

echo json_encode(['success' => true, 'msg' => 'Application submitted! Admin will review it shortly.']);
exit();
?>