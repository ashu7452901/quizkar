<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    echo "Please login to access support.";
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Check if support is resolved
$res = $conn->query("SELECT status FROM support_queue WHERE user_id = $user_id");
$row = $res->fetch_assoc();
$supportClosed = isset($row['status']) && $row['status'] === 'resolved';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Support</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #f0f8ff, #dbeeff);
        }
        .chat-box {
            max-width: 800px;
            height: 90vh;
            margin: 30px auto;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            background: white;
        }
        .messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        .message-block {
            margin-bottom: 15px;
        }
        .message-block.user {
            text-align: right;
        }
        .message-block.admin {
            text-align: left;
        }
        .message-text {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 20px;
            max-width: 75%;
            font-size: 15px;
            background: #e1f3ff;
        }
        .message-block.user .message-text {
            background: #cfe2ff;
        }
        small {
            font-size: 11px;
            color: #666;
            display: block;
            margin-top: 3px;
        }
        .input-area {
            display: flex;
            flex-wrap: wrap;
            padding: 10px;
            border-top: 1px solid #ddd;
            background: #fff;
            align-items: center;
        }
        .input-area input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            margin: 5px;
        }
        .input-area input[type="file"] {
            display: none;
        }
        .emoji-btn, .upload-btn, .send-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 14px;
            margin: 5px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        .send-btn:hover, .upload-btn:hover, .emoji-btn:hover {
            background: #0056b3;
        }
        .emoji-picker {
            display: none;
            position: absolute;
            bottom: 60px;
            left: 20px;
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .emoji-picker span {
            cursor: pointer;
            font-size: 20px;
            margin: 5px;
        }
        .typing-status {
            font-size: 12px;
            color: gray;
            padding-left: 16px;
            padding-bottom: 4px;
            display: none;
        }
        .closed-msg {
            text-align: center;
            color: red;
            font-size: 16px;
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            .chat-box {
                width: 95%;
                height: 92vh;
            }
        }
    </style>
</head>
<body>

<div class="chat-box">
    <div class="messages" id="chatMessages"></div>
    <div class="typing-status" id="typingStatus">👨‍💼 Admin is typing...</div>

    <?php if ($supportClosed): ?>
        <div class="closed-msg">❌ Your support session has been closed by admin.</div>
        <div style="text-align:center;">
            <button onclick="resetSupport()" style="padding: 10px 20px; background: green; color: white; border: none; border-radius: 6px; cursor: pointer;">📞 Request New Support</button>
        </div>
    <?php else: ?>
    <div class="input-area">
        <input type="text" id="messageInput" placeholder="Type your message...">
        <button class="emoji-btn" onclick="toggleEmojiPicker()">😊</button>
        <button class="upload-btn" onclick="document.getElementById('fileInput').click()">📎</button>
        <button class="send-btn" onclick="sendMessage()">Send</button>
        <input type="file" id="fileInput" accept="image/*" onchange="handleFile(this)">
    </div>
    <?php endif; ?>
</div>

<!-- Emoji Picker -->
<div class="emoji-picker" id="emojiPicker">
    <span onclick="insertEmoji('😀')">😀</span>
    <span onclick="insertEmoji('😂')">😂</span>
    <span onclick="insertEmoji('👍')">👍</span>
    <span onclick="insertEmoji('❤️')">❤️</span>
    <span onclick="insertEmoji('🙏')">🙏</span>
    <span onclick="insertEmoji('🎉')">🎉</span>
    <span onclick="insertEmoji('😢')">😢</span>
    <span onclick="insertEmoji('🔥')">🔥</span>
</div>

<script>
    function fetchMessages() {
        fetch('../support_messages.php')
            .then(res => res.text())
            .then(data => {
                const chat = document.getElementById('chatMessages');
                chat.innerHTML = data;
                chat.scrollTop = chat.scrollHeight;
            });
    }

    function checkTypingStatus() {
        fetch('../fetch_typing_status.php')
            .then(res => res.text())
            .then(data => {
                const typingBox = document.getElementById('typingStatus');
                typingBox.style.display = data.trim() === '1' ? 'block' : 'none';
            });
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const msg = input.value.trim();
        if (!msg) return;

        fetch('../send_support_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg)
        }).then(() => {
            input.value = '';
            fetchMessages();
        });
    }

    function toggleEmojiPicker() {
        const picker = document.getElementById('emojiPicker');
        picker.style.display = (picker.style.display === 'block') ? 'none' : 'block';
    }

    function insertEmoji(emoji) {
        const input = document.getElementById('messageInput');
        input.value += emoji;
        input.focus();
        document.getElementById('emojiPicker').style.display = 'none';
    }

    function handleFile(input) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("file", file);

        fetch('../upload_support_file.php', {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(fileUrl => {
            const imageTag = `<img src="${fileUrl}" style="max-width:100%; max-height:200px;">`;
            fetch('../send_support_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message=' + encodeURIComponent(imageTag)
            }).then(() => {
                fetchMessages();
            });
        });
    }

    function resetSupport() {
        fetch('../reset_support.php')
            .then(res => res.text())
            .then(data => {
                alert(data);
                location.reload();
            });
    }

    setInterval(() => {
        fetchMessages();
        checkTypingStatus();
    }, 3000);

    window.onload = () => {
        fetchMessages();
        checkTypingStatus();
    };
</script>
</body>
</html>
