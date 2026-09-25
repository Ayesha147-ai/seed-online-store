<?php
require_once '../includes/db.php';

$productId = intval($_GET['product_id'] ?? 0);
if ($productId <= 0) {
    echo json_encode([]); exit();
}

// Product ke tamam reviews aur farmer ka naam database se fetch karne ke liye query prepare karo
$stmt = mysqli_prepare($conn, "SELECT f.rating, f.comment, f.created_at, u.name as farmer_name
        FROM feedback f
        JOIN users u ON f.user_id = u.id
        WHERE f.product_id = ?
        ORDER BY f.created_at DESC");
// Product ID ko query ke parameter ke saath bind karo
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reviews  = [];
$totalRating = 0;
$count    = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $reviews[] = $row;
    $totalRating += $row['rating'];
    $count++;
}

$avgRating = $count > 0 ? round($totalRating / $count, 1) : 0;

header('Content-Type: application/json');
echo json_encode([
    'reviews'    => $reviews,
    'avg_rating' => $avgRating,
    'count'      => $count
]);

?>