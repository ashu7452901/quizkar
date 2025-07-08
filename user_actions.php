<?php
session_start();
include("../config.php"); // ✅ adjust the path as needed

if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo "Email or Password not provided.";
        exit();
    }

    $email = mysqli_real_escape_string($conn, $email);
    $password = md5($password);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password' AND status='active'");

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        echo "Login successful. <a href='dashboard.php'>Go to Dashboard</a>";
        // header("Location: dashboard.php"); // Uncomment if dashboard page is ready
    } else {
        echo "Invalid login details or account not active.";
    }
}
?>
