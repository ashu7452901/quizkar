<?php
include("../config.php");
$results = mysqli_query($conn, "SELECT * FROM game_results ORDER BY session_time DESC");
?>

<h2>Game Results</h2>
<table border="1">
    <tr>
        <th>Session Time</th>
        <th>Result</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($results)) { ?>
        <tr>
            <td><?php echo date("d M Y, h A", strtotime($row['session_time'])); ?></td>
            <td><?php echo $row['result_number']; ?></td>
        </tr>
    <?php } ?>
</table>
