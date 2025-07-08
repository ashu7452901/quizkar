<?php
session_start();
include('../config.php'); // 🔧 adjust path as needed

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Answer Result History</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        h3 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .pending {
            color: orange;
            font-weight: bold;
        }

        .win {
            color: green;
            font-weight: bold;
        }

        .lost {
            color: red;
            font-weight: bold;
        }

        .gray {
            color: gray;
        }
    </style>
</head>
<body>

<h3>📊 Your Answer Result History</h3>

<table>
    <tr>
        <th>Session</th>
        <th>Question ID</th>
        <th>Your Answer</th>
        <th>Correct Answer</th>
        <th>Status</th>
    </tr>

    <?php
    $query = $conn->prepare("
        SELECT 
            g.session_time, 
            q.question_id, 
            g.answer AS user_answer, 
            q.correct_answer
        FROM guesses g
        LEFT JOIN game_results gr ON g.session_time = gr.session_time
        LEFT JOIN questions q ON gr.question_id = q.id
        WHERE g.user_id = ?
        ORDER BY g.session_time DESC
    ");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $session = htmlspecialchars($row['session_time']);
            $questionId = htmlspecialchars($row['question_id'] ?? 'N/A');
            $userAnswer = htmlspecialchars($row['user_answer']);
            $correctAnswer = $row['correct_answer'];

            if (empty($correctAnswer)) {
                $correctDisplay = "<span class='gray'>Not Declared</span>";
                $statusDisplay = "<span class='pending'>PENDING</span>";
            } else {
                $correctDisplay = "<span class='win'>" . htmlspecialchars($correctAnswer) . "</span>";

                if (strtolower($userAnswer) == strtolower($correctAnswer)) {
                    $statusDisplay = "<span class='win'>WIN</span>";
                } else {
                    $statusDisplay = "<span class='lost'>LOST</span>";
                }
            }

            echo "<tr>
                <td>$session</td>
                <td>$questionId</td>
                <td>$userAnswer</td>
                <td>$correctDisplay</td>
                <td>$statusDisplay</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No answer history found.</td></tr>";
    }
    ?>
</table>

</body>
</html>
