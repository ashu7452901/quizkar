<?php
include('../config.php');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Today's date
$today = date('Y-m-d');

// Delete old sessions (optional, use only if needed)
// $conn->query("DELETE FROM sessions WHERE DATE(start_time) = '$today'");

for ($i = 0; $i < 24; $i += 2) {
    $start = date('Y-m-d H:i:s', strtotime("$today $i:00:00"));
    $end = date('Y-m-d H:i:s', strtotime("$today " . ($i + 2) . ":00:00"));

    $stmt = $conn->prepare("INSERT INTO sessions (start_time, end_time, is_active) VALUES (?, ?, 1)");
    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
}

echo "✅ All 2-hour sessions inserted successfully for today.";
?>
