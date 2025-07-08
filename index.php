<?php
// Optional: Start session if needed
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome to Quizkar</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      text-align: center;
      padding-top: 100px;
    }
    h1 {
      color: #333;
    }
    .btn {
      display: inline-block;
      padding: 12px 24px;
      margin: 10px;
      font-size: 18px;
      background-color: #3498db;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
    }
    .btn:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>

  <h1>Welcome to Quizkar!</h1>
  <p>Select an option to continue:</p>

  <div>
    <a href="login.php" class="btn">Login</a>
    <a href="register.php" class="btn">Register</a>
    <a href="dashboard.php" class="btn">Dashboard</a>
  </div>

</body>
</html>
