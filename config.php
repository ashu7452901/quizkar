<?php
// Show PHP errors (during development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB connection
$host = 'sql.freesqldatabase.com';
$user = 'sql7788896';
$pass = 'mlVnEfKEwX';
$db   = 'sql7788896';

$conn = mysqli_connect($host, $user, $pass, $db);

// Check
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
?>
