<?php
// event_register.php - Register Student for Campus Event (Table: registration)
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "Event Registration";
$errorMsg = "";
$successMsg = "";

// Fetch all events for the dropdown
$eventsDropdown = [];
$evQuery = $conn->query("SELECT * FROM `events` ORDER BY `Date` ASC");
if ($evQuery) {
    while ($row = $evQuery->fetch_assoc()) {
        $eventsDropdown[] = $row;
    }
}

// Pre-fill parameters from session (Student ID & Name fixed to registration/login values)
$selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$student_name = $_SESSION['student_name'] ?? "";
$entered_student_id = $_SESSION['student_id'] ?? $_SESSION['username'] ?? (isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : "");
$event_name = "";
$event_date = "";
$event_venue = "";

// If event_id is given, pre-populate event details
if ($selectedEventId > 0) {
    foreach ($eventsDropdown as $ev) {
        if ((int)$ev['ID'] === $selectedEventId) {
            $event_name = $ev['Name'];
            $event_date = $ev['Date'];
            $event_venue = $ev['Venue'];
            break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postEventId = (int)($_POST["event_id"] ?? 0);
    
    // Student ID and Student Name are strictly fixed from registration/login session - cannot be changed by anyone
    if ($isLoggedIn) {
        $student_name = $_SESSION['student_name'];
        $entered_student_id = $_SESSION['student_id'] ?? $_SESSION['username'] ?? (string)$_SESSION['user_id'];
    } else {
        $student_name = trim($_POST["student_name"] ?? "");
        $entered_student_id = trim($_POST["student_id"] ?? "");
    }

    // Find the event chosen
    $chosenEvent = null;
    foreach ($eventsDropdown as $ev) {
        if ((int)$ev['ID'] === $postEventId) {
            $chosenEvent = $ev;
            break;
        }
    }

    if (!$chosenEvent) {
        $errorMsg = "Please choose a valid campus event from the list.";
    } elseif ($student_name === "") {
        $errorMsg = "Student name is required.";
    } elseif ($entered_student_id === "") {
        $errorMsg = "Please enter a valid Student ID (e.g. D26CE169).";
    } elseif (strlen($entered_student_id) < 2 || strlen($entered_student_id) > 50) {
        $errorMsg = "Student ID must be between 2 and 50 characters.";
    } else {
        $event_name = $chosenEvent['Name'];
        $event_date = $chosenEvent['Date'];
        $event_venue = $chosenEvent['Venue'];
        $event_id = (int)$chosenEvent['ID'];

        // Enforce Requirement: 1 student can only register one time in each event!
        // Check BOTH student_id AND Student_Name for this specific event
        $dupIdStmt = $conn->prepare("SELECT ID, Student_Name, Event_Name, student_id FROM `registration` WHERE (`student_id` = ? OR `Student_Name` = ?) AND (`event_id` = ? OR `Event_Name` = ?) LIMIT 1");
        if ($dupIdStmt) {
            $dupIdStmt->bind_param("ssis", $entered_student_id, $student_name, $event_id, $event_name);
            $dupIdStmt->execute();
            $dupIdRes = $dupIdStmt->get_result();

            if ($dupIdRes->num_rows > 0) {
                $existing = $dupIdRes->fetch_assoc();
                $errorMsg = "Registration Failed: Student '" . htmlspecialchars($student_name) . "' (ID: " . htmlspecialchars($entered_student_id) . ") is already registered for '" . htmlspecialchars($existing['Event_Name']) . "'. Each student can only register one time in each event!";
                $dupIdStmt->close();
            } else {
                $dupIdStmt->close();

                // Insert into `registration` table with VARCHAR student_id (sssssi)
                $insertSql = "INSERT INTO `registration` (`Student_Name`, `Event_Name`, `Date`, `Venue`, `student_id`, `event_id`) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);

                if (!$insertStmt) {
                    $errorMsg = "Database error: " . $conn->error;
                } else {
                    $insertStmt->bind_param("sssssi", $student_name, $event_name, $event_date, $event_venue, $entered_student_id, $event_id);
                    if ($insertStmt->execute()) {
                        $regId = $insertStmt->insert_id;
                        $successMsg = "Success! Registration #" . $regId . " confirmed for Student ID '" . htmlspecialchars($entered_student_id) . "' (" . htmlspecialchars($student_name) . ") for the event '" . htmlspecialchars($event_name) . "'.";
                    } else {
                        if ($insertStmt->errno == 1062) {
                            $errorMsg = "Registration Failed: This student is already registered for this event. Each student can only register once per event.";
                        } else {
                            $errorMsg = "Failed to record registration: " . $insertStmt->error;
                        }
                    }
                    $insertStmt->close();
                }
            }
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header" style="justify-content: center; text-align: center;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-ticket" style="color:var(--primary);"></i> Campus Event Registration</h1>
        <p class="page-subtitle">Reserve your seat for college workshops, tech fests, and competitions. Table: <code>registration</code></p>
    </div>
</div>

<div class="form-container">

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:1.2rem;"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check" style="font-size:1.3rem;"></i>
            <div>
                <strong>Registration Confirmed!</strong><br>
                <?= $successMsg ?>
                <div style="margin-top:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="registrations.php" class="btn btn-sm btn-success"><i class="fa-solid fa-list-check"></i> View Registrations Directory</a>
                    <a href="events.php" class="btn btn-sm btn-outline"><i class="fa-solid fa-calendar"></i> Explore More Events</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="event_register.php" method="POST" id="eventRegForm">

        <!-- Select Event -->
        <div class="form-group">
            <label class="form-label" for="event_id">
                <i class="fa-solid fa-calendar-star"></i> Select Campus Event <span class="required">*</span>
            </label>
            <select class="form-control" id="event_id" name="event_id" required onchange="updateEventDetails()">
                <option value="">-- Choose an Event --</option>
                <?php foreach ($eventsDropdown as $ev): ?>
                    <option 
                        value="<?= (int)$ev['ID'] ?>"
                        data-venue="<?= htmlspecialchars($ev['Venue']) ?>"
                        data-date="<?= htmlspecialchars($ev['Date']) ?>"
                        data-time="<?= date('h:i A', strtotime($ev['Start_Time'])) ?> - <?= date('h:i A', strtotime($ev['End_Time'])) ?>"
                        <?= ($selectedEventId === (int)$ev['ID']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($ev['Name']) ?> (<?= date('M d, Y', strtotime($ev['Date'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($eventsDropdown)): ?>
                <div class="form-hint" style="color:var(--danger);">No events available yet. <a href="add_event.php">Create an event first</a>.</div>
            <?php endif; ?>
        </div>

        <!-- Event Details Preview Box -->
        <div id="eventDetailsCard" class="card" style="background:#f8fafc; margin-bottom:1.25rem; display:none; padding:1rem;">
            <h4 style="font-size:0.95rem; color:var(--dark); margin-bottom:0.4rem;" id="previewTitle">Event Details</h4>
            <div style="font-size:0.85rem; color:var(--dark-muted); display:flex; flex-direction:column; gap:0.3rem;">
                <div><i class="fa-regular fa-calendar" style="color:var(--primary); width:18px;"></i> <strong>Date:</strong> <span id="previewDate">-</span></div>
                <div><i class="fa-solid fa-location-dot" style="color:var(--danger); width:18px;"></i> <strong>Venue:</strong> <span id="previewVenue">-</span></div>
                <div><i class="fa-regular fa-clock" style="color:var(--secondary); width:18px;"></i> <strong>Time:</strong> <span id="previewTime">-</span></div>
            </div>
        </div>

        <!-- Student ID (Fixed & Locked to Registration/Login) -->
        <div class="form-group">
            <label class="form-label" for="student_id">
                <i class="fa-solid fa-id-badge"></i> Student ID / Roll No <span class="badge badge-success"><i class="fa-solid fa-lock"></i> Fixed from Login/Registration</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="student_id" 
                name="student_id" 
                value="<?= htmlspecialchars($entered_student_id) ?>" 
                readonly 
                style="background:#f1f5f9; cursor:not-allowed; font-weight:700; color:var(--dark);"
            >
            <div class="form-hint"><i class="fa-solid fa-shield-halved" style="color:var(--success);"></i> Fixed Student ID: <strong><?= htmlspecialchars($entered_student_id) ?></strong>. This is your permanent Student ID from registration/login and cannot be changed.</div>
        </div>

        <!-- Student Name (Fixed & Locked to Registration/Login) -->
        <div class="form-group">
            <label class="form-label" for="student_name">
                <i class="fa-solid fa-user"></i> Student Name <span class="badge badge-success"><i class="fa-solid fa-lock"></i> Fixed from Login/Registration</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="student_name" 
                name="student_name" 
                value="<?= htmlspecialchars($student_name) ?>" 
                readonly 
                style="background:#f1f5f9; cursor:not-allowed; font-weight:700; color:var(--dark);"
            >
            <div class="form-hint"><i class="fa-solid fa-shield-halved" style="color:var(--success);"></i> Fixed Student Name: <strong><?= htmlspecialchars($student_name) ?></strong>. Cannot be changed.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.5rem;">
            <i class="fa-solid fa-ticket"></i> Confirm Event Registration
        </button>

        <div style="text-align:center; margin-top:1.25rem;">
            <a href="registrations.php" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-list"></i> View All Current Registrations
            </a>
        </div>
    </form>
</div>

<script>
function updateEventDetails() {
    const select = document.getElementById('event_id');
    const card = document.getElementById('eventDetailsCard');
    const selectedOpt = select.options[select.selectedIndex];

    if (select.value) {
        document.getElementById('previewTitle').textContent = selectedOpt.text;
        document.getElementById('previewDate').textContent = selectedOpt.getAttribute('data-date') || '-';
        document.getElementById('previewVenue').textContent = selectedOpt.getAttribute('data-venue') || '-';
        document.getElementById('previewTime').textContent = selectedOpt.getAttribute('data-time') || '-';
        card.style.display = 'block';
    } else {
        card.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateEventDetails();
});
</script>

<?php require_once __DIR__ . "/footer.php"; ?>
