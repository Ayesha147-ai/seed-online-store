<?php
// ============================================================
//   orders/place-order.php — Order Placement, Validation, Stripe Integration & Stock Update Logic
//   Yeh file checkout form se data receive karke cart items ko database prices aur stock ke sath verify karti hai, optional Stripe payment process karti hai, orders aur order items ko database mein save karke stock reduce karti hai, aur transaction record create karke success response return karti hai
// ============================================================

// Session, database aur helper functions ki required files load karo
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Check karo ke user logged in hai aur response JSON format mein do
requireLogin();
header('Content-Type: application/json');

// Sirf POST request ko allow karo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'msg' => 'Invalid request.']);
    exit();
}

// Checkout form se user aur delivery information hasil karo
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
// Cart ke JSON data ko PHP array mein convert karo
$cartItems = json_decode($cartJson, true);
if (empty($cartItems)) {
    echo json_encode(['success' => false, 'msg' => 'Cart is empty']);
    exit();
}

// Validate required fields
// Required checkout fields empty hain ya nahi check karo
if (empty($fullName) || empty($email) || empty($phone) || empty($city) || empty($address)) {
    echo json_encode(['success' => false, 'msg' => 'Please fill all required fields']);
    exit();
}

// Calculate totals — price DB se fetch karo, client se kabhi trust mat karo
// Har product ki real price database se verify karke subtotal calculate karo
$subtotal = 0;
$verifiedItems = [];

// Cart ke har item ko verify aur process karo
foreach ($cartItems as $item) {
    // Product ID aur quantity ko cart se hasil karo
    $rawId = $item['id'] ?? 0;
    $productId = intval(preg_replace('/[^0-9]/', '', $rawId));
    $qty       = intval($item['qty'] ?? 1);

    // Invalid product ID ya quantity ko skip karo
    if ($productId <= 0 || $qty <= 0) continue;

    // Approved product ki price aur available stock database se fetch karo
    $priceStmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id = ? AND status = 'approved' LIMIT 1");
    mysqli_stmt_bind_param($priceStmt, 'i', $productId);
    mysqli_stmt_execute($priceStmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($priceStmt));

    // Product invalid ho ya stock required quantity se kam ho to item skip karo
    if (!$product || $product['stock'] < $qty) continue;

    // Database wali real price se subtotal calculate karo
    $realPrice = floatval($product['price']);
    $subtotal += $realPrice * $qty;

    // Verified product details ko order processing ke liye store karo
    $verifiedItems[] = [
        'id'    => $product['id'],
        'name'  => $product['name'],
        'qty'   => $qty,
        'price' => $realPrice
    ];
}

// Agar koi valid item nahi mila to order create na karo
if (empty($verifiedItems)) {
    echo json_encode(['success' => false, 'msg' => 'No valid items in cart']);
    exit();
}

// Delivery charge add karke final order total calculate karo
$delivery   = 50;
$grandTotal = $subtotal + $delivery;

// Generate order number
// Unique order number generate karo
$orderNumber = generateOrderNumber();

// ==========================================
// STRIPE PAYMENT PROCESSING (Test Mode)
// ==========================================
// Agar payment method Stripe hai to pehle payment process karo
if ($payment === 'stripe') {
    // Stripe token missing ho to payment process na karo
    if (empty($stripeToken)) {
        echo json_encode(['success' => false, 'msg' => 'Stripe token is missing.']);
        exit();
    }

    // Yahan apni Stripe ki Secret Test Key daliye (sk_test_...)
    $stripeSecretKey = 'sk_test_51UAQgmQodAeOwyHCYg1EEcCwEAsznbGUU4MMFNDZ8FzBEPUL7BAz0pHYziYeAdSo3tKDDk4mRHuKHubxisC3EeRJ00T0X16oUr'; 

    // Stripe Charges API ke liye cURL request initialize karo
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

    // Stripe API ka response aur HTTP status code hasil karo
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Stripe response ko decode karke payment result check karo
    $stripeRes = json_decode($response, true);
    if ($httpCode !== 200 || isset($stripeRes['error'])) {
        $errorMsg = $stripeRes['error']['message'] ?? 'Payment failed.';
        echo json_encode(['success' => false, 'msg' => 'Stripe Error: ' . $errorMsg]);
        exit();
    }
}

// Save order
// Verified checkout information aur totals ko orders table mein save karo
$orderStmt = mysqli_prepare($conn, "INSERT INTO orders
        (order_number, user_id, full_name, email, phone, city, province, warehouse, address,
         payment_method, subtotal, delivery_charge, grand_total, status)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'placed')");
mysqli_stmt_bind_param($orderStmt, 'sissssssssddd',
    $orderNumber, $userId, $fullName, $email, $phone, $city, $province,
    $warehouse, $address, $payment, $subtotal, $delivery, $grandTotal);

// Order ko database mein save karo
if (!mysqli_stmt_execute($orderStmt)) {
    error_log('Order insert failed: ' . mysqli_stmt_error($orderStmt));
    echo json_encode(['success' => false, 'msg' => 'Order failed. Please try again.']);
    exit();
}

// Newly created order ki ID hasil karo
$orderId = mysqli_insert_id($conn);

// Save order items
// Order ke tamam verified products ko order_items table mein save karo
$itemStmt  = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
             VALUES (?, ?, ?, ?, ?, ?)");
$stockStmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

// Har verified item ka order record aur stock update process karo
foreach ($verifiedItems as $item) {
    // Item ki total price quantity ke hisaab se calculate karo
    $totalPrice = $item['price'] * $item['qty'];

    // Order item database mein save karo
    mysqli_stmt_bind_param($itemStmt, 'iisidd', $orderId, $item['id'], $item['name'], $item['qty'], $item['price'], $totalPrice);
    mysqli_stmt_execute($itemStmt);

    // Product ka stock ordered quantity se reduce karo
    mysqli_stmt_bind_param($stockStmt, 'iii', $item['qty'], $item['id'], $item['qty']);
    mysqli_stmt_execute($stockStmt);
}

// Save transaction record
// Payment method ke according transaction ka payment status set karo
$payStatus = ($payment === 'cod') ? 'pending' : 'paid';

// Transaction details database mein save karo
$tStmt = mysqli_prepare($conn, "INSERT INTO transactions (order_id, user_id, pay_amount, pay_method, pay_status)
         VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($tStmt, 'iidss', $orderId, $userId, $grandTotal, $payment, $payStatus);
mysqli_stmt_execute($tStmt);

// Return success with order number
// Successful order ka order number aur final total response mein return karo
echo json_encode([
    'success'      => true,
    'order_number' => $orderNumber,
    'grand_total'  => $grandTotal
]);
?>