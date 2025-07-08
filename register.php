<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
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
        .registration-box {
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
        .terms-checkbox {
            font-size: 14px;
            color: #333;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="registration-box">
    <h2>Registration</h2>
    <form method="POST" action="send_otp.php">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="mobile" placeholder="Mobile Number" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Create Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <label class="terms-checkbox">
            <input type="checkbox" required>
            I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
        </label>

        <button type="submit" name="register">Register</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>
