<?php
include("../config.php");

// Get current session based on current hour
$current_time = date("Y-m-d H:00:00");
$query = mysqli_query($conn, "SELECT * FROM game_sessions WHERE session_time = '$current_time' LIMIT 1");
$session = mysqli_fetch_assoc($query);

// Countdown logic
$end_time = strtotime($current_time) + 3600; // +1 hour
$remaining = $end_time - time();
?>

<div>
    <h3>Current Session Info</h3>
    <p>Session Time: <?= date("h A", strtotime($current_time)) ?></p>
    <p>Session Ends In: <span id="timer"></span></p>
</div>

<script>
function startCountdown(duration, display) {
    var timer = duration, minutes, seconds;
    setInterval(function () {
        minutes = parseInt(timer / 60);
        seconds = parseInt(timer % 60);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 
