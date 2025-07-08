<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id']) || empty($_POST['message'])) {
    header("Location: support.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$msg = trim($_POST['message']);

$stmt = $conn->prepare("INSERT INTO support_requests (user_id, message) VALUES (?, ?)");
$stmt->bind_param("is", $user_id, $msg);
$stmt->execute();

header("Location: support.php");
