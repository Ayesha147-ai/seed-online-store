<?php
// ============================================================
//   includes/helpers.php — Helper Functions
// ============================================================

// Generate unique order number: TS-XXXXXX
// Unique order number generate karo jisme TS prefix aur random digits hoti hain
function generateOrderNumber() {
    return '#TS-' . strtoupper(substr(uniqid(), -4)) . rand(10, 99);
}

// Sanitize input
// User input ko trim aur escape karke SQL injection se bachane mein help karo
function clean($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// JSON response helper (for AJAX endpoints)
// AJAX endpoint ke liye JSON response aur HTTP status code send karo
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Redirect with message
// User ko message ke saath specified URL par redirect karo
function redirectWithMsg($url, $key, $value) {
    header("Location: $url?$key=" . urlencode($value));
    exit();
}
// Format price
// Price ko Rs format mein comma ke saath display karo
function formatPrice($price) {
    return 'Rs ' . number_format($price, 0);
}
?>