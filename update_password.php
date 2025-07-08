<?php
include("../config.php");

if (isset($_POST['update'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new_password === $confirm) {
        $md5pass = md5($new_password);
        mysqli_query($conn, "UPDATE users SET password='$md5pass', otp=NULL WHERE email='$email'");
        echo "Password updated. <a href='login.php'>Login Now</a>";
    } else {
        echo "Passwords do not match.";
    }
}
?>
