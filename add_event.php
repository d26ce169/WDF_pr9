<?php
// add_event.php - Host / Add Campus Event (Table: events)
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "Host Campus Event";
$errorMsg = "";
$successMsg = "";

$name = "";
$venue = "";
$date = "";
$start_time = "09:00";
$end_time = "17:00";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $venue = trim($_POST["venue"] ?? "");
    $date = trim($_POST["date"] ?? "");
    $start_time = trim($_POST["start_time"] ?? "");
    $end_time = trim($_POST["end_time"] ?? "");

    // Validations
    if ($name === "" || $venue === "" || $date === "" || $start_time === "" || $end_time === "") {
        $errorMsg = "All fields are required. Please fill in all event details.";
    } elseif (strlen($name) < 3) {
        $errorMsg = "Event name must be at least 3 characters long.";
    } elseif (strlen($venue) < 3) {
        $errorMsg = "Venue must be at least 3 characters long.";
    } elseif (strtotime($end_time) <= strtotime($start_time)) {
        $errorMsg = "Event End Time must be later than the Start Time.";
    } else {
        // Insert into `events` table using MySQLi Prepared Statement
        $sql = "INSERT INTO `events` (`Name`, `Venue`, `Start_Time`, `End_Time`, `Date`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $errorMsg = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("sssss", $name, $venue, $start_time, $end_time, $date);
            if ($stmt->execute()) {
                $newEventId = $stmt->insert_id;
                $successMsg = "Event '" . htmlspecialchars($name) . "' has been created successfully (Event #" . $newEventId . ")!";
                
                // Clear form
                $name = "";
                $venue = "";
                $date = "";
            } else {
                $errorMsg = "Failed to insert event into database: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header" style="justify-content: center; text-align: center;">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-calendar-plus" style="color:var(--primary);"></i> Host / Propose Campus Event</h1>
        <p class="page-subtitle">Schedule a university seminar, workshop, hackathon, or cultural activity. Table: <code>events</code></p>
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
                <strong>Event Published!</strong><br>
                <?= $successMsg ?>
                <div style="margin-top:0.75rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <a href="events.php" class="btn btn-sm btn-success"><i class="fa-solid fa-calendar-days"></i> View in Events Explorer</a>
                    <a href="event_register.php" class="btn btn-sm btn-outline"><i class="fa-solid fa-ticket"></i> Test Registration</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form action="add_event.php" method="POST" id="addEventForm">

        <!-- Event Name -->
        <div class="form-group">
            <label class="form-label" for="name">
                <i class="fa-solid fa-heading"></i> Event Name / Title <span class="required">*</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="name" 
                name="name" 
                placeholder="e.g. AI & Cloud Computing Workshop" 
                value="<?= htmlspecialchars($name) ?>" 
                required
            >
            <div class="form-hint">At least 3 characters. Max 100 characters.</div>
        </div>

        <!-- Venue -->
        <div class="form-group">
            <label class="form-label" for="venue">
                <i class="fa-solid fa-location-dot"></i> Campus Venue <span class="required">*</span>
            </label>
            <input 
                type="text" 
                class="form-control" 
                id="venue" 
                name="venue" 
                placeholder="e.g. Main Auditorium / Lab 3" 
                value="<?= htmlspecialchars($venue) ?>" 
                required
            >
        </div>

        <!-- Date -->
        <div class="form-group">
            <label class="form-label" for="date">
                <i class="fa-regular fa-calendar"></i> Event Date <span class="required">*</span>
            </label>
            <input 
                type="date" 
                class="form-control" 
                id="date" 
                name="date" 
                value="<?= htmlspecialchars($date) ?>" 
                required
            >
        </div>

        <!-- Time Range Grid -->
        <div class="grid-2">
            <div class="form-group">
                <label class="form-label" for="start_time">
                    <i class="fa-regular fa-clock"></i> Start Time <span class="required">*</span>
                </label>
                <input 
                    type="time" 
                    class="form-control" 
                    id="start_time" 
                    name="start_time" 
                    value="<?= htmlspecialchars($start_time) ?>" 
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="end_time">
                    <i class="fa-solid fa-clock"></i> End Time <span class="required">*</span>
                </label>
                <input 
                    type="time" 
                    class="form-control" 
                    id="end_time" 
                    name="end_time" 
                    value="<?= htmlspecialchars($end_time) ?>" 
                    required
                >
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.5rem;">
            <i class="fa-solid fa-calendar-plus"></i> Publish Campus Event
        </button>

        <div style="text-align:center; margin-top:1.25rem;">
            <a href="events.php" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to Events Catalog
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
