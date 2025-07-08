<?php
session_start();
include("../config.php");

$user_id = $_SESSION['user_id'] ?? 0;
$numbers = $_POST['number'] ?? [];
$amounts = $_POST['amount'] ?? [];
$session_id = $_POST['session_id'] ?? 0;

if (!$user_id || !$session_id) {
    echo "Invalid session or user.";
    exit;
}

$total_bet = 0;
$bets = [];

for ($i = 0; $i < count($numbers); $i++) {
    $num = $numbers[$i];
    $amt = (float)$amounts[$i];

    if ($amt >= 10) {
        $total_bet += $amt;
        $bets[] = ['number' => $num, 'amount' => $amt];
    }
}

// Check balance
$wallet = mysqli_fetch_assoc(mysqli_query($conn, "SELECT wallet FROM users WHERE id=$user_id"));
if (!$wallet || $wallet['wallet'] < $total_bet) {
    echo "❌ Insufficient wallet balance. Required ₹$total_bet";
    exit;
}

// Deduct wallet
mysqli_query($conn, "UPDATE users SET wallet = wallet - $total_bet WHERE id=$user_id");

// Insert all valid bets
foreach ($bets as $bet) {
    $num = $bet['number'];
    $amt = $bet['amount'];
    mysqli_query($conn, "INSERT INTO guesses (user_id, number, amount, session_id, created_at)
        VALUES ($user_id, '$num', $amt, $session_id, NOW())");
}

echo "✅ All valid bets placed successfully. <a href='play_game.php'>Back</a>";
