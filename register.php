<?php
include 'db.php';

$success_msg = isset($_GET['success']) ? "Participant registered successfully!" : "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_p_name = trim($_POST['new_p_name'] ?? '');
    $new_p_college = trim($_POST['new_p_college'] ?? '');
    $new_p_phone = trim($_POST['new_p_phone'] ?? '');
    $participant_id = $_POST['participant_id'] ?? '';

    $new_e_name = trim($_POST['new_e_name'] ?? '');
    $new_e_venue = trim($_POST['new_e_venue'] ?? '');
    $new_e_date = trim($_POST['new_e_date'] ?? '');
    $event_id = $_POST['event_id'] ?? '';

    $reg_date = $_POST['reg_date'] ?? '';
    $team_name = trim($_POST['team_name'] ?? '');

    $has_new_p = ($new_p_name !== '' || $new_p_college !== '' || $new_p_phone !== '');
    $full_new_p = ($new_p_name !== '' && $new_p_college !== '' && $new_p_phone !== '');

    // Check if user started typing a new event (name or venue), or filled the date with name/venue
    $has_new_e = ($new_e_name !== '' || $new_e_venue !== '' || ($new_e_date !== '' && ($new_e_name !== '' || $new_e_venue !== '')));
    $full_new_e = ($new_e_name !== '' && $new_e_venue !== '' && $new_e_date !== '');

    if ($has_new_p && !$full_new_p) {
        $error_msg = "Please fill in all three fields (Name, College, Phone) to add a new participant.";
    } elseif ($has_new_e && !$full_new_e) {
        $error_msg = "Please fill in all three fields (Event Name, Venue, Event Date) to add a new event.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // 1. Handle Participant (New entry OR Dropdown selection)
            if ($full_new_p) {
                $stmt_p = mysqli_prepare($conn, "INSERT INTO participant (name, college, phone) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_p, "sss", $new_p_name, $new_p_college, $new_p_phone);
                mysqli_stmt_execute($stmt_p);
                $participant_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_p);
            }

            // 2. Handle Event (New entry OR Dropdown selection)
            if ($full_new_e) {
                $stmt_e = mysqli_prepare($conn, "INSERT INTO events (event_name, venue, event_date) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_e, "sss", $new_e_name, $new_e_venue, $new_e_date);
                mysqli_stmt_execute($stmt_e);
                $event_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_e);
            }

            // 3. Handle Registration Entry
            if (!empty($participant_id) && !empty($event_id) && !empty($reg_date) && !empty($team_name)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO registration (participant_id, event_id, reg_date, team_name) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiss", $participant_id, $event_id, $reg_date, $team_name);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                mysqli_commit($conn);
                header("Location: register.php?success=1");
                exit();
            } else {
                mysqli_rollback($conn);
                $error_msg = "Please select or enter both a Participant and an Event, and fill in Team Name and Date.";
            }
        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            if ($e->getCode() === 1062) {
                $error_msg = "This participant is already registered for the selected event.";
            } else {
                $error_msg = "Error: " . $e->getMessage();
            }
        }
    }
}

$participants = mysqli_query($conn, "SELECT participant_id, name, college FROM participant ORDER BY name ASC");
$events = mysqli_query($conn, "SELECT event_id, event_name, venue FROM events ORDER BY event_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="page-container">
        <h2>College Fest Event Registration</h2>

        <!-- Success and Error Messages -->
        <?php if (!empty($success_msg)): ?>
            <p class="msg msg-success"><?= htmlspecialchars($success_msg) ?></p>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <p class="msg msg-error"><?= htmlspecialchars($error_msg) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- PARTICIPANT SECTION -->
            <div class="form-section">
                <h3>1. Select Existing Participant OR Add New</h3>

                <div class="field-group">
                    <label class="field-label">Select Existing Participant:</label>
                    <select name="participant_id">
                        <option value="">-- Choose Participant --</option>
                        <?php while ($row = mysqli_fetch_assoc($participants)): ?>
                            <option value="<?= $row['participant_id']; ?>">
                                <?= htmlspecialchars($row['name'] . " (" . $row['college'] . ")"); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <span class="sub-label">OR Enter New Participant Details:</span>
                <div class="field-group">
                    <label class="field-label">Name:</label>
                    <input type="text" name="new_p_name" placeholder="Participant Name">
                </div>
                <div class="field-group">
                    <label class="field-label">College:</label>
                    <input type="text" name="new_p_college" placeholder="College Name">
                </div>
                <div class="field-group">
                    <label class="field-label">Phone:</label>
                    <input type="text" name="new_p_phone" placeholder="Phone Number">
                </div>
            </div>

            <hr>

            <!-- EVENT SECTION -->
            <div class="form-section">
                <h3>2. Select Existing Event OR Add New</h3>

                <div class="field-group">
                    <label class="field-label">Select Existing Event:</label>
                    <select name="event_id">
                        <option value="">-- Choose Event --</option>
                        <?php while ($row = mysqli_fetch_assoc($events)): ?>
                            <option value="<?= $row['event_id']; ?>">
                                <?= htmlspecialchars($row['event_name'] . " @ " . $row['venue']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <span class="sub-label">OR Enter New Event Details:</span>
                <div class="field-group">
                    <label class="field-label">Event Name:</label>
                    <input type="text" name="new_e_name" placeholder="Event Name">
                </div>
                <div class="field-group">
                    <label class="field-label">Venue:</label>
                    <input type="text" name="new_e_venue" placeholder="Event Venue">
                </div>
                <div class="field-group">
                    <label class="field-label">Event Date:</label>
                    <input type="date" name="new_e_date">
                </div>
            </div>

            <hr>

            <!-- REGISTRATION DETAILS -->
            <div class="form-section">
                <h3>3. Registration Details</h3>

                <div class="field-group">
                    <label class="field-label">Team Name:</label>
                    <input type="text" name="team_name" required placeholder="Enter team or individual name">
                </div>

                <div class="field-group">
                    <label class="field-label">Registration Date:</label>
                    <input type="date" name="reg_date" required value="<?= date('Y-m-d'); ?>">
                </div>

                <button type="submit">Register</button>
            </div>
        </form>

        <p class="note">Note: If new participant or event details are typed, they will be saved and used instead of the dropdown.</p>
        <a class="report-link" href="report.php">View Registration Report</a>
    </div>

</body>
</html>