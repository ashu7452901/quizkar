<?php
$host = 'db4free.net';
$user = 'quizkar34';
$pass = 'quizkar1834';
$dbname = 'quizKar34'; // yahi aapke database ka naam hona chahiye

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
