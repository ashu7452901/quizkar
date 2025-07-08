<?php
session_start();
include('../config.php'); // your DB connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);

    if ($password != $confirm_password) {
        echo "❌ Passwords do not match!";
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Store user data + OTP temporarily in session
    $_SESSION['reg_name'] = $name;
    $_SESSION['reg_mobile'] = $mobile;
    $_SESSION['reg_email'] = $email;
    $_SESSION['reg_password'] = $password;
    $_SESSION['reg_otp'] = $otp;

    // ✅ Send OTP via Fast2SMS
    $fields = array(
        "sender_id" => "FSTSMS",
        "message" => "Your QuizKar OTP is $otp. Do not share it with anyone.",
        "language" => "english",
        "route" => "p",
        "numbers" => $mobile,
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($fields),
        CURLOPT_HTTPHEADER => array(
            "authorization: 2gbX9KZzNOob3MSDbe2gbGc3qDbMF1aIC9Gf5GqOSLWqcVzjQzdLbAmnceBl
",
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "❌ OTP sending failed: " . $err;
    } else {
        // Redirect to OTP verification page
        header("Location: verify_otp.php");
        exit();
    }
}
?>
