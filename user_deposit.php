<?php include("../config.php"); session_start(); ?>

<h2>Deposit Funds</h2>

<p><strong>Scan and Pay via UPI:</strong></p>
<img src="../images/qr_code.png" width="250" alt="QR Code"><br>
<p><strong>UPI ID:</strong> 9560994405@ptaxis</p>

<form method="POST" action="submit_deposit.php" enctype="multipart/form-data">
    <label>Enter Amount (in ₹):</label><br>
    <input type="number" name="amount" required><br><br>

    <label>Enter UTR/Transaction ID:</label><br>
    <input type="text" name="utr" required><br><br>

    <label>Upload Payment Screenshot:</label><br>
    <input type="file" name="screenshot" accept="image/*" required><br><br>

    <button type="submit" name="submit">Submit Deposit Request</button>
</form>
