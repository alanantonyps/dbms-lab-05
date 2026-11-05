<?php
include 'db.php';

$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $participant_id = $_POST['participant_id'];
    $event_id = $_POST['event_id'];
    $reg_date = $_POST['reg_date'];
    $team_name = trim($_POST['team_name']);

    if (!empty($participant_id) && !empty($event_id) && !empty($reg_date) && !empty($team_name)) {
        $stmt = $conn->prepare("INSERT INTO registration (participant_id, event_id, reg_date, team_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $participant_id, $event_id, $reg_date, $team_name);

        if ($stmt->execute()) {
            $success_msg = "Participant registered successfully!";
        } else {
            $error_msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

$participants = $conn->query("SELECT participant_id, name, college FROM participant");
$events = $conn->query("SELECT event_id, event_name, venue FROM events");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration</title>
</head>
<body>

    <h2>College Fest Event Registration</h2>

    <!-- Success and Error Messages -->
    <?php if (!empty($success_msg)): ?>
        <p style="color: green; font-weight: bold;"><?= htmlspecialchars($success_msg) ?></p>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error_msg) ?></p>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="">
        <label for="participant_id">Select Participant:</label><br>
        <select name="participant_id" id="participant_id" required>
            <option value="">-- Choose Participant --</option>
            <?php while($row = $participants->fetch_assoc()): ?>
                <option value="<?= $row['participant_id']; ?>">
                    <?= htmlspecialchars($row['name'] . " (" . $row['college'] . ")"); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <label for="event_id">Select Event:</label><br>
        <select name="event_id" id="event_id" required>
            <option value="">-- Choose Event --</option>
            <?php while($row = $events->fetch_assoc()): ?>
                <option value="<?= $row['event_id']; ?>">
                    <?= htmlspecialchars($row['event_name'] . " @ " . $row['venue']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <label for="team_name">Team Name:</label><br>
        <input type="text" name="team_name" id="team_name" required placeholder="Enter team or individual name">
        <br><br>

        <label for="reg_date">Registration Date:</label><br>
        <input type="date" name="reg_date" id="reg_date" required value="<?= date('Y-m-d'); ?>">
        <br><br>

        <button type="submit">Register</button>
    </form>

    <br>
    <a href="report.php">View Registration Report</a>

</body>
</html>