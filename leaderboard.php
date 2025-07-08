<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function safe_trim($val) {
    return isset($val) && $val !== null ? trim($val) : '';
}

// Get all users who placed bets
$sql = "SELECT u.username, b.user_id, b.number, b.correct_answer, b.amount 
        FROM bets b 
        JOIN users u ON u.id = b.user_id";

$result = $conn->query($sql);

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

// Sort by total_reward DESC
usort($stats, function($a, $b) {
    return $b['total_reward'] <=> $a['total_reward'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef1f7;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .back {
            text-align: center;
            margin-bottom: 15px;
            display: block;
            color: #007bff;
            text-decoration: none;
        }
        .back:hover {
            text-decoration: underline;
        }
        .gold {
            background-color: #ffd70033;
        }
        .silver {
            background-color: #c0c0c033;
        }
        .bronze {
            background-color: #cd7f3233;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back">← Back to Dashboard</a>
    <h2>🏆 Leaderboard</h2>
    <table>
        <tr>
            <th>Rank</th>
            <th>Username</th>
            <th>Total Bets</th>
            <th>Wins</th>
            <th>Reward ₹</th>
        </tr>
        <?php
        $rank = 1;
        foreach ($stats as $user):
            $class = '';
            if ($rank == 1) $class = 'gold';
            elseif ($rank == 2) $class = 'silver';
            elseif ($rank == 3) $class = 'bronze';
        ?>
        <tr class="<?= $class ?>">
            <td><?= $rank++ ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= $user['total_bets'] ?></td>
            <td><?= $user['total_wins'] ?></td>
            <td>₹<?= $user['total_reward'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
