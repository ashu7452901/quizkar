<?php
session_start();
include('../config.php');

if (!isset($_SESSION['resend_count'])) {
    $_SESSION['resend_count'] = 0;
}

$error = "";

// Handle OTP resend
if (isset($_GET['resend']) && $_SESSION['resend_count'] < 10) {
    $otp = rand(100000, 999999);
    $_SESSION['reg_otp'] = $otp;
    $_SESSION['resend_count']++;

    $mobile = $_SESSION['reg_mobile'];

    // Fast2SMS API
    $fields = array(
        "sender_id" => "FSTSMS",
        "message" => "Your new OTP is $otp. Do not share it.",
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
            "authorization: 2gbX9KZzNOob3MSDbe2gbGc3qDbMF1aIC9Gf5GqOSLWqcVzjQzdLbAmnceBl",
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json"
        ),
    ));

    curl_exec($curl);
    curl_close($curl);
}

// Verify OTP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];
    if ($entered_otp == $_SESSION['reg_otp']) {
        $name = $_SESSION['reg_name'];
        $mobile = $_SESSION['reg_mobile'];
        $email = $_SESSION['reg_email'];
        $password = $_SESSION['reg_password'];

        $check = $conn->query("SELECT * FROM users WHERE mobile = '$mobile' OR email = '$email'");
        if ($check->num_rows > 0) {
            echo "❌ User already registered.";
            exit();
        }

        $sql = "INSERT INTO users (name, mobile, email, password, wallet, status, created_at)
                VALUES ('$name', '$mobile', '$email', '$password', 0, 'active', NOW())";

        if ($conn->query($sql) === TRUE) {
            session_unset();
            session_destroy();
            echo "<script>alert('✅ Registration successful! Please login.');window.location.href='login.php';</script>";
        } else {
            echo "❌ Database Error: " . $conn->error;
        }
    } else {
        $error = "❌ Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f9d423, #ff4e50);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .otp-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        input {
            margin: 10px 0;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            width: 100%;
        }
        button {
            padding: 12px;
            margin-top: 10px;
            background-color: #ff4e50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #e03e3e;
        }
        .error-msg {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: center;
        }
        #resend {
            margin-top: 15px;
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
            font-weight: bold;
        }
        #resend.disabled {
            color: gray;
            text-decoration: none;
            cursor: not-allowed;
        }
        #timer {
            font-size: 14px;
            margin-top: 5px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="otp-box">
    <h2>Enter OTP</h2>

    <?php if ($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="otp" placeholder="Enter OTP sent to your mobile" required>
        <button type="submit">Verify OTP</button>
    </form>

    <div id="timer">You can resend OTP in <span id="countdown">30</span> seconds</div>

    <?php if ($_SESSION['resend_count'] < 10): ?>
        <div id="resend" class="disabled">Resend OTP</div>
    <?php else: ?>
        <div id="resend" class="disabled">Resend Limit Reached</div>
    <?php endif; ?>
</div>

<script>
    let countdown = 30;
    const countdownEl = document.getElementById('countdown');
    const resendBtn = document.getElementById('resend');

    let timer = setInterval(() => {
        countdown--;
        countdownEl.innerText = countdown;
        if (countdown <= 0) {
            clearInterval(timer);
            if (!resendBtn.classList.contains('disabled')) return;
            if (resendBtn.innerText === "Resend Limit Reached") return;
            resendBtn.classList.remove('disabled');
            resendBtn.innerText = "Resend OTP";
            resendBtn.onclick = function () {
                resendBtn.classList.add('disabled');
                window.location.href = "verify_otp.php?resend=1";
            }
        }
    }, 1000);
</script>

</body>
</html>
