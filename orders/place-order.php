<?php
// ============================================================
//   orders/place-order.php — Algorithm 4: PlaceOrder
//   checkout.html form action OR AJAX call
// ============================================================


require_once '../includes/db.php';
require_once '../includes/session.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.html');
    exit();
}

$userId    = getUserId();
$fullName  = clean($conn, $_POST['fullName']  ?? '');
$email     = clean($conn, $_POST['email']     ?? '');
$phone     = clean($conn, $_POST['phone']     ?? '');
$city      = clean($conn, $_POST['city']      ?? '');
$province  = clean($conn, $_POST['province']  ?? '');
$warehouse = clean($conn, $_POST['warehouse'] ?? '');
$address   = clean($conn, $_POST['address']   ?? '');
$payment   = clean($conn, $_POST['payment']   ?? 'cod');
$cartJson  = $_POST['cart'] ?? '[]';

// Parse cart from JSON
$cartItems = json_decode($cartJson, true);
if (empty($cartItems)) {
    echo json_encode(['success' => false, 'msg' => 'Cart is empty']);
    exit();
}

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($city) || empty($address)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields']);
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += floatval($item['price']) * intval($item['qty']);
}
$delivery   = 50;
$grandTotal = $subtotal + $delivery;

// Generate order number
$orderNumber = generateOrderNumber();

// Save order
$sql = "INSERT INTO orders
        (order_number, user_id, full_name, email, phone, city, province, warehouse, address,
         payment_method, subtotal, delivery_charge, grand_total, status)
        VALUES
        ('$orderNumber', $userId, '$fullName', '$email', '$phone', '$city', '$province',
         '$warehouse', '$address', '$payment', $subtotal, $delivery, $grandTotal, 'placed')";

if (!mysqli_query($conn, $sql)) {
    echo json_encode(['success' => false, 'msg' => 'Order failed: ' . mysqli_error($conn)]);
    exit();
}

$orderId = mysqli_insert_id($conn);

// Save order items
foreach ($cartItems as $item) {
    $productId  = intval($item['id'] ?? 0);
    $qty        = intval($item['qty']  ?? 1);
    $unitPrice  = floatval($item['price'] ?? 0);
    $totalPrice = $unitPrice * $qty;
    $pName      = clean($conn, $item['name'] ?? '');

    // Handle cart ID format: 'veg-3', 'fru-2', 'herb-1', 'idx-1'
    // Extract numeric product ID from DB
    if (is_numeric($productId) && $productId > 0) {
        $dbProductId = $productId;
    } else {
        // Try to find by name
        $pNameEsc    = mysqli_real_escape_string($conn, $pName);
        $pResult     = mysqli_query($conn, "SELECT id FROM products WHERE name = '$pNameEsc' LIMIT 1");
        $pRow        = mysqli_fetch_assoc($pResult);
        $dbProductId = $pRow['id'] ?? 1;
    }

    $iSql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
             VALUES ($orderId, $dbProductId, '$pName', $qty, $unitPrice, $totalPrice)";
    mysqli_query($conn, $iSql);

    // Deduct stock
    mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = $dbProductId AND stock >= $qty");
}

// Save transaction record
$tSql = "INSERT INTO transactions (order_id, user_id, pay_amount, pay_method, pay_status)
         VALUES ($orderId, $userId, $grandTotal, '$payment',
         " . ($payment === 'cod' ? "'pending'" : "'paid'") . ")";
mysqli_query($conn, $tSql);

// Return success with order number
echo json_encode([
    'success'      => true,
    'order_number' => $orderNumber,
    'grand_total'  => $grandTotal
]);
?>
