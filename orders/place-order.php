<?php
// ============================================================
//   orders/place-order.php — Algorithm 4: PlaceOrder
//   checkout.html form action OR AJAX call
// ============================================================

require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';
require_once '../includes/config.php';

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
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
$stripeToken = $_POST['stripeToken'] ?? '';
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

// Calculate totals — price DB se fetch karo, client se kabhi trust mat karo
$subtotal = 0;
$verifiedItems = [];

foreach ($cartItems as $item) {
    $rawId = $item['id'] ?? 0;
    $productId = intval(preg_replace('/[^0-9]/', '', $rawId));
    $qty       = intval($item['qty'] ?? 1);

    if ($productId <= 0 || $qty <= 0) continue;

    $priceStmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id = ? AND status = 'approved' LIMIT 1");
    mysqli_stmt_bind_param($priceStmt, 'i', $productId);
    mysqli_stmt_execute($priceStmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($priceStmt));

    if (!$product || $product['stock'] < $qty) continue;

    $realPrice = floatval($product['price']);
    $subtotal += $realPrice * $qty;

    $verifiedItems[] = [
        'id'    => $product['id'],
        'name'  => $product['name'],
        'qty'   => $qty,
        'price' => $realPrice
    ];
}

if (empty($verifiedItems)) {
    echo json_encode(['success' => false, 'msg' => 'No valid items in cart']);
    exit();
}

$delivery   = 50;
$grandTotal = $subtotal + $delivery;

// Generate order number
$orderNumber = generateOrderNumber();

// ==========================================
// STRIPE PAYMENT PROCESSING (Test Mode)
// ==========================================
if ($payment === 'stripe') {
    if (empty($stripeToken)) {
        echo json_encode(['success' => false, 'msg' => 'Stripe token is missing.']);
        exit();
    }

    // Yahan apni Stripe ki Secret Test Key daliye (sk_test_...)
        $stripeSecretKey = STRIPE_SECRET_KEY; // .env se config.php ke zariye aa raha hai 

    $ch = curl_init('https://api.stripe.com/v1/charges');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $stripeSecretKey . ':');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'amount'      => intval($grandTotal * 100), // Stripe cents/paisa mein leta hai isliye * 100
        'currency'    => 'pkr',                     // Currency
        'source'      => $stripeToken,
        'description' => 'TrackSeed Order #' . $orderNumber
    ]));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $stripeRes = json_decode($response, true);
    if ($httpCode !== 200 || isset($stripeRes['error'])) {
        $errorMsg = $stripeRes['error']['message'] ?? 'Payment failed.';
        echo json_encode(['success' => false, 'msg' => 'Stripe Error: ' . $errorMsg]);
        exit();
    }
}

// Save order
$orderStmt = mysqli_prepare($conn, "INSERT INTO orders
        (order_number, user_id, full_name, email, phone, city, province, warehouse, address,
         payment_method, subtotal, delivery_charge, grand_total, status)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'placed')");
mysqli_stmt_bind_param($orderStmt, 'sissssssssddd',
    $orderNumber, $userId, $fullName, $email, $phone, $city, $province,
    $warehouse, $address, $payment, $subtotal, $delivery, $grandTotal);

if (!mysqli_stmt_execute($orderStmt)) {
    error_log('Order insert failed: ' . mysqli_stmt_error($orderStmt));
    echo json_encode(['success' => false, 'msg' => 'Order failed. Please try again.']);
    exit();
}

$orderId = mysqli_insert_id($conn);

// Save order items
$itemStmt  = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
             VALUES (?, ?, ?, ?, ?, ?)");
$stockStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

foreach ($verifiedItems as $item) {
    $totalPrice = $item['price'] * $item['qty'];

    mysqli_stmt_bind_param($itemStmt, 'iisidd', $orderId, $item['id'], $item['name'], $item['qty'], $item['price'], $totalPrice);
    mysqli_stmt_execute($itemStmt);

    mysqli_stmt_bind_param($stockStmt, 'iii', $item['qty'], $item['id'], $item['qty']);
    mysqli_stmt_execute($stockStmt);
}

// Save transaction record
$payStatus = ($payment === 'cod') ? 'pending' : 'paid';
$tStmt = mysqli_prepare($conn, "INSERT INTO transactions (order_id, user_id, pay_amount, pay_method, pay_status)
         VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($tStmt, 'iidss', $orderId, $userId, $grandTotal, $payment, $payStatus);
mysqli_stmt_execute($tStmt);

// Return success with order number
echo json_encode([
    'success'      => true,
    'order_number' => $orderNumber,
    'grand_total'  => $grandTotal
]);
?>