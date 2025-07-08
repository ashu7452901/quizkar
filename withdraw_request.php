<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$wallet_balance = $user['wallet'];
$message = "";
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdraw_amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $details = $conn->real_escape_string($_POST['details']);

    $commission = $withdraw_amount * 0.02;
    $total_deduction = $withdraw_amount + $commission;

    if ($withdraw_amount < 1000) {
        $message = "⛔ Minimum withdrawal amount is ₹1000.";
    } elseif ($wallet_balance < 200 || $wallet_balance < $total_deduction) {
        $message = "⛔ You must have at least ₹200 balance and sufficient funds for withdrawal + commission.";
    } else {
        $new_balance = $wallet_balance - $total_deduction;
        $conn->query("UPDATE users SET wallet = $new_balance WHERE id = $user_id");

        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, method, details) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $withdraw_amount, $method, $details);

        if ($stmt->execute()) {
            $withdrawal_id = $conn->insert_id;
            $conn->query("INSERT INTO transactions (user_id, type, amount, status, reference_id) 
                          VALUES ($user_id, 'withdrawal', $withdraw_amount, 'pending', $withdrawal_id)");
            $message = "✅ Withdrawal request submitted successfully.";
            $isSuccess = true;
        } else {
            $message = "⛔ Error submitting request: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdraw Request</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            width: 400px;
        }
        h2 {
            text-align: center;
        }
        input, select, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
        }
        .balance {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="form-box">
    <h2>💸 Withdraw </h2>
    <div class="balance">Wallet Balance: ₹<?= number_format($wallet_balance, 2) ?></div>

    <form method="POST">
        <label>Amount (min ₹1000)</label>
        <input type="number" name="amount" required min="1000" step="10" />

        <label>Withdraw Method</label>
        <select name="method" required>
            <option value="UPI">UPI</option>
            <option value="Bank">Bank</option>
        </select>

        <label>Enter UPI ID or Bank Details</label>
        <textarea name="details" rows="3" required placeholder="e.g., UPI: user@upi or Bank: Account No, IFSC, Name"></textarea>

        <button type="submit">Submit</button>
    </form>
</div>

<?php if (!empty($message)): ?>
<script>
Swal.fire({
    icon: '<?= $isSuccess ? 'success' : 'error' ?>',
    title: '<?= $isSuccess ? 'Success!' : 'Error!' ?>',
    text: '<?= addslashes($message) ?>',
    showConfirmButton: false,
    timer: 2000
});
<?php if ($isSuccess): ?>
setTimeout(() => {
    window.location.href = 'dashboard.php';
}, 2000);
<?php endif; ?>
</script>
<?php endif; ?>

</body>
</html>
