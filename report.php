<?php
include 'db.php';

/* ============================================================
   HANDLE EDIT (POST)
   ============================================================ */
$flash = null; // ['type' => 'success'|'error', 'msg' => '...']

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'edit') {
        $reg_id         = (int)($_POST['reg_id'] ?? 0);
        $participant_id = (int)($_POST['participant_id'] ?? 0);
        $event_id       = (int)($_POST['event_id'] ?? 0);
        $reg_date       = trim($_POST['reg_date'] ?? '');
        $team_name      = trim($_POST['team_name'] ?? '');

        if ($reg_id > 0 && $participant_id > 0 && $event_id > 0 && $reg_date !== '' && $team_name !== '') {
            $stmt = mysqli_prepare($conn,
                "UPDATE registration
                 SET participant_id = ?, event_id = ?, reg_date = ?, team_name = ?
                 WHERE reg_id = ?");
            mysqli_stmt_bind_param($stmt, "iissi", $participant_id, $event_id, $reg_date, $team_name, $reg_id);
            try {
                if (mysqli_stmt_execute($stmt)) {
                    $flash = ['type' => 'success', 'msg' => 'Registration updated successfully.'];
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Could not update registration.'];
                }
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $flash = ['type' => 'error', 'msg' => 'This participant is already registered for that event.'];
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Error: ' . $e->getMessage()];
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $flash = ['type' => 'error', 'msg' => 'Please fill in all fields.'];
        }
    }

    if ($_POST['action'] === 'delete') {
        $reg_id = (int)($_POST['reg_id'] ?? 0);
        if ($reg_id > 0) {
            $stmt = mysqli_prepare($conn, "DELETE FROM registration WHERE reg_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $reg_id);
            try {
                if (mysqli_stmt_execute($stmt)) {
                    $flash = ['type' => 'success', 'msg' => 'Registration deleted.'];
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Could not delete registration.'];
                }
            } catch (mysqli_sql_exception $e) {
                $flash = ['type' => 'error', 'msg' => 'Error: ' . $e->getMessage()];
            }
            mysqli_stmt_close($stmt);
        }
    }

    if ($flash) {
        $_SESSION['flash'] = $flash;
        header('Location: report.php');
        exit();
    }
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

/* ============================================================
   FETCH REGISTRATIONS
   ============================================================ */
$sql = "SELECT r.reg_id, p.participant_id, p.name AS participant_name, p.college,
               e.event_id, e.event_name, e.venue,
               r.reg_date, r.team_name
        FROM registration r
        JOIN participant p ON r.participant_id = p.participant_id
        JOIN events e ON r.event_id = e.event_id
        ORDER BY r.reg_id DESC";

$result = $conn->query($sql);

$rows = [];
$total = 0;
if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }
    $total = count($rows);
}

/* ============================================================
   STATS
   ============================================================ */
$unique_participants = [];
$unique_events = [];
$unique_teams = [];
foreach ($rows as $r) {
    $unique_participants[$r['participant_id']] = true;
    $unique_events[$r['event_name']] = true;
    $unique_teams[$r['team_name']] = true;
}
$stat_participants = count($unique_participants);
$stat_events       = count($unique_events);
$stat_teams        = count($unique_teams);

/* ============================================================
   HELPER DATA
   ============================================================ */
$gradients = [
    'linear-gradient(135deg, #4285F4, #6ba3ff)',
    'linear-gradient(135deg, #EA4335, #ff6b5e)',
    'linear-gradient(135deg, #FBBC05, #ffd23f)',
    'linear-gradient(135deg, #34A853, #5cc777)',
    'linear-gradient(135deg, #8b5cf6, #a78bfa)',
    'linear-gradient(135deg, #06b6d4, #22d3ee)',
];

$all_participants = [];
$q1 = mysqli_query($conn, "SELECT participant_id, name, college FROM participant ORDER BY name ASC");
while ($p = mysqli_fetch_assoc($q1)) $all_participants[] = $p;

$all_events = [];
$q2 = mysqli_query($conn, "SELECT event_id, event_name, venue FROM events ORDER BY event_name ASC");
while ($ev = mysqli_fetch_assoc($q2)) $all_events[] = $ev;

function initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $ini = '';
    foreach ($parts as $p) {
        if ($p !== '') $ini .= strtoupper($p[0]);
        if (strlen($ini) >= 2) break;
    }
    return $ini ?: '?';
}

