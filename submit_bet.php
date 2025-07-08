<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Kolkata');

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 📥 Form Data
$selected_answer = trim($_POST['selected_answer']);
$amount = (int)$_POST['amount'];
$question_id = $_POST['question_id'];
$session_time = $_POST['session_time'];
$question = $_POST['question_text'];
$correct_answer = $_POST['correct_answer'];
$bet_time = date('Y-m-d H:i:s');

// ✅ Minimum ₹10 check
if ($amount < 10) {
    echo "<script>alert('❌ Minimum ₹10 required.'); window.location.href='play_game.php';</script>";
    exit();
}

// 🔁 Check if already placed bet on same question
$chk = $conn->prepare("SELECT id FROM bets WHERE user_id = ? AND question_id = ?");
$chk->bind_param("is", $user_id, $question_id);
$chk->execute();
$chkResult = $chk->get_result();

if ($chkResult->num_rows > 0) {
    echo "<script>alert('❌ You have already placed a bet on this question.'); window.location.href='bet_history.php';</script>";
    exit();
}

// 💰 Wallet Check
$check = $conn->prepare("SELECT wallet FROM users WHERE id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['wallet'] < $amount) {
    echo "<script>alert('❌ Insufficient wallet balance.'); window.location.href='play_game.php';</script>";
    exit();
}

// 💸 Deduct Amount
$update = $conn->prepare("UPDATE users SET wallet = wallet - ? WHERE id = ?");
$update->bind_param("ii", $amount, $user_id);
$update->execute();

// 🔁 Normalize Function
function normalize($str) {
    return strtolower(trim(html_entity_decode($str ?? '')));
}

// 🎯 Answer Check
$correct = normalize($correct_answer);
$user_input = normalize($selected_answer);
$is_correct = $user_input === $correct;
$status = $is_correct ? 'win' : 'lose';

// 📝 Save Bet
$stmt = $conn->prepare("INSERT INTO bets (user_id, number, amount, session_time, question_id, question_text, correct_answer, user_answer, bet_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isisssssss", $user_id, $selected_answer, $amount, $session_time, $question_id, $question, $correct_answer, $selected_answer, $bet_time, $status);
$stmt->execute();

// 🏆 Reward logic
if ($is_correct) {
    $reward = $amount * 8;
    $commission = $reward * 0.02;
    $final_reward = $reward - $commission;

    $conn->query("UPDATE users SET wallet = wallet + $final_reward WHERE id = $user_id");

    $conn->query("INSERT INTO transactions (user_id, type, amount, status, reference_id) 
                  VALUES ($user_id, 'reward', $final_reward, 'success', '$question_id')");
}

echo "<script>alert('✅ Bet placed successfully!'); window.location.href='bet_history.php';</script>";
?>
