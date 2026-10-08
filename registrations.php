<?php
// registrations.php - Directory of All Campus Event Registrations (Table: registration)
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "All Registrations";

// Search filter
$search = trim($_GET['search'] ?? '');
$registrations = [];

if (!empty($search)) {
    $searchWild = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM `registration` WHERE `Student_Name` LIKE ? OR `Event_Name` LIKE ? OR `Venue` LIKE ? OR `student_id` LIKE ? ORDER BY `ID` DESC");
    if ($stmt) {
        $stmt->bind_param("ssss", $searchWild, $searchWild, $searchWild, $searchWild);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $registrations[] = $row;
        }
        $stmt->close();
    }
} else {
    $res = $conn->query("SELECT * FROM `registration` ORDER BY `ID` DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $registrations[] = $row;
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-list-check" style="color:var(--primary);"></i> Event Registrations Directory</h1>
        <p class="page-subtitle">Official records in MySQL table <code>registration</code> (<?= count($registrations) ?> total records)</p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="event_register.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Event Registration</a>
        <button onclick="window.print()" class="btn btn-outline"><i class="fa-solid fa-print"></i> Print Directory</button>
    </div>
</div>

<!-- Search Card -->
<div class="card" style="margin-bottom: 2rem; padding: 1.25rem;">
    <form action="registrations.php" method="GET" style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <div style="flex:1; min-width:260px;">
            <input 
                type="text" 
                class="form-control" 
                name="search" 
                placeholder="Search by student name, event title, venue, or Student ID..." 
                value="<?= htmlspecialchars($search) ?>"
            >
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Filter
        </button>
        <?php if (!empty($search)): ?>
            <a href="registrations.php" class="btn btn-outline">
                <i class="fa-solid fa-xmark"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Registered Participants</h3>
        <span class="badge badge-success"><i class="fa-solid fa-database"></i> Table: degree.registration</span>
    </div>

    <?php if (!empty($registrations)): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reg ID</th>
                        <th>Student ID (Unique)</th>
                        <th>Student Name</th>
                        <th>Event Name</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $r): ?>
                        <tr>
                            <td><span class="badge badge-secondary">#<?= (int)$r['ID'] ?></span></td>
                            <td>
                                <?php if (!empty($r['student_id'])): ?>
                                    <span class="badge badge-primary"><i class="fa-solid fa-id-badge"></i> ID: <?= htmlspecialchars($r['student_id']) ?></span>
                                <?php else: ?>
                                    <span style="color:var(--dark-muted); font-size:0.8rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><i class="fa-solid fa-user-graduate" style="color:var(--primary); margin-right:4px;"></i> <?= htmlspecialchars($r['Student_Name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-weight:600; color:var(--dark);"><?= htmlspecialchars($r['Event_Name']) ?></span>
                            </td>
                            <td>
                                <i class="fa-regular fa-calendar" style="color:var(--dark-muted);"></i> <?= !empty($r['Date']) ? date('M d, Y', strtotime($r['Date'])) : 'N/A' ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-location-dot" style="color:var(--danger);"></i> <?= htmlspecialchars($r['Venue']) ?>
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
        <div style="text-align:center; padding:3rem 1rem;">
            <i class="fa-solid fa-folder-open" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
            <h3>No Registrations Found</h3>
            <p style="color:var(--dark-muted); margin-bottom:1.5rem;">
                <?= !empty($search) ? "No registrations matched your filter." : "The registration table is currently empty." ?>
            </p>
            <a href="event_register.php" class="btn btn-primary"><i class="fa-solid fa-ticket"></i> Add First Registration</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
