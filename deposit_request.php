<?php
session_start();
include('../config.php');

// User login check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cashfree credentials
$APP_ID = "6776430195ffb72c9a86cbfc15346776";
$SECRET_KEY = "cfsk_ma_prod_0c7db594ae4802bc1b8ec028fb789d92_c9573d4b";
$CURRENCY = "INR";

// User data
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Payment handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $orderId = "ORD" . time() . rand(100, 999); // Unique order ID

    // Callback URLs
    $returnUrl = "https://yourdomain.com/dashboard.php"; // Success
    $notifyUrl = "https://yourdomain.com/cashfree_callback.php"; // Optional webhook

    // Create payment request using Cashfree's API
    $postData = array(
        "order_id" => $orderId,
        "order_amount" => $amount,
        "order_currency" => $CURRENCY,
        "customer_details" => array(
            "customer_id" => $user_id,
            "customer_name" => $username,
            "customer_email" => "user@example.com",  // Optional
            "customer_phone" => "9999999999"         // Optional
        ),
        "order_meta" => array(
            "return_url" => $returnUrl . "?order_id={order_id}"
        )
    );

    $headers = array(
        "Content-Type: application/json",
        "x-api-version: 2022-09-01",
        "x-client-id: $APP_ID",
        "x-client-secret: $SECRET_KEY"
    );

    $ch = curl_init("https://api.cashfree.com/pg/orders");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['payment_link'])) {
        header("Location: " . $result['payment_link']);
        exit();
    } else {
        echo "❌ Payment initiation failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deposit via Cashfree</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="text-center mb-4">💰 Deposit</h3>
            <form method="POST">
                <div class="mb-3">
                    <label>Enter Amount (₹)</label>
                    <input type="number" name="amount" min="10" required class="form-control" />
                </div>
                <button type="submit" class="btn btn-primary w-100">Pay with Cashfree</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
