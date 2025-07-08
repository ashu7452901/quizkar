<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

date_default_timezone_set('Asia/Kolkata');

$hour = (int)date('H');
$greeting = match (true) {
    $hour >= 5 && $hour < 12 => "Good Morning",
    $hour >= 12 && $hour < 17 => "Good Afternoon",
    $hour >= 17 && $hour < 21 => "Good Evening",
    default => "Hello"
};

// Auto Hindi Notification Once Daily
$today = date('Y-m-d');
$check = $conn->query("SELECT id FROM notifications WHERE DATE(created_at) = '$today' AND user_id IS NULL AND message LIKE '\xF0\x9F\x8C\x9E%'");
if ($check->num_rows == 0) {
    $messages = [
        "🌞 सुप्रभात! आज का दिन आपके ज्ञान की परीक्षा का है! 🧠✨\nसही प्रश्न का उत्तर दें और पाएं शानदार इनाम! 💰",
        "📚 ज्ञान ही असली शक्ति है! आज के सवाल का सही उत्तर देकर बनें विजेता! 🏆",
        "💥 हर सवाल एक मौका है — और हर जवाब आपके दिमाग की ताकत का सबूत!\nसही उत्तर दीजिए और कमाइए बना इनाम! 💸",
        "🧠 दिमाग लगाइए और जीत को अपनाइए!\nआज के सवाल का सही उत्तर दीजिए और बन जाएए हमारे टॉप खिलाड़ी! 🥇",
        "🔥 आज भी एक नया सवाल, एक नया मौका!\nतैयार हो जाइए जीतने के लिए — उत्तर दीजिए और पाएं इनाम! 🎯"
    ];
    $msg = $messages[array_rand($messages)];
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (NULL, ?)");
    $stmt->bind_param("s", $msg);
    $stmt->execute();
}

