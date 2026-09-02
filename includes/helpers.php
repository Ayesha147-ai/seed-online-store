<?php
// ============================================================
//   includes/helpers.php — Helper Functions
// ============================================================

// Generate unique order number: TS-XXXXXX
function generateOrderNumber() {
    return '#TS-' . strtoupper(substr(uniqid(), -4)) . rand(10, 99);
}

// Sanitize input
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// JSON response helper (for AJAX endpoints)
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Redirect with message
function redirectWithMsg($url, $key, $value) {
    header("Location: $url?$key=" . urlencode($value));
    exit();
}
// Format price
function formatPrice($price) {
    return 'Rs ' . number_format($price, 0);
}
?>
