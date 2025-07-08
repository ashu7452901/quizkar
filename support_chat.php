<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    echo "Please login to access support.";
    exit();
}
$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Support</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
        }
        .chat-box {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f4f4f4;
        }
        .messages {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            border-bottom: 1px solid #ccc;
        }
        .input-area {
            display: flex;
            border-top: 1px solid #ccc;
        }
        .input-area input {
            flex: 1;
            padding: 10px;
            border: none;
        }
        .input-area button {
            padding: 10px 20px;
            border: none;
            background: #007bff;
            color: white;
            cursor: pointer;
        }
        .message-block {
            margin-bottom: 10px;
        }
        .message-block.user { text-align: right; }
        .message-block.admin { text-align: left; }
        .message-text {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 16px;
            max-width: 70%;
            background: #d1ecf1;
        }
        .message-block.user .message-text {
            background: #cce5ff;
        }
        small {
            display: block;
            color: gray;
            font-size: 11px;
        }
    </style>
</head>
<body>
<div class="chat-box">
    <div class="messages" id="chatMessages"></div>
    <div class="input-area">
        <input type="text" id="messageInput" placeholder="Type your message...">
        <button onclick="sendMessage()">Send</button>
    </div>
</div>

<script>
    function fetchMessages() {
        fetch('support_messages.php')
            .then(res => res.text())
            .then(data => {
                const chat = document.getElementById('chatMessages');
                chat.innerHTML = data;
                chat.scrollTop = chat.scrollHeight;
            });
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const msg = input.value.trim();
        if (!msg) return;

        fetch('send_support_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=' + encodeURIComponent(msg)
        }).then(() => {
            input.value = '';
            fetchMessages();
        });
    }

    setInterval(fetchMessages, 3000);
    window.onload = fetchMessages;
</script>
</body>
</html>