// Wallet Info
$user_result = $conn->query("SELECT wallet FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();
$wallet = $user['wallet'];

$server_time = date('Y-m-d H:i:s');

// Result check
$current_timestamp = time();
$session_id = floor($current_timestamp / 45);
$check_result = $conn->prepare("SELECT id FROM game_results WHERE session_id = ?");
$check_result->bind_param("i", $session_id);
$check_result->execute();
$result_check = $check_result->get_result();
$result_declared = $result_check->num_rows > 0;

$session_closed = false; // JS will handle it live
$disable_play = $result_declared;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap">
    <link rel="icon" href="favicon.ico">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('https://i.imgur.com/5Aqgz7o.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .container {
            max-width: 960px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            text-align: center;
        }
        .wallet, .session-time, .server-time {
            font-size: 18px;
            font-weight: bold;
            margin: 10px auto;
            padding: 12px;
            border-radius: 10px;
        }
        .wallet { background: #007bff; color: #fff; }
        .session-time { background: #ffc107; }
        .server-time { background: #ffffff88; box-shadow: 0 0 10px rgba(0,0,0,0.1); }

        .btns a {
            display: inline-block;
            margin: 8px;
            padding: 12px 20px;
            background: #28a745;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }
        .btns a.view { background: #6f42c1; }
        .btns a.disabled { background: gray; pointer-events: none; opacity: 0.6; }

        .notifications {
            background: #ffffffdd;
            padding: 15px;
            margin-top: 30px;
            border-radius: 10px;
        }
        .notification-item {
            background: linear-gradient(135deg, #e0f7fa, #ffffff);
            padding: 10px 15px;
            font-size: 15px;
            line-height: 1.6;
            color: #333;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            margin-bottom: 10px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        table.leaderboard {
            width: 100%;
            border-collapse: collapse;
        }
        table.leaderboard th, table.leaderboard td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        table.leaderboard th {
            background: #007bff;
            color: #fff;
        }

        .ref-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
            border-radius: 8px;
        }
        .ref-box button {
            margin-top: 8px;
            background: #007bff;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .live-support-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #17a2b8;
            color: white;
            padding: 12px 18px;
            border-radius: 30px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="notifications">
        <h3>📢 Notifications</h3>
        <?php
        $n = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? OR user_id IS NULL ORDER BY created_at DESC LIMIT 5");
        $n->bind_param("i", $user_id);
        $n->execute();
        $res = $n->get_result();
        $firstSound = true;
        if ($res->num_rows > 0) {
            while ($note = $res->fetch_assoc()) {
                echo "<div class='notification-item'>";
                echo nl2br(htmlspecialchars($note['message'])) . "<br><small>{$note['created_at']}</small>";
                echo "</div>";
                if ($firstSound) {
                    echo "<audio autoplay><source src='https://notificationsounds.com/notification-sounds/event-538/download/mp3' type='audio/mpeg'></audio>";
                    $firstSound = false;
                }
            }
        } else {
            echo "<div class='notification-item'>No notifications yet.</div>";
        }
        ?>
    </div>

    <h2>🎯 <?= $greeting ?>, <?= htmlspecialchars($username) ?>!</h2>
    <div class="wallet">💰 Balance: ₹<?= number_format($wallet, 2); ?></div>
    <div class="session-time">🕒 Session: --:--:-- - --:--:--</div>
    <div class="server-time" id="serverTime">⏱️ Time: --:--:--</div>
    <div class="server-time" id="countdown">⏳ Ends in: --</div>

    <div class="btns">
        <?php if (!$disable_play): ?>
            <a href="play_game.php">🎮 Play</a>
        <?php else: ?>
            <a class="disabled">🎮 Play(Closed)</a>
        <?php endif; ?>
        <a href="deposit_request.php">💳 Deposit</a>
        <a href="withdraw_request.php">🏧 Withdraw</a>
        <a href="transaction_status.php">📋 Transactions</a>
        <a href="bet_history.php" class="view">📄 Bet History</a>
        <a href="logout.php" style="background:#dc3545;">🚪 Logout</a>
    </div>

    <div class="ref-box">
        <h3>🎁 Invite & Earn ₹10</h3>
        <input type="text" id="refLink" value="https://yourdomain.com/register.php?ref=<?= $user_id ?>" readonly onclick="this.select();">
        <button onclick="copyRef()">📋 Copy Link</button>
    </div>

    <div class="notifications">
        <h3>🏆 Weekly Top Winners</h3>
        <table class="leaderboard" id="rewardLeaderboard">
            <thead><tr><th>User</th><th>Winnings</th></tr></thead>
            <tbody><tr><td colspan='2'>Loading...</td></tr></tbody>
        </table>
    </div>

    <div class="notifications">
        <h3>🏅 Top Referrers</h3>
        <table class="leaderboard" id="referralLeaderboard">
            <thead><tr><th>User</th><th>Earnings</th></tr></thead>
            <tbody><tr><td colspan='2'>Loading...</td></tr></tbody>
        </table>
    </div>

</div>

<a href="support.php" class="live-support-btn" target="_blank">💬 Live Support</a>

<script>
let serverTime = new Date("<?= $server_time ?>");

function updateTime() {
    serverTime.setSeconds(serverTime.getSeconds() + 1);

    const timeString = serverTime.toLocaleTimeString('en-IN', { hour12: true });
    document.getElementById("serverTime").textContent = "⏱️ Time: " + timeString;

    let nowUnix = Math.floor(serverTime.getTime() / 1000);
    let sessionStart = nowUnix - (nowUnix % 45);
    let sessionEnd = sessionStart + 45;

    let startStr = new Date(sessionStart * 1000).toLocaleTimeString('en-IN', { hour12: false });
    let endStr = new Date(sessionEnd * 1000).toLocaleTimeString('en-IN', { hour12: false });

    document.querySelector(".session-time").textContent = `🕒 Session: ${startStr} - ${endStr}`;

    let remaining = sessionEnd - nowUnix;
    document.getElementById("countdown").textContent = "⏳ Ends in: " + remaining + "s";
}
setInterval(updateTime, 1000);
window.onload = updateTime;

fetch('get_weekly_leaderboard.php')
    .then(res => res.json())
    .then(data => {
        const body = document.querySelector('#rewardLeaderboard tbody');
        body.innerHTML = '';
        data.forEach(user => {
            body.innerHTML += `<tr><td>${user.username.slice(0,3)}***</td><td>₹${user.total_reward}</td></tr>`;
        });
    });

fetch('get_top_referrers.php')
    .then(res => res.json())
    .then(data => {
        const body = document.querySelector('#referralLeaderboard tbody');
        body.innerHTML = '';
        data.forEach(user => {
            body.innerHTML += `<tr><td>${user.username.slice(0,3)}***</td><td>₹${user.earning}</td></tr>`;
        });
    });

function copyRef() {
    let copyText = document.getElementById("refLink");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Referral link copied!");
}
</script>
</body>
</html>
