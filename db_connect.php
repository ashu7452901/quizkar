<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'QuizKar'; // yahi aapke database ka naam hona chahiye

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
