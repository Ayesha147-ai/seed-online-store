<?php
// ============================================================
//   orders/place-order.php — Order Placement, Validation, Stripe Integration & Stock Update Logic
//   Yeh file checkout form se data receive karke cart items ko database prices aur stock ke sath verify karti hai, optional Stripe payment process karti hai, orders aur order items ko database mein save karke stock reduce karti hai, aur transaction record create karke success response return karti hai
// ============================================================

// Session, database aur helper functions ki required files load karo
require_once '../includes/session.php';
require_once '../includes/db.php';
require_once '../includes/config.php';
require_once '../includes/helpers.php';
require_once '../vendor/autoload.php';

$env = parse_ini_file(__DIR__ . '/../.env');

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

if (!in_array($payment, ['cod', 'stripe'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'msg' => 'Please select a valid payment method.']);
    exit();
}

// Parse cart from JSON
// Cart ke JSON data ko PHP array mein convert karo
$cartItems = json_decode($cartJson, true);
if (!is_array($cartItems) || empty($cartItems)) {
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
$itemErrors = [];

// Cart ke har item ko verify aur process karo
foreach ($cartItems as $item) {
    if (!is_array($item)) {
        $itemErrors[] = 'The cart contains an invalid item. Remove it and add the product again.';
        continue;
    }

    // Sirf database product IDs accept karo; idx-/veg-/fru-/herb- IDs demo catalog ke hain.
    $rawId = $item['id'] ?? '';
    if ((!is_string($rawId) && !is_int($rawId)) ||
        !preg_match('/^(?:db-)?([1-9][0-9]*)$/D', (string)$rawId, $idMatch)) {
        $itemName = isset($item['name']) && is_string($item['name']) && trim($item['name']) !== ''
            ? trim($item['name'])
            : 'This item';
        $itemErrors[] = $itemName . ' is not linked to an approved store product. Remove it and add a seed from a category page.';
        continue;
    }
    $productId = (int)$idMatch[1];

    // Product quantity ko positive integer hona chahiye.
    $qty = filter_var($item['qty'] ?? 1, FILTER_VALIDATE_INT);
    if ($qty === false || $qty <= 0) {
        $itemName = isset($item['name']) && is_string($item['name']) && trim($item['name']) !== ''
            ? trim($item['name'])
            : 'This item';
        $itemErrors[] = $itemName . ' has an invalid quantity. Remove it and add the product again.';
        continue;
    }

    // Product details fetch karo taa-ke unavailable status aur stock ki specific wajah di ja sake.
    $priceStmt = mysqli_prepare($conn, "SELECT id, name, price, stock, status FROM products WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($priceStmt, 'i', $productId);
    mysqli_stmt_execute($priceStmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($priceStmt));

    if (!$product) {
        $itemErrors[] = 'A product in your cart is no longer available. Remove it and add it again from a category page.';
        continue;
    }
    if ($product['status'] !== 'approved') {
        $itemErrors[] = $product['name'] . ' is not approved for sale yet. Remove it and choose an approved seed.';
        continue;
    }
    if ((int)$product['stock'] < $qty) {
        $itemErrors[] = $product['name'] . ' has only ' . (int)$product['stock'] . ' in stock; reduce the quantity or remove it.';
        continue;
    }

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

// Invalid item ho to partial order create na karo; user ko exact wajah batao.
if (!empty($itemErrors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'msg' => implode(' ', $itemErrors)]);
    exit();
}

if (empty($verifiedItems)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'msg' => 'Your cart is empty. Add an approved seed before placing the order.']);
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
    $stripeSecretKey = $env['STRIPE_SECRET_KEY'];


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
    'order_id'     => $orderId,
    'order_number' => $orderNumber,
    'grand_total'  => $grandTotal
]);
?>