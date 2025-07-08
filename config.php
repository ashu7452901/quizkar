<?php
// Show PHP errors (during development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection
$host = 'db4free.net';
$user = 'quizkar34';
$pass = 'quizkar1834';
$db   = 'quizkar34';

$conn = mysqli_connect($host, $user, $pass, $db);

// Check
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
