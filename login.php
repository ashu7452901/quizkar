<?php
session_start();
('../config.php'); // ✅ fix kiya path

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email_or_mobile = $_POST['email_or_mobile'];
    $password = md5($_POST['password']); // Assuming password is stored in md5

    // Prepare query
    $stmt = $conn->prepare("SELECT * FROM users WHERE (email = ? OR mobile = ?) AND password = ?");
    $stmt->bind_param("sss", $email_or_mobile, $email_or_mobile, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // make sure this column exists
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email/mobile or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin: 10px 0;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }
        button {
            padding: 12px;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .login-link {
            margin-top: 15px;
            text-align: center;
        }
        .login-link a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>User Login</h2>

    <?php if (!empty($error)) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="">
        <input type="text" name="email_or_mobile" placeholder="Email or Mobile" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>

        <div class="login-link">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </form>

    <div class="login-link">
        New user? <a href="register.php">Register Here</a>
    </div>
</div>

</body>
</html>
