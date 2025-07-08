<?php
include("../config.php");

$current_session_q = mysqli_query($conn, "SELECT id FROM game_sessions ORDER BY id DESC LIMIT 1");
$current_session = mysqli_fetch_assoc($current_session_q)['id'];

$query = mysqli_query($conn, "SELECT guessed_number, SUM(amount) as total FROM guesses WHERE session_id=$current_session GROUP BY guessed_number ORDER BY guessed_number");
?>

<h2>Bet Pool (Current Session)</h2>
<table border="1">
    <tr>
        <th>Number</th>
        <th>Total Bet (₹)</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($query)): ?>
    <tr>
        <td><?= str_pad($row['guessed_number'], 2, '0', STR_PAD_LEFT) ?></td>
        <td><?= $row['total'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
