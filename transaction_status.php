<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];
date_default_timezone_set('Asia/Kolkata');
$today = date('Y-m-d');

// यूज़र आईडी प्राप्त करें
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// आज की डेट की ट्रांजैक्शन हिस्ट्री प्राप्त करें
$txn = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? AND DATE(created_at) = ? ORDER BY created_at DESC");
$txn->bind_param("is", $user_id, $today);
$txn->execute();
$txnResult = $txn->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📋 Transaction Status - QuizKar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #343a40;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .status-success {
            color: green;
            font-weight: bold;
        }

        .status-failed {
            color: red;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📋 Transaction Status (Today - <?= date('d M Y') ?>)</h2>
    <table>
        <tr>
            <th>Type</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date & Time</th>
        </tr>
        <?php if ($txnResult->num_rows > 0): ?>
            <?php while ($row = $txnResult->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                    <td>₹<?= number_format($row['amount'], 2) ?></td>
                    <td class="<?=
                        $row['status'] == 'success' ? 'status-success' :
                        ($row['status'] == 'failed' ? 'status-failed' : 'status-pending')
                    ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">📭 No transactions found for today.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
