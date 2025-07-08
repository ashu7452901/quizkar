<?php
session_start();
include('../config.php'); // adjust path if needed

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo "❌ Email required.";
    exit;
}

// ✅ Fetch mobile number from users table
$stmt = $conn->prepare("SELECT mobile FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "❌ User not found.";
    exit;
}

$row = $result->fetch_assoc();
$mobile = $row['mobile'];

// ✅ Generate 6-digit OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;

// ✅ Send OTP via Fast2SMS
$apiKey = "YOUR_FAST2SMS_API_KEY";  // Replace this
$senderId = "FSTSMS";               // default sender ID
$message = urlencode("Your OTP for password reset is $otp. Please do not share it.");
$route = "p";                       // transactional route
$numbers = $mobile;

$postData = array(
    'authorization' => $apiKey,
    'sender_id' => $senderId,
    'message' => $message,
    'language' => 'english',
    'route' => $route,
    'numbers' => $numbers,
);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPHEADER => array(
        "authorization: $apiKey",
        "Content-Type: application/x-www-form-urlencoded"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "❌ cURL Error: $err";
} else {
    echo "✅ OTP sent successfully!";
}
?>
