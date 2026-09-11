<?php
// ============================================================
//   admin/save-settings.php
//   Platform settings ko database mein save karta hai
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
requireAdmin();
header('Content-Type: application/json');

$platformName = clean($conn, $_POST['platform_name'] ?? '');
$supportEmail = clean($conn, $_POST['support_email'] ?? '');
$supportPhone = clean($conn, $_POST['support_phone'] ?? '');

if (empty($platformName) || empty($supportEmail) || empty($supportPhone)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all fields.']);
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE platform_settings
    SET platform_name = ?, support_email = ?, support_phone = ?
    WHERE id = 1");
mysqli_stmt_bind_param($stmt, 'sss', $platformName, $supportEmail, $supportPhone);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'msg' => 'Settings updated!']);
} else {
    echo json_encode(['success' => false, 'msg' => 'Update failed. Please try again.']);
}
?>