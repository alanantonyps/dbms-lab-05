<?php
include 'db.php';

$sql = "SELECT p.participant_id, p.name AS participant_name, p.college, e.event_name, e.venue, r.reg_date, r.team_name 
        FROM registration r
        JOIN participant p ON r.participant_id = p.participant_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.reg_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Report</title>
    <link rel="stylesheet" href="report.css">
</head>
<body>

    <h2>College Fest Registration Report</h2>

    <a href="register.php">&larr; Back to Registration Form</a>
    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Participant Name</th>
                <th>College</th>
                <th>Event Name</th>
                <th>Venue</th>
                <th>Team Name</th>
                <th>Registration Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['participant_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['college']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['event_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['venue']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['team_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['reg_date']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center;'>No registrations found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>
</html>