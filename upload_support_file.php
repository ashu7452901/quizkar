<?php
session_start();
include('../config.php');
$user_id = $_SESSION['user_id'] ?? 0;

if (isset($_FILES['file'])) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'support_' . time() . ".$ext";
    $path = "../uploads/support/" . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
        $url = "<img src='../uploads/support/$filename' style='max-width:100%;'>";
        $stmt = $conn->prepare("INSERT INTO support_messages (user_id, sender, message) VALUES (?, 'user', ?)");
        $stmt->bind_param("is", $user_id, $url);
        $stmt->execute();
    }
}
?>
