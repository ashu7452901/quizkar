<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    echo "❌ Missing user ID.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove existing entry if any
$conn->query("DELETE FROM support_queue WHERE user_id = $user_id");

// Add new support request
$conn->query("INSERT INTO support_queue (user_id, status) VALUES ($user_id, 'waiting')");

echo "✅ Support request sent again!";
?>
