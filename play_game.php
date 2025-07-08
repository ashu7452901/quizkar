<?php
session_start();
include('../config.php');

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

date_default_timezone_set('Asia/Kolkata');
$server_time = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Play Game - Live Session</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #dbeafe, #ffffff);
            padding: 40px;
        }
        .container { max-width: 600px; margin: auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #007bff; margin-bottom: 10px; }
        .timer-box { text-align: center; margin-bottom: 10px; font-weight: 600; }
        .question { margin: 20px 0; font-size: 18px; font-weight: 600; }
        label { font-weight: 500; margin-top: 10px; display: block; }
        input[type="text"], input[type="number"] { width:100%; padding:10px; font-size:16px; margin-bottom:10px; }
        input[type="submit"] { width:100%; padding:12px; background-color:#007bff; border:none; color:white; font-size:16px; border-radius:6px; cursor:pointer; }
        input[type="submit"]:hover { background-color:#0056b3; }
        .back-link { display:inline-block; margin-bottom:20px; color:#007bff; text-decoration:none; }
        .back-link:hover { text-decoration:underline; }
        .already-bet { color: red; font-weight: bold; text-align: center; margin-top: 15px; }
    </style>
</head>
<body>
<div class="container">
    <a href="dashboard.php" class="back-link">←Back</a>
    <h2>Live Session</h2>
    <div class="timer-box">
        ⏱️ Time: <span id="serverTime">--:--:--</span><br>
        🆔 ID: <span id="sessionId">--</span><br>
        🕒 Start: <span id="sessionStart">--</span><br>
        🕓 End: <span id="sessionEnd">--</span><br>
        ⏳ Countdown: <span id="countdown">--</span>
    </div>
    <form id="betForm" action="submit_bet.php" method="post">
        <div class="question" id="questionBox">Loading question...</div>
        <input type="hidden" name="question_id" id="question_id" value="">
        <input type="hidden" name="session_time" id="session_time" value="">
        <input type="hidden" name="question_text" id="question_text" value="">
        <input type="hidden" name="correct_answer" id="correct_answer" value="">
        <label>Answer</label>
        <input type="text" name="selected_answer" id="selected_answer" required>
        <label>Amount (Min ₹10)</label>
        <input type="number" name="amount" id="amount" min="10" required>
        <input type="submit" id="submitBtn" value="Submit">
        <div class="already-bet" id="alreadyMessage" style="display:none;">❌ आपने पहले ही इस सवाल पर Bet कर लिया है।</div>
    </form>
</div>

<script>
    let serverTime = new Date("<?= $server_time ?>");
    let currentSessionID = null;
    const userId = <?= $user_id ?>;

    function getSessionID() {
        const seconds = Math.floor(serverTime.getTime() / 1000);
        return Math.floor(seconds / 45);
    }

    function getSessionTimestamps(sessionId) {
        const sessionStart = new Date(sessionId * 45 * 1000);
        const sessionEnd = new Date((sessionId + 1) * 45 * 1000);
        return { sessionStart, sessionEnd };
    }

    async function loadQuestion() {
        const sessionId = getSessionID();
        const session = getSessionTimestamps(sessionId);
        currentSessionID = sessionId;

        document.getElementById("sessionId").innerText = sessionId;
        document.getElementById("sessionStart").innerText = session.sessionStart.toLocaleTimeString();
        document.getElementById("sessionEnd").innerText = session.sessionEnd.toLocaleTimeString();
        document.getElementById("session_time").value = session.sessionStart.toISOString().slice(0, 19).replace('T', ' ');

        const cacheKey = "question_" + sessionId;
        if (localStorage.getItem(cacheKey)) {
            const questionData = JSON.parse(localStorage.getItem(cacheKey));
            renderQuestion(questionData);
            checkIfAlreadyBet(questionData.question_id);
            return;
        }

        try {
            const res = await fetch('api_proxy.php');
            const json = await res.json();
            const q = json.results[0];

            const engQuestion = decodeHTMLEntities(q.question);
            const hinQuestion = await translateToHindi(engQuestion);
            const correct = decodeHTMLEntities(q.correct_answer);
            const incorrect = q.incorrect_answers.map(decodeHTMLEntities);
            const allOptions = incorrect.concat(correct).sort(() => Math.random() - 0.5);

            const questionData = {
                question_en: engQuestion,
                question_hi: hinQuestion,
                correct,
                options: allOptions,
                question_id: "q_" + Math.floor(Math.random() * 1000000)
            };

            localStorage.setItem(cacheKey, JSON.stringify(questionData));
            renderQuestion(questionData);
            checkIfAlreadyBet(questionData.question_id);
        } catch (e) {
            document.getElementById("questionBox").innerText = "❌ Failed to load question.";
        }
    }

    async function checkIfAlreadyBet(questionId) {
        try {
            const res = await fetch('check_bet.php?user_id=' + userId + '&question_id=' + questionId);
            const json = await res.json();
            if (json.alreadyBet) {
                document.getElementById("submitBtn").disabled = true;
                document.getElementById("selected_answer").disabled = true;
                document.getElementById("amount").disabled = true;
                document.getElementById("alreadyMessage").style.display = "block";
            }
        } catch (e) {
            console.error('Failed to check bet:', e);
        }
    }

    async function translateToHindi(text) {
        try {
            const res = await fetch('https://api.mymemory.translated.net/get?q=' + encodeURIComponent(text) + '&langpair=en|hi');
            const data = await res.json();
            return data.responseData.translatedText;
        } catch {
            return text;
        }
    }

    function renderQuestion(data) {
        let html = `<div><strong>EN:</strong> ${data.question_en}</div><br>
                    <div><strong>HI:</strong> ${data.question_hi}</div><hr>`;
        html += data.options.map((opt,i) => `<div><b>${String.fromCharCode(65+i)}.</b> ${opt}</div>`).join("");
        document.getElementById("questionBox").innerHTML = html;

        document.getElementById("question_id").value = data.question_id;
        document.getElementById("question_text").value = data.question_en;
        document.getElementById("correct_answer").value = data.correct;
    }

    function updateClock() {
        serverTime.setSeconds(serverTime.getSeconds() + 1);
        document.getElementById("serverTime").innerText = serverTime.toLocaleTimeString();

        const now = Math.floor(serverTime.getTime() / 1000);
        const sessionEnd = (currentSessionID + 1) * 45;
        const remaining = sessionEnd - now;

        if (remaining <= 0) {
            localStorage.removeItem("question_" + currentSessionID);
            loadQuestion();
        }

        document.getElementById("countdown").innerText = remaining + "s";
    }

    function decodeHTMLEntities(text) {
        const textarea = document.createElement("textarea");
        textarea.innerHTML = text;
        return textarea.value;
    }

    loadQuestion();
    setInterval(updateClock, 1000);

    // ====== Yaha form submit hone par submit button disable karne ka code =====
    document.getElementById('betForm').addEventListener('submit', function(){
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').value = 'Submitting...';
    });
</script>
</body>
</html>
