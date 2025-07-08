<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$ref_result = $query->get_result();
$ref_code = $ref_result->fetch_assoc()['referral_code'];

$referrals = $conn->query("
    SELECT u.username, u.created_at,
        (SELECT COUNT(*) FROM deposits WHERE user_id = u.id AND status = 'Approved') as deposits
    FROM users u
    WHERE u.referred_by = '$ref_code'
    ORDER BY u.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Referral History</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #007bff; color: white; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>👥 Your Referral History</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Joined On</th>
                <th>Approved Deposits</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($referrals->num_rows > 0): while ($row = $referrals->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td><?= $row['deposits'] ?></td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="3">No referrals yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
