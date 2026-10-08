<?php
// events.php - Campus Events Explorer
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "Campus Events";

// Search / Filter query
$search = trim($_GET['search'] ?? '');
$events = [];

// Get logged-in user registered event IDs if any
$registeredEventIds = [];
$registeredEventNames = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $uname = $_SESSION['student_name'];
    $st = $conn->prepare("SELECT event_id, Event_Name FROM `registration` WHERE student_id = ? OR Student_Name = ?");
    if ($st) {
        $st->bind_param("is", $uid, $uname);
        $st->execute();
        $r = $st->get_result();
        while ($row = $r->fetch_assoc()) {
            if (!empty($row['event_id'])) $registeredEventIds[] = (int)$row['event_id'];
            if (!empty($row['Event_Name'])) $registeredEventNames[] = strtolower($row['Event_Name']);
        }
        $st->close();
    }
}

// Fetch events with optional search
if (!empty($search)) {
    $searchWild = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM `events` WHERE `Name` LIKE ? OR `Venue` LIKE ? ORDER BY `Date` ASC");
    $stmt->bind_param("ss", $searchWild, $searchWild);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query("SELECT * FROM `events` ORDER BY `Date` ASC");
}

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $events[] = $row;
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-calendar-days" style="color:var(--primary);"></i> Campus Events Explorer</h1>
        <p class="page-subtitle">Discover tech conferences, cultural festivals, workshops, and sports meets. Table: <code>events</code></p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="add_event.php" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i> Host New Event</a>
        <a href="event_register.php" class="btn btn-outline-primary"><i class="fa-solid fa-ticket"></i> Custom Registration</a>
    </div>
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 2rem; padding: 1.25rem;">
    <form action="events.php" method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <div style="flex:1; min-width:250px;">
            <input 
                type="text" 
                class="form-control" 
                name="search" 
                placeholder="Search events by title or venue..." 
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
        <?php if (!empty($search)): ?>
            <a href="events.php" class="btn btn-outline">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Events Grid -->
<?php if (!empty($events)): ?>
    <div class="grid-3">
        <?php foreach ($events as $ev): ?>
            <?php 
                $isAlreadyRegistered = in_array((int)$ev['ID'], $registeredEventIds) || in_array(strtolower($ev['Name']), $registeredEventNames);
                $isPast = strtotime($ev['Date']) < strtotime(date('Y-m-d'));
            ?>
            <div class="event-card">
                <div class="event-card-header" style="position:relative;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span class="badge badge-primary">Event #<?= (int)$ev['ID'] ?></span>
                        <?php if ($isAlreadyRegistered): ?>
                            <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Registered</span>
                        <?php elseif ($isPast): ?>
                            <span class="badge badge-secondary"><i class="fa-solid fa-clock-rotate-left"></i> Concluded</span>
                        <?php else: ?>
                            <span class="badge badge-warning"><i class="fa-solid fa-bolt"></i> Upcoming</span>
                        <?php endif; ?>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-top: 0.75rem; color:var(--dark);"><?= htmlspecialchars($ev['Name']) ?></h3>
                </div>

                <div class="event-card-body">
                    <ul class="event-meta">
                        <li>
                            <i class="fa-regular fa-calendar-check" style="color:var(--primary); width:20px;"></i>
                            <span><strong>Date:</strong> <?= date('l, M d, Y', strtotime($ev['Date'])) ?></span>
                        </li>
                        <li>
                            <i class="fa-solid fa-location-dot" style="color:var(--danger); width:20px;"></i>
                            <span><strong>Venue:</strong> <?= htmlspecialchars($ev['Venue']) ?></span>
                        </li>
                        <li>
                            <i class="fa-regular fa-clock" style="color:var(--secondary); width:20px;"></i>
                            <span><strong>Time:</strong> <?= date('h:i A', strtotime($ev['Start_Time'])) ?> &ndash; <?= date('h:i A', strtotime($ev['End_Time'])) ?></span>
                        </li>
                    </ul>

                    <div style="margin-top: 1rem; pt: 0.75rem; border-top:1px solid var(--border);">
                        <?php if ($isAlreadyRegistered): ?>
                            <a href="registrations.php?search=<?= urlencode($ev['Name']) ?>" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fa-solid fa-ticket-simple"></i> View My Registration
                            </a>
                        <?php else: ?>
                            <a href="event_register.php?event_id=<?= (int)$ev['ID'] ?>" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-ticket"></i> Book Event Slot
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card" style="text-align:center; padding:3rem 1.5rem;">
        <i class="fa-regular fa-calendar-xmark" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
        <h3>No Events Found</h3>
        <p style="color:var(--dark-muted); margin-bottom:1.5rem;">
            <?= !empty($search) ? "No campus events matched your search query '" . htmlspecialchars($search) . "'." : "No events are currently scheduled." ?>
        </p>
        <a href="add_event.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add the First Event</a>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . "/footer.php"; ?>
