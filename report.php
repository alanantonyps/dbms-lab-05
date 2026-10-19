<?php
include 'db.php';

$sql = "SELECT p.participant_id, p.name AS participant_name, p.college, e.event_name, e.venue, r.reg_date, r.team_name 
        FROM registration r
        JOIN participant p ON r.participant_id = p.participant_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.reg_id DESC";

$result = $conn->query($sql);

// Collect rows once so we can compute summary stats and reuse them in the table.
$rows = [];
$unique_participants = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
        $unique_participants[$row['participant_id']] = true;
    }
}

$total = count($rows);
$participant_count = count($unique_participants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Report · College Fest</title>
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
            --surface: #ffffff;
            --line: #d5d8ee;
            --line-soft: #e6e8f5;
            --ok-bg: #e4f6ea;
            --ok-text: #14683a;
        }

        * { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
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
            width: 100%;
            max-width: 1080px;
            background: var(--surface);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.6) inset,
                0 30px 60px -20px rgba(27, 31, 59, 0.28),
                0 10px 25px -10px rgba(27, 31, 59, 0.15);
        }

        /* ---------- Top banner ---------- */
        .banner {
            position: relative;
            padding: 36px 40px 32px;
            background: linear-gradient(160deg, #23284f 0%, var(--ink) 55%, #141733 100%);
            color: #fff;
            overflow: hidden;
            isolation: isolate;
        }

        .banner::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 18px 18px;
            opacity: 0.5;
            z-index: -1;
        }

        .banner .glow {
            position: absolute;
            width: 240px;
            height: 240px;
            right: -90px;
            bottom: -110px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #ffd166, var(--marigold) 55%, var(--marigold-dark) 100%);
            z-index: -1;
            box-shadow: 0 0 70px rgba(255, 183, 3, 0.32);
        }

        .banner .ring {
            position: absolute;
            width: 110px;
            height: 110px;
            right: 40px;
            bottom: 40px;
            border-radius: 50%;
            border: 14px solid var(--violet);
            z-index: -1;
            opacity: 0.9;
        }

        .banner-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--marigold);
            margin-bottom: 14px;
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
            font-size: 2.1rem;
            line-height: 1.05;
            margin: 0 0 10px;
            letter-spacing: -0.025em;
        }

        .banner p {
            margin: 0;
            color: #c4c8ea;
            font-size: 0.95rem;
            max-width: 52ch;
        }

        .back-link {
            position: relative;
            z-index: 1;
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
            white-space: nowrap;
        }
        .back-link .arrow {
            display: inline-block;
            transition: transform 0.2s;
        }
        .back-link:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .back-link:hover .arrow { transform: translateX(-3px); }
        .back-link:focus-visible {
            outline: 3px solid var(--marigold);
            outline-offset: 3px;
        }

        /* ---------- Summary chips ---------- */
        .stats {
            position: relative;
            z-index: 1;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .stat {
            flex: 1 1 160px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 14px;
            padding: 14px 16px;
            backdrop-filter: blur(6px);
        }
        .stat .num {
            font-family: "Bricolage Grotesque", "DM Sans", Arial, sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            line-height: 1;
            color: #fff;
            letter-spacing: -0.02em;
        }
        .stat .lbl {
            display: block;
            margin-top: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #9aa0c9;
        }

        /* ---------- Table area ---------- */
        .table-wrap {
            padding: 28px 32px 32px;
        }

        .section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .section-head h2 {
            font-family: "Bricolage Grotesque", "DM Sans", Arial, sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: var(--ink);
            letter-spacing: -0.01em;
        }
        .section-head .hint {
            font-size: 0.82rem;
            color: #8c90b0;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.92rem;
        }

        caption.sr-only {
            position: absolute;
            width: 1px; height: 1px;
            padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0);
            white-space: nowrap; border: 0;
        }

        thead th {
            position: sticky;
            top: 0;
            background: #f4f5fd;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
            z-index: 1;
        }
        thead th:first-child { border-top-left-radius: 12px; }
        thead th:last-child  { border-top-right-radius: 12px; }

        tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--line-soft);
            color: var(--ink);
            vertical-align: top;
        }
        tbody tr { transition: background 0.12s; }
        tbody tr:hover td { background: #fafbff; }
        tbody tr:last-child td { border-bottom: none; }

        td.name { font-weight: 700; }
        td.mono {
            font-variant-numeric: tabular-nums;
            color: var(--ink-soft);
            white-space: nowrap;
        }
        td .sub {
            display: block;
            font-size: 0.78rem;
            color: #8c90b0;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--ok-bg);
            color: var(--ok-text);
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ---------- Empty state ---------- */
        .empty {
            text-align: center;
            padding: 48px 24px;
            border: 1.5px dashed var(--line);
            border-radius: 16px;
            background: #fafbff;
        }
        .empty .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: #eceefb;
            display: grid;
            place-items: center;
            font-size: 1.6rem;
        }
        .empty h3 {
            margin: 0 0 6px;
            font-family: "Bricolage Grotesque", "DM Sans", Arial, sans-serif;
            font-size: 1.1rem;
            color: var(--ink);
        }
        .empty p {
            margin: 0 0 18px;
            color: var(--ink-soft);
            font-size: 0.9rem;
        }
        .empty a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            background: linear-gradient(180deg, #6a4ff7 0%, var(--violet) 100%);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: background 0.15s, transform 0.08s;
            box-shadow: 0 8px 20px -8px rgba(91, 61, 245, 0.55);
        }
        .empty a:hover { background: var(--violet-dark); }
        .empty a:active { transform: translateY(1px); }

        /* ---------- Footer note ---------- */
        .foot {
            padding: 0 32px 28px;
            font-size: 0.78rem;
            color: #8c90b0;
            text-align: center;
        }

        /* ---------- Responsive: table becomes cards ---------- */
        @media (max-width: 780px) {
            body { padding: 16px; }
            .card { border-radius: 20px; }
            .banner { padding: 28px 22px 24px; }
            .banner h1 { font-size: 1.7rem; }
            .banner .glow { width: 170px; height: 170px; right: -70px; bottom: -80px; }
            .banner .ring { display: none; }
            .table-wrap { padding: 22px 18px 24px; }
            .foot { padding: 0 18px 22px; }

            thead { display: none; }
            table, tbody, tr, td { display: block; width: 100%; }

            tbody tr {
                border: 1px solid var(--line);
                border-radius: 14px;
                padding: 4px 6px;
                margin-bottom: 12px;
                background: #fff;
                box-shadow: 0 4px 10px -6px rgba(27, 31, 59, 0.12);
            }
            tbody tr:hover td { background: transparent; }

            tbody td {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 12px;
                padding: 9px 12px;
                border-bottom: 1px dashed var(--line-soft);
                text-align: right;
            }
            tbody tr td:last-child { border-bottom: none; }

            tbody td::before {
                content: attr(data-label);
                flex: none;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: #8c90b0;
                text-align: left;
            }
            td.name, td.mono { white-space: normal; }
            td .sub { text-align: right; }
        }

        @media (max-width: 420px) {
            .stats { gap: 8px; }
            .stat { flex-basis: 100%; }
            .stat .num { font-size: 1.4rem; }
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

    <header class="banner">
        <span class="glow" aria-hidden="true"></span>
        <span class="ring" aria-hidden="true"></span>

        <div class="banner-top">
            <div>
                <span class="eyebrow">College Fest · 2025</span>
                <h1>Registration Report</h1>
                <p>Every entry submitted through the registration form, newest first.</p>
            </div>

            <a href="register.php" class="back-link">
                <span class="arrow" aria-hidden="true">←</span>
                Back to registration
            </a>
        </div>

        <div class="stats" aria-label="Summary">
            <div class="stat">
                <span class="num"><?= (int) $total ?></span>
                <span class="lbl">Total registrations</span>
            </div>
            <div class="stat">
                <span class="num"><?= (int) $participant_count ?></span>
                <span class="lbl">Unique participants</span>
            </div>
        </div>
    </header>

    <main class="table-wrap">
        <div class="section-head">
            <h2>All entries</h2>
            <span class="hint"><?= $total > 0 ? 'Newest first' : 'Nothing yet' ?></span>
        </div>

        <?php if ($total > 0): ?>
            <div role="region" aria-label="Registration entries" tabindex="0" style="overflow-x:auto;">
                <table>
                    <caption class="sr-only">List of all event registrations</caption>
                    <thead>
                        <tr>
                            <th scope="col">Participant</th>
                            <th scope="col">College</th>
                            <th scope="col">Event</th>
                            <th scope="col">Venue</th>
                            <th scope="col">Team</th>
                            <th scope="col">Registered on</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td data-label="Participant" class="name">
                                    <?= htmlspecialchars($row['participant_name']) ?>
                                </td>
                                <td data-label="College">
                                    <?= htmlspecialchars($row['college']) ?>
                                </td>
                                <td data-label="Event">
                                    <span class="badge"><?= htmlspecialchars($row['event_name']) ?></span>
                                </td>
                                <td data-label="Venue">
                                    <?= htmlspecialchars($row['venue']) ?>
                                </td>
                                <td data-label="Team">
                                    <?= htmlspecialchars($row['team_name']) ?>
                                </td>
                                <td data-label="Registered on" class="mono">
                                    <time datetime="<?= htmlspecialchars($row['reg_date']) ?>">
                                        <?= htmlspecialchars(date('M j, Y', strtotime($row['reg_date']))) ?>
                                    </time>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty">
                <div class="icon" aria-hidden="true">🗂️</div>
                <h3>No registrations yet</h3>
                <p>Once participants sign up, their entries will appear here.</p>
                <a href="register.php">Go to registration form →</a>
            </div>
        <?php endif; ?>
    </main>

    <p class="foot">Report generated from the Fest database.</p>
</div>

</body>
</html>