<?php
include("../config.php");

if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];
    $otp = rand(1000, 9999);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE email='$email'");
        
        // Replace mail() with real SMTP logic in production
        mail($email, "Password Reset OTP", "Your OTP is: $otp");

        echo "OTP sent to your email. <a href='verify_reset_otp.php?email=$email'>Verify OTP</a>";
    } else {
        echo "Email not found.";
    }
}
?>
