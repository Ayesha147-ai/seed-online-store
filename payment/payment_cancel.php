<?php
// ============================================================
//   payment/payment_cancel.php — Payment Cancellation Notice & Cart Return Page Logic
//   Yeh file user session start karke payment cancellation ka message display karti hai, user ko assure karti hai ke unke cart items safe hain, aur unhe wapas cart page par jaane ka link provide karti hai
// ============================================================
// Session start kar rahe hain taake session ki information available ho.
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Browser tab me payment canceled ka title show hoga. -->
    <title>Payment Canceled</title>
</head>
<body>
    <!-- Payment cancel hone ka message center me display kiya ja raha hai. -->
    <div style="text-align: center; margin-top: 50px;">
        <!-- User ko bataya ja raha hai ke payment cancel ho gayi hai. -->
        <h2>Payment Canceled ❌</h2>

        <!-- User ko confirm kiya ja raha hai ke cart items safe hain. -->
        <p>You canceled the payment process. Your cart items are still safe!</p>

        <!-- User ko cart page par wapas jane ka link diya gaya hai. -->
        <a href="../cart.php">Return to Cart</a>
    </div>
</body>
</html>