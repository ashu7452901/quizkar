<?php
session_start();
include('../config.php');

// OTP and email from POST
$email = $_POST['email'] ?? '';
$entered_otp = $_POST['otp'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// If OTP is being verified
if (isset($_POST['verify_otp'])) {
    if (!isset($_SESSION['otp']) || $entered_otp != $_SESSION['otp']) {
        $_SESSION['otp_message'] = "❌ Invalid OTP. Please try again.";
        $_SESSION['otp_alert'] = "error";
        header("Location: verify_forgot_otp.php?email=" . urlencode($email));
        exit();
    } else {
        $_SESSION['otp_verified'] = true;
        $_SESSION['verified_email'] = $email;
    }
}

// If password is being reset
if (isset($_POST['reset_password'])) {
    if (!$new_password || !$confirm_password) {
        $error = "⚠️ Please fill in all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "🔒 Password must be at least 6 characters.";
    } else {
        $hashed = md5($new_password);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $_SESSION['verified_email']);
        $stmt->execute();

        // Clear OTP and email from session
        unset($_SESSION['otp'], $_SESSION['otp_verified'], $_SESSION['verified_email']);

        $success = "✅ Password has been reset successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body {
            background: linear-gradient(135deg, #fdfcfb, #e2d1c3);
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .box {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 18px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #444;
        }
        input {
            padding: 12px;
            width: 92%;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        button {
            padding: 12px;
            width: 100%;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>🔐 Reset Password</h2>

    <?php if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) { ?>
        <div class="alert error">OTP verification required. Please verify OTP first.</div>
    <?php } elseif (isset($error)) { ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php } elseif (isset($success)) { ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php } ?>

    <?php if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] && !isset($success)) { ?>
    <form method="POST">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
    <?php } ?>
</div>

</body>
</html>
