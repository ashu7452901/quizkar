<?php
include('../config.php');

function safe_trim($val) {
    return isset($val) && $val !== null ? trim($val) : '';
}

$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

$sql = "SELECT u.username, b.user_id, b.number, b.correct_answer, b.amount 
        FROM bets b 
        JOIN users u ON u.id = b.user_id
        WHERE b.bet_time >= ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $seven_days_ago);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];

while ($row = $result->fetch_assoc()) {
    $uid = $row['user_id'];
    $username = $row['username'];
    $number = safe_trim($row['number']);
    $correct = safe_trim($row['correct_answer']);
    $amount = $row['amount'];

    $isWin = strtolower($number) === strtolower($correct);
    $reward = $isWin ? ($amount * 8) : 0;

    if (!isset($stats[$uid])) {
        $stats[$uid] = [
            'username' => $username,
            'total_bets' => 0,
            'total_wins' => 0,
            'total_reward' => 0
        ];
    }

    $stats[$uid]['total_bets']++;
    if ($isWin) $stats[$uid]['total_wins']++;
    $stats[$uid]['total_reward'] += $reward;
}

usort($stats, function($a, $b) {
    return $b['total_reward'] <=> $a['total_reward'];
});

header('Content-Type: application/json');
echo json_encode(array_slice($stats, 0, 10)); // top 10
