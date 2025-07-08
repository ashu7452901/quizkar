<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

date_default_timezone_set('Asia/Kolkata');

// Session time calculation - 45 seconds
$currentTime = time();
$session_start_timestamp = $currentTime - ($currentTime % 45);
$session_end_timestamp = $session_start_timestamp + 45;
$session_time_str = date('H:i:s', $session_start_timestamp) . " - " . date('H:i:s', $session_end_timestamp);

// Server time
$server_time = date('Y-m-d H:i:s');

// Count total questions
$total_q_result = $conn->query("SELECT COUNT(*) AS total FROM questions");
$total_q_row = $total_q_result->fetch_assoc();
$total_questions = $total_q_row['total'];

$question_text = '';
$question_id = 0;

if ($total_questions > 0) {
    $session_number = floor(time() / 45);
    $question_index = $session_number % $total_questions;

    $q = $conn->prepare("SELECT * FROM questions LIMIT 1 OFFSET ?");
    $q->bind_param("i", $question_index);
    $q->execute();
    $qResult = $q->get_result();

    if ($qResult && $qResult->num_rows > 0) {
        $questionData = $qResult->fetch_assoc();
        $question_id = $questionData['id'];
        $question_text = $questionData['question_text'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Play Game - QuizKar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #dbeafe, #ffffff);
            padding: 40px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
            margin-bottom: 10px;
        }
        .server-time, .countdown-time {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #6f42c1;
            margin-bottom: 10px;
            background: #f0f0ff;
            padding: 8px 16px;
            display: inline-block;
            border-radius: 8px;
            box-shadow: 0 0 6px rgba(0,0,0,0.05);
        }
        .question {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 500;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            margin-top: 20px;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #0056b3;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .info {
            text-align: center;
            color: #888;
            margin-top: 20px;
        }
    </style>
    <script>
        const serverTime = new Date("<?= $server_time ?>");
        const sessionEnd = <?= $session_end_timestamp ?> * 1000;

        function updateTime() {
            serverTime.setSeconds(serverTime.getSeconds() + 1);
            const timeString = serverTime.toLocaleTimeString('en-IN', { hour12: true });
            document.getElementById("serverTime").textContent = "⏱️ Server Time: " + timeString;

            const now = new Date().getTime();
            const remaining = Math.floor((sessionEnd - now) / 1000);
            const countdownText = remaining > 0 ? `⏳ Session ends in: 00:${remaining < 10 ? '0' + remaining : remaining}` : '⏳ Session changing...';
            document.getElementById("countdown").textContent = countdownText;

            if (remaining <= 0) {
                location.reload();
            }
        }
        setInterval(updateTime, 1000);
        window.onload = updateTime;
    </script>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    <h2>Play Game - <?= $session_time_str ?></h2>
    <div class="server-time" id="serverTime">⏱️ Server Time: --:--:--</div>
    <div class="countdown-time" id="countdown">⏳ Session ends in: 00:45</div>

    <?php if ($question_text): ?>
        <form action="submit_bet.php" method="post">
            <div class="question"><?= htmlspecialchars($question_text) ?></div>
            <input type="hidden" name="question_id" value="<?= $question_id ?>">
            <input type="hidden" name="session_time" value="<?= $session_time_str ?>">

            <label>Your Answer</label>
            <input type="text" name="selected_answer" required>

            <label>Bet Amount (Min ₹10)</label>
            <input type="number" name="amount" min="10" required>

            <input type="submit" value="Submit Bet">
        </form>
    <?php else: ?>
        <p class="info">❌ No questions found in the database.</p>
    <?php endif; ?>
</div>
</body>
</html>
