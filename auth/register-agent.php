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
$userRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role, phone FROM users WHERE id = $userId LIMIT 1"));

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
$existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, is_approved FROM agents WHERE user_id = $userId LIMIT 1"));

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
    $sql = "UPDATE agents
            SET agency_name = '$agencyName', contact_no = '$userPhone', cnic = '$cnic',
                province = '$province', city = '$city', is_approved = 0, approved_at = NULL
            WHERE user_id = $userId";
    mysqli_query($conn, $sql);
} else {
    $sql = "INSERT INTO agents (user_id, agency_name, contact_no, cnic, province, city, is_approved)
            VALUES ($userId, '$agencyName', '$userPhone', '$cnic', '$province', '$city', 0)";
    mysqli_query($conn, $sql);
}

echo json_encode(['success' => true, 'msg' => 'Application submitted! Admin will review it shortly.']);
exit();
?>