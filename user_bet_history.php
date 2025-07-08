<?php
include("../config.php");
session_start();
$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn, "
    SELECT g.guessed_number, g.amount, s.session_time, s.result_number
    FROM guesses g
    JOIN game_sessions s ON g.session_id = s.id
    WHERE g.user_id = $user_id
    ORDER BY s.session_time DESC
");

echo "<h3>Bet History</h3>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "Session: " . $row['session_time'] .
         " | Bet: " . $row['guessed_number'] .
         " | Amount: ₹" . $row['amount'] .
         " | Result: " . ($row['result_number'] ?? 'Pending') . "<br>";
}
?>
