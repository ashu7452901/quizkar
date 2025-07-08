<?php
session_start();
$email = $_GET['email'] ?? '';
$message = $_SESSION['otp_message'] ?? '';
$alertType = $_SESSION['otp_alert'] ?? '';
unset($_SESSION['otp_message'], $_SESSION['otp_alert']);
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
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .box {
            background: #fff;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        input[type="text"] {
            padding: 12px;
            width: 90%;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            padding: 12px;
            width: 95%;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 8px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #resendBtn {
            background-color: #28a745;
        }
        #resendBtn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        #countdown {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        .alert {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 15px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Verify OTP</h2>

    <?php if (!empty($message)) { ?>
        <div class="alert <?= $alertType === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php } ?>

    <form method="POST" action="reset_password.php">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <input type="text" name="otp" placeholder="Enter OTP" required><br>
        <button type="submit" name="verify_otp">Verify OTP</button>
    </form>

    <button id="resendBtn" onclick="resendOTP()" disabled>Resend OTP</button>
    <div id="countdown">You can resend OTP in 30 seconds</div>
</div>

<script>
    let countdownTime = 30;
    let resendLimit = 10;
    let resendCount = 0;
    let countdownInterval;

    function startCountdown() {
        document.getElementById("resendBtn").disabled = true;
        countdownTime = 30;
        document.getElementById("countdown").innerText = `You can resend OTP in ${countdownTime} seconds`;
        countdownInterval = setInterval(() => {
            countdownTime--;
            document.getElementById("countdown").innerText = `You can resend OTP in ${countdownTime} seconds`;
            if (countdownTime <= 0) {
                clearInterval(countdownInterval);
                if (resendCount < resendLimit) {
                    document.getElementById("resendBtn").disabled = false;
                    document.getElementById("countdown").innerText = `You can resend OTP now (${resendLimit - resendCount} left)`;
                } else {
                    document.getElementById("resendBtn").disabled = true;
                    document.getElementById("countdown").innerText = "❌ OTP resend limit reached.";
                }
            }
        }, 1000);
    }

    function resendOTP() {
        if (resendCount >= resendLimit) return;
        resendCount++;
        document.getElementById("resendBtn").disabled = true;
        document.getElementById("countdown").innerText = "Sending OTP...";

        fetch('resend_otp.php?email=<?= urlencode($email) ?>')
            .then(res => res.text())
            .then(data => {
                document.getElementById("countdown").innerText = "OTP sent!";
                startCountdown();
            })
            .catch(err => {
                document.getElementById("countdown").innerText = "Failed to send OTP.";
            });
    }

    window.onload = startCountdown;
</script>

</body>
</html>