function fmt_date($d) {
    if (!$d) return '—';
    $t = strtotime($d);
    return $t ? date('M j, Y', $t) : $d;
}

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
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

    <div class="page-container">

        <!-- Header -->
        <header class="page-header">
            <div class="header-top">
                <div class="header-title">
                    <div class="header-icon" aria-hidden="true"></div>
                    <div>
                        <h1>Registration Report</h1>
                        <p class="header-sub">College Fest · All registrations at a glance</p>
                    </div>
                </div>
                <a href="register.php" class="back-link">
                    <span class="back-label">Back to Registration</span>
                </a>
            </div>
        </header>

        <!-- Stats strip -->
        <section class="stats-grid" aria-label="Summary statistics">
            <div class="stat-card stat-blue">
                <div class="stat-icon" aria-hidden="true"></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $total ?></div>
                    <div class="stat-label">Total Registrations</div>
                </div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-icon" aria-hidden="true"></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $stat_participants ?></div>
                    <div class="stat-label">Unique Participants</div>
                </div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="stat-icon" aria-hidden="true"></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $stat_events ?></div>
                    <div class="stat-label">Events Covered</div>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon" aria-hidden="true"></div>
                <div class="stat-body">
                    <div class="stat-value"><?= $stat_teams ?></div>
                    <div class="stat-label">Teams Registered</div>
                </div>
            </div>
        </section>

        <!-- Toolbar: search -->
        <div class="toolbar">
            <div class="search-wrap">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by participant, college, event, team…" autocomplete="off" aria-label="Search registrations">
                <button type="button" class="search-clear" id="searchClear" aria-label="Clear search" hidden>&times;</button>
            </div>
            <div class="result-count" id="resultCount">
                Showing <strong><?= $total ?></strong> <?= $total === 1 ? 'registration' : 'registrations' ?>
            </div>
        </div>

        <!-- Table card -->
        <div class="table-card">
            <table id="reportTable">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>College</th>
                        <th>Event</th>
                        <th>Venue</th>
                        <th>Team</th>
                        <th>Date</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total > 0): ?>
                        <?php foreach ($rows as $i => $row): ?>
                            <?php
                                $grad = $gradients[$i % count($gradients)];
                                $ini  = initials($row['participant_name']);
                            ?>
                            <tr class="data-row"
                                data-reg-id="<?= (int)$row['reg_id'] ?>"
                                data-participant-id="<?= (int)$row['participant_id'] ?>"
                                data-event-id="<?= (int)$row['event_id'] ?>"
                                data-reg-date="<?= e($row['reg_date']) ?>"
                                data-team-name="<?= e($row['team_name']) ?>"
                                data-participant-name="<?= e($row['participant_name']) ?>"
                                data-event-name="<?= e($row['event_name']) ?>">
                                <td data-label="Participant">
                                    <div class="participant-cell">
                                        <span class="avatar" style="background: <?= $grad ?>"><?= e($ini) ?></span>
                                        <span class="participant-name"><?= e($row['participant_name']) ?></span>
                                    </div>
                                </td>
                                <td data-label="College">
                                    <span class="college-name"><?= e($row['college']) ?></span>
                                </td>
                                <td data-label="Event">
                                    <span class="badge badge-event"><?= e($row['event_name']) ?></span>
                                </td>
                                <td data-label="Venue">
                                    <span class="venue-name"><?= e($row['venue']) ?></span>
                                </td>
                                <td data-label="Team">
                                    <span class="badge badge-team"><?= e($row['team_name']) ?></span>
                                </td>
                                <td data-label="Date">
                                    <span class="date-cell">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                            <path d="M16 2v4M8 2v4M3 10h18"></path>
                                        </svg>
                                        <?= e(fmt_date($row['reg_date'])) ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="td-actions">
                                    <div class="action-group">
                                        <button type="button"
                                                class="btn-action btn-edit js-edit"
                                                data-reg-id="<?= (int)$row['reg_id'] ?>"
                                                aria-label="Edit registration for <?= e($row['participant_name']) ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                            </svg>
                                            <span>Edit</span>
                                        </button>
                                        <button type="button"
                                                class="btn-action btn-delete js-delete"
                                                data-reg-id="<?= (int)$row['reg_id'] ?>"
                                                data-participant-name="<?= e($row['participant_name']) ?>"
                                                data-event-name="<?= e($row['event_name']) ?>"
                                                aria-label="Delete registration for <?= e($row['participant_name']) ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                            <path d="M3 10h18M8 2v4M16 2v4"></path>
                                            <path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"></path>
                                        </svg>
                                    </div>
                                    <h3 class="empty-title">No registrations yet</h3>
                                    <p class="empty-text">Once participants register for events, their entries will appear here.</p>
                                    <a href="register.php" class="empty-cta">Create the first registration</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ================= EDIT MODAL ================= -->
    <div class="modal-overlay" id="editModal" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
            <div class="modal-header">
                <h2 id="editTitle">Edit Registration</h2>
                <button type="button" class="modal-close" id="editClose" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="report.php" class="modal-body" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="reg_id" id="edit_reg_id">

                <div class="field">
                    <label for="edit_participant">Participant</label>
                    <select name="participant_id" id="edit_participant" required>
                        <?php foreach ($all_participants as $p): ?>
                            <option value="<?= (int)$p['participant_id'] ?>">
                                <?= e($p['name'] . ' (' . $p['college'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="edit_event">Event</label>
                    <select name="event_id" id="edit_event" required>
                        <?php foreach ($all_events as $ev): ?>
                            <option value="<?= (int)$ev['event_id'] ?>">
                                <?= e($ev['event_name'] . ' — ' . $ev['venue']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="edit_team">Team Name</label>
                    <input type="text" name="team_name" id="edit_team" required maxlength="100">
                </div>

                <div class="field">
                    <label for="edit_date">Registration Date</label>
                    <input type="date" name="reg_date" id="edit_date" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" id="editCancel">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= DELETE MODAL ================= -->
    <div class="modal-overlay" id="deleteModal" hidden>
        <div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="delTitle">
            <div class="modal-header">
                <h2 id="delTitle">Delete Registration?</h2>
                <button type="button" class="modal-close" id="delClose" aria-label="Close">&times;</button>
            </div>
            <form method="POST" action="report.php" class="modal-body" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="reg_id" id="del_reg_id">

                <p class="modal-text">
                    This will permanently remove the registration for
                    <strong id="del_summary">this participant</strong>. This action cannot be undone.
                </p>

                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" id="delCancel">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TOAST ================= -->
    <?php if ($flash): ?>
        <div class="toast toast-<?= e($flash['type']) ?>" id="toast" role="status">
            <span class="toast-dot"></span>
            <span><?= e($flash['msg']) ?></span>
        </div>
    <?php endif; ?>

    <!-- JS lives in report.js -->
    <script src="report.js" defer></script>
</body>
</html>