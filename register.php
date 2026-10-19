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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>College Fest · Event Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1b1f3b;
            --ink-soft: #4a4f73;
            --marigold: #ffb703;
            --marigold-dark: #e09f00;
            --violet: #5b3df5;
            --violet-dark: #4529d6;
            --bg: #eceefb;
            --bg-deep: #dfe2f7;
            --surface: #ffffff;
            --line: #d5d8ee;
            --line-hover: #b7bce0;
            --ok-bg: #e4f6ea;
            --ok-text: #14683a;
            --err-bg: #fde8e8;
            --err-text: #a11d1d;
        }

        * { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            background:
                radial-gradient(1200px 600px at 10% -10%, #f5f1ff 0%, transparent 60%),
                radial-gradient(1000px 500px at 110% 110%, #fff4d6 0%, transparent 55%),
                var(--bg);
            color: var(--ink);
            font-family: "DM Sans", system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            display: grid;
            grid-template-columns: 5fr 7fr;
            width: 100%;
            max-width: 880px;
            background: var(--surface);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.6) inset,
                0 30px 60px -20px rgba(27, 31, 59, 0.28),
                0 10px 25px -10px rgba(27, 31, 59, 0.15);
        }

        /* ---------- Left banner ---------- */
        .banner {
            position: relative;
            padding: 44px 36px 36px;
            background:
                linear-gradient(160deg, #23284f 0%, var(--ink) 55%, #141733 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            isolation: isolate;
        }

        /* subtle dotted texture */
        .banner::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 18px 18px;
            opacity: 0.5;
            z-index: -1;
        }

        /* marigold glow peeking from bottom-right */
        .banner .glow {
            position: absolute;
            width: 280px;
            height: 280px;
            right: -110px;
            bottom: -110px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #ffd166, var(--marigold) 55%, var(--marigold-dark) 100%);
            filter: blur(0.5px);
            z-index: -1;
            box-shadow: 0 0 80px rgba(255, 183, 3, 0.35);
        }

        /* violet ring layered above the glow */
        .banner .ring {
            position: absolute;
            width: 130px;
            height: 130px;
            right: 30px;
            bottom: 60px;
            border-radius: 50%;
            border: 16px solid var(--violet);
            z-index: -1;
            opacity: 0.9;
        }

        .banner-text { position: relative; z-index: 1; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--marigold);
            margin-bottom: 18px;
        }
        .eyebrow::before {
            content: "";
            width: 22px;
            height: 2px;
            background: var(--marigold);
            border-radius: 2px;
        }

        .banner h1 {
            font-family: "Bricolage Grotesque", "DM Sans", Arial, sans-serif;
            font-weight: 800;
            font-size: 2.5rem;
            line-height: 1.02;
            margin: 0 0 16px;
            letter-spacing: -0.025em;
        }

        .banner p {
            margin: 0;
            color: #c4c8ea;
            font-size: 0.98rem;
            max-width: 28ch;
        }

        .banner .report-link {
            position: relative;
            z-index: 1;
            align-self: flex-start;
            margin-top: 28px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
        }
        .banner .report-link .arrow {
            display: inline-block;
            transition: transform 0.2s;
        }
        .banner .report-link:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .banner .report-link:hover .arrow { transform: translateX(3px); }
        .banner .report-link:focus-visible {
            outline: 3px solid var(--marigold);
            outline-offset: 3px;
        }

        /* ---------- Form side ---------- */
        .form-side { padding: 44px 40px 36px; }

        .form-side .form-head {
            margin-bottom: 22px;
        }

        .form-side h2 {
            font-family: "Bricolage Grotesque", "DM Sans", Arial, sans-serif;
            font-size: 1.55rem;
            font-weight: 700;
            margin: 0 0 6px;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        .form-side .sub {
            margin: 0;
            color: var(--ink-soft);
            font-size: 0.92rem;
        }

        /* alerts */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 20px;
            padding: 12px 14px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.92rem;
            animation: pop 0.25s ease-out;
        }
        @keyframes pop {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert::before {
            flex: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
            color: #fff;
        }
        .success { background: var(--ok-bg); color: var(--ok-text); }
        .success::before { content: "\2713"; background: var(--ok-text); }
        .error { background: var(--err-bg); color: var(--err-text); }
        .error::before { content: "!"; background: var(--err-text); }

        /* form fields */
        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 700;
            font-size: 0.86rem;
            color: var(--ink);
            letter-spacing: 0.01em;
        }

        select, input[type="date"], input[type="text"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--line);
            border-radius: 11px;
            background: #fafbff;
            color: var(--ink);
            font: inherit;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
        }
        select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 42px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1.5l5 5 5-5' fill='none' stroke='%234a4f73' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        select:hover, input:hover { border-color: var(--line-hover); }
        select:focus, input:focus {
            outline: none;
            border-color: var(--violet);
            background-color: #fff;
            box-shadow:
                0 0 0 3px rgba(91, 61, 245, 0.14),
                0 0 0 1px var(--violet);
        }
        ::placeholder { color: #8c90b0; }

        /* invalid state — only after user interacts */
        .field-touched.is-invalid select,
        .field-touched.is-invalid input {
            border-color: var(--err-text);
            background: #fff7f7;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        button {
            width: 100%;
            margin-top: 8px;
            padding: 14px 18px;
            border: none;
            border-radius: 11px;
            background: linear-gradient(180deg, #6a4ff7 0%, var(--violet) 100%);
            color: #fff;
            font: inherit;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: background 0.15s, transform 0.08s, box-shadow 0.2s;
            box-shadow: 0 8px 20px -8px rgba(91, 61, 245, 0.55);
        }
        button:hover {
            background: linear-gradient(180deg, var(--violet) 0%, var(--violet-dark) 100%);
            box-shadow: 0 12px 24px -8px rgba(91, 61, 245, 0.6);
        }
        button:active { transform: translateY(1px); }
        button:focus-visible {
            outline: 3px solid var(--marigold);
            outline-offset: 2px;
        }

        .foot-note {
            margin-top: 18px;
            font-size: 0.78rem;
            color: #8c90b0;
            text-align: center;
        }
        .foot-note a {
            color: var(--violet);
            text-decoration: none;
            font-weight: 600;
        }
        .foot-note a:hover { text-decoration: underline; }

        /* ---------- Responsive ---------- */
        @media (max-width: 780px) {
            body { padding: 16px; align-items: flex-start; }
            .card { grid-template-columns: 1fr; border-radius: 20px; }
            .banner { padding: 32px 26px 28px; }
            .banner h1 { font-size: 2rem; }
            .banner p { max-width: none; }
            .banner .glow { width: 180px; height: 180px; right: -70px; bottom: -70px; }
            .banner .ring { display: none; }
            .banner .report-link { margin-top: 20px; }
            .form-side { padding: 30px 26px 26px; }
        }

        @media (max-width: 480px) {
            .row { grid-template-columns: 1fr; gap: 0; }
            .banner h1 { font-size: 1.7rem; }
            .form-side h2 { font-size: 1.3rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>

<div class="card">
    <aside class="banner">
        <span class="glow" aria-hidden="true"></span>
        <span class="ring" aria-hidden="true"></span>

        <div class="banner-text">
            <span class="eyebrow">College Fest · 2025</span>
            <h1>Register for the<br>big day.</h1>
            <p>Pick a participant, choose an event and add a team name to lock in your spot.</p>
        </div>

        <a href="report.php" class="report-link">
            View registration report
            <span class="arrow" aria-hidden="true">→</span>
        </a>
    </aside>

    <main class="form-side">
        <div class="form-head">
            <h2>Register for an event</h2>
            <p class="sub">All fields are required.</p>
        </div>

        <div aria-live="polite" aria-atomic="true">
            <?php if (!empty($success_msg)): ?>
                <p class="alert success"><?= htmlspecialchars($success_msg) ?></p>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <p class="alert error"><?= htmlspecialchars($error_msg) ?></p>
            <?php endif; ?>
        </div>

        <form method="POST" action="" id="regForm" novalidate>
            <div class="form-group" data-field>
                <label for="participant_id">Participant</label>
                <select name="participant_id" id="participant_id" required aria-required="true">
                    <option value="">Choose a participant</option>
                    <?php while($row = $participants->fetch_assoc()): ?>
                        <option value="<?= $row['participant_id']; ?>">
                            <?= htmlspecialchars($row['name'] . " (" . $row['college'] . ")"); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group" data-field>
                <label for="event_id">Event</label>
                <select name="event_id" id="event_id" required aria-required="true">
                    <option value="">Choose an event</option>
                    <?php while($row = $events->fetch_assoc()): ?>
                        <option value="<?= $row['event_id']; ?>">
                            <?= htmlspecialchars($row['event_name'] . " @ " . $row['venue']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="row">
                <div class="form-group" data-field>
                    <label for="team_name">Team name</label>
                    <input type="text" name="team_name" id="team_name" required
                           aria-required="true" placeholder="Team or individual name"
                           autocomplete="off">
                </div>

                <div class="form-group" data-field>
                    <label for="reg_date">Registration date</label>
                    <input type="date" name="reg_date" id="reg_date" required
                           aria-required="true" value="<?= date('Y-m-d'); ?>">
                </div>
            </div>

            <button type="submit">Register participant</button>

            <p class="foot-note">
                Need to review entries first? <a href="report.php">Open the report</a>.
            </p>
        </form>
    </main>
</div>

<script>
    // Lightweight, no-library inline validation feedback.
    // Adds .is-invalid only AFTER the user has interacted with a field.
    (function () {
        const form = document.getElementById('regForm');
        if (!form) return;

        const fields = form.querySelectorAll('[data-field]');

        fields.forEach(function (group) {
            const input = group.querySelector('select, input');
            if (!input) return;

            const mark = function () {
                if (input.value.trim() === '') {
                    group.classList.add('field-touched', 'is-invalid');
                } else {
                    group.classList.add('field-touched');
                    group.classList.remove('is-invalid');
                }
            };

            input.addEventListener('blur', mark);
            input.addEventListener('change', mark);
            input.addEventListener('input', function () {
                if (group.classList.contains('is-invalid')) mark();
            });
        });

        // Block native submission only if something is invalid,
        // so the PHP server-side validation still runs normally.
        form.addEventListener('submit', function (e) {
            let hasInvalid = false;
            fields.forEach(function (group) {
                const input = group.querySelector('select, input');
                if (input && input.value.trim() === '') {
                    group.classList.add('field-touched', 'is-invalid');
                    hasInvalid = true;
                }
            });
            if (hasInvalid) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid select, .is-invalid input');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    })();
</script>

</body>
</html>