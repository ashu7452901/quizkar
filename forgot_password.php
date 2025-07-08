<?php
session_start();
include('../config.php');

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mobile = $_POST['mobile'];

    $check = $conn->query("SELECT * FROM users WHERE mobile = '$mobile'");
    if ($check->num_rows == 1) {
        $otp = rand(100000, 999999);
        $_SESSION['forgot_otp'] = $otp;
        $_SESSION['forgot_mobile'] = $mobile;

        // Fast2SMS API
        $fields = array(
            "sender_id" => "FSTSMS",
            "message" => "Your OTP for password reset is $otp. Do not share it.",
            "language" => "english",
            "route" => "p",
            "numbers" => $mobile,
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => array(
                "authorization: YOUR_FAST2SMS_API_KEY_HERE",
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        curl_exec($curl);
        curl_close($curl);

        header("Location: verify_forgot_otp.php");
        exit();
    } else {
        $message = "❌ Mobile number not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #d4fc79, #96e6a1);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-box {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input {
            padding: 12px;
            width: 100%;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        button {
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 10px;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="forgot-box">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="mobile" placeholder="Enter your registered mobile number" required>
        <button type="submit">Send OTP</button>
    </form>
</div>

</body>
</html>
