<?php
// ============================================================
//   admin/get-settings.php
//   Platform settings wapas deta hai (Platform Name, Support Email/Phone)
// ============================================================
require_once '../includes/session.php';
require_once '../includes/db.php';
requireAdmin();
header('Content-Type: application/json');

$result   = mysqli_query($conn, "SELECT platform_name, support_email, support_phone FROM platform_settings WHERE id = 1 LIMIT 1");
$settings = mysqli_fetch_assoc($result);

if (!$settings) {
    // Agar kisi wajah se row hi na mile, to safe defaults bhej do
    $settings = [
        'platform_name' => 'TrackSeed',
        'support_email' => 'support@trackseed.pk',
        'support_phone' => ''
    ];
}

echo json_encode($settings);
?>