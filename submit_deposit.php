<?php
include("../config.php");
session_start();

if (isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $utr = $_POST['utr'];

    $uploadDir = "../uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $screenshot = $_FILES['screenshot'];
    $fileName = time() . "_" . basename($screenshot['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($screenshot['tmp_name'], $targetPath)) {
        $sql = "INSERT INTO deposit_requests (user_id, amount, utr, screenshot, status, created_at)
                VALUES ('$user_id', '$amount', '$utr', '$fileName', 'pending', NOW())";
        mysqli_query($conn, $sql);
        echo "✅ Deposit request submitted! Await admin approval.";
    } else {
        echo "❌ Failed to upload screenshot.";
    }
}
?>
