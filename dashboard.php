<?php
// dashboard.php - Personalized Student Dashboard
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "Student Dashboard";

$studentId = $_SESSION['user_id'];
$studentName = $_SESSION['student_name'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Fetch latest details from DB in case profile was updated
$stmtStu = $conn->prepare("SELECT * FROM `student` WHERE `ID` = ? LIMIT 1");
$stmtStu->bind_param("i", $studentId);
$stmtStu->execute();
$currentStudent = $stmtStu->get_result()->fetch_assoc();
$stmtStu->close();

if ($currentStudent) {
    $studentName = $currentStudent['STUDENT_NAME'];
    $username = $currentStudent['USERNAME'];
    $email = $currentStudent['EMAIL'];
    $mobile = $currentStudent['MOBILE_NUMBER'];
    $gender = $currentStudent['GENDER'];
} else {
    $mobile = $_SESSION['mobile_number'] ?? 'N/A';
    $gender = $_SESSION['gender'] ?? 'N/A';
}

// Fetch registrations for this student
$myRegistrations = [];
$stmtReg = $conn->prepare("SELECT r.*, e.Start_Time, e.End_Time FROM `registration` r LEFT JOIN `events` e ON (r.event_id = e.ID OR r.Event_Name = e.Name) WHERE r.student_id = ? OR r.Student_Name = ? ORDER BY r.Date ASC, r.ID DESC");
if ($stmtReg) {
    $stmtReg->bind_param("is", $studentId, $studentName);
    $stmtReg->execute();
    $resReg = $stmtReg->get_result();
    while ($row = $resReg->fetch_assoc()) {
        $myRegistrations[] = $row;
    }
    $stmtReg->close();
}

// Total campus events count
$totalEventsCount = 0;
if ($evRes = $conn->query("SELECT COUNT(*) as c FROM `events`")) {
    $totalEventsCount = (int)$evRes->fetch_assoc()['c'];
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-gauge-high" style="color:var(--primary);"></i> Student Portal Dashboard</h1>
        <p class="page-subtitle">Welcome back, <strong><?= htmlspecialchars($studentName) ?></strong> (<?= htmlspecialchars($username) ?>)! Manage your campus activities.</p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="events.php" class="btn btn-primary"><i class="fa-solid fa-calendar-plus"></i> Browse & Register Events</a>
        <a href="profile.php" class="btn btn-outline"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
        <a href="logout.php" class="btn btn-danger btn-sm"><i class="fa-solid fa-power-off"></i> Logout</a>
    </div>
</div>

<?php if (!empty($actionMsg)): ?>
    <div class="alert alert-success">
        <i class="fa-solid fa-circle-check" style="font-size:1.2rem;"></i>
        <div><?= htmlspecialchars($actionMsg) ?></div>
    </div>
<?php endif; ?>

<!-- Top Dashboard Summary Grid -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fa-solid fa-ticket-simple"></i></div>
        <div class="stat-info">
            <h3><?= count($myRegistrations) ?></h3>
            <p>My Registered Events</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="stat-info">
            <h3><?= $totalEventsCount ?></h3>
            <p>Total College Events Available</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success"><i class="fa-solid fa-shield-check"></i></div>
        <div class="stat-info">
            <h3 style="font-size:1.4rem; color:var(--success);">Verified</h3>
            <p>Active Student Session</p>
        </div>
    </div>
</div>

<div class="grid-3" style="align-items: flex-start; margin-bottom: 2.5rem;">
    <!-- Student Profile Card (1 column) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-id-card" style="color:var(--primary);"></i> Student Details</h3>
            <span class="badge badge-primary">ID #<?= $studentId ?></span>
        </div>
        <div style="text-align:center; padding:1rem 0;">
            <div style="width:72px; height:72px; background:linear-gradient(135deg, var(--primary), var(--accent)); color:white; border-radius:50%; margin:0 auto 1rem; display:flex; align-items:center; justify-content:center; font-size:1.8rem; font-weight:700;">
                <?= strtoupper(substr($studentName, 0, 1)) ?>
            </div>
            <h4 style="font-size:1.2rem; color:var(--dark); margin-bottom:0.2rem;"><?= htmlspecialchars($studentName) ?></h4>
            <p style="font-size:0.85rem; color:var(--dark-muted);"><i class="fa-solid fa-at"></i> <?= htmlspecialchars($username) ?></p>
        </div>
        <hr style="border:none; border-top:1px solid var(--border); margin:1rem 0;">
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.75rem; font-size:0.9rem;">
            <li><strong><i class="fa-solid fa-envelope" style="color:var(--dark-muted); width:20px;"></i> Email:</strong> <span style="word-break:break-all;"><?= htmlspecialchars($email) ?></span></li>
            <li><strong><i class="fa-solid fa-phone" style="color:var(--dark-muted); width:20px;"></i> Mobile:</strong> <?= htmlspecialchars($mobile) ?></li>
            <li><strong><i class="fa-solid fa-venus-mars" style="color:var(--dark-muted); width:20px;"></i> Gender:</strong> <?= htmlspecialchars($gender) ?></li>
            <li><strong><i class="fa-solid fa-database" style="color:var(--dark-muted); width:20px;"></i> MySQL Table:</strong> <code>student</code></li>
        </ul>
        <div style="margin-top:1.5rem;">
            <a href="profile.php" class="btn btn-outline btn-block btn-sm"><i class="fa-solid fa-key"></i> Security & Password</a>
        </div>
    </div>

    <!-- My Registered Events Table (2 columns) -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fa-solid fa-list-check" style="color:var(--success);"></i> My Event Registrations</h3>
                <p style="font-size:0.85rem; color:var(--dark-muted); margin-top:0.2rem;">Loaded dynamically from <code>registration</code> table for student #<?= $studentId ?></p>
            </div>
            <a href="event_register.php" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> New Registration</a>
        </div>

        <?php if (!empty($myRegistrations)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reg #</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($myRegistrations as $reg): ?>
                            <tr>
                                <td><span class="badge badge-secondary">#<?= (int)$reg['ID'] ?></span></td>
                                <td><strong><?= htmlspecialchars($reg['Event_Name']) ?></strong></td>
                                <td><i class="fa-regular fa-calendar"></i> <?= date('M d, Y', strtotime($reg['Date'])) ?></td>
                                <td><i class="fa-solid fa-location-dot" style="color:var(--danger);"></i> <?= htmlspecialchars($reg['Venue']) ?></td>
                                <td>
                                    <?php if (!empty($reg['Start_Time'])): ?>
                                        <?= date('h:i A', strtotime($reg['Start_Time'])) ?>
                                    <?php else: ?>
                                        <span style="color:var(--dark-muted);">TBA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Confirmed</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:2.5rem 1rem;">
                <i class="fa-solid fa-ticket" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
                <h4 style="color:var(--dark); margin-bottom:0.4rem;">No Event Registrations Found Yet</h4>
                <p style="font-size:0.9rem; color:var(--dark-muted); max-width:400px; margin:0 auto 1.25rem;">
                    You haven't registered for any events yet. Check out the latest campus events and book your slot!
                </p>
                <a href="events.php" class="btn btn-primary"><i class="fa-solid fa-calendar-days"></i> Explore Events</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
