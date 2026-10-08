<?php
// index.php - Student Hub Main Landing & Overview Portal
$pageTitle = "Home";
require_once __DIR__ . "/header.php";

// Fetch counts and sample upcoming events
$studentCount = 0;
$eventCount = 0;
$registrationCount = 0;
$eventsList = [];

if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `student`")) {
        $studentCount = (int)$res->fetch_assoc()['c'];
    }
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `events`")) {
        $eventCount = (int)$res->fetch_assoc()['c'];
    }
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `registration`")) {
        $registrationCount = (int)$res->fetch_assoc()['c'];
    }
    if ($res = $conn->query("SELECT * FROM `events` ORDER BY `Date` ASC LIMIT 3")) {
        while ($row = $res->fetch_assoc()) {
            $eventsList[] = $row;
        }
    }
}
?>

<!-- Hero Banner -->
<div class="hero-banner">
    <div style="max-width: 800px; position:relative; z-index:1;">
        <span class="badge badge-warning" style="margin-bottom:1rem; font-size:0.85rem; padding:0.4rem 0.9rem;">
            <i class="fa-solid fa-sparkles"></i> 2026 Academic Session Active
        </span>
        <h1 class="hero-title">Your Central Hub for Student Life, Events & Registrations</h1>
        <p class="hero-desc">
            Seamlessly register students, explore campus workshops, hackathons, and symposiums, and manage your registrations with high security powered by PHP MySQLi.
        </p>
        <div class="hero-actions">
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="btn btn-lg btn-success">
                    <i class="fa-solid fa-gauge-high"></i> Go to My Dashboard
                </a>
                <a href="events.php" class="btn btn-lg" style="background:white; color:var(--primary); font-weight:700;">
                    <i class="fa-solid fa-calendar-check"></i> Explore Events
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-lg" style="background:#ffffff; color:var(--primary); font-weight:700;">
                    <i class="fa-solid fa-user-plus"></i> Register as Student
                </a>
                <a href="login.php" class="btn btn-lg btn-outline" style="background:rgba(255,255,255,0.15); color:white; border-color:rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Student Login
                </a>
                <a href="events.php" class="btn btn-lg" style="background:rgba(0,0,0,0.2); color:white; border:1px solid rgba(255,255,255,0.3);">
                    <i class="fa-solid fa-calendar-days"></i> Browse Events
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Live Campus Counters -->
<div class="grid-4" style="margin-bottom: 2.5rem;">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $studentCount ?></h3>
            <p>Registered Students (Table 1)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary"><i class="fa-solid fa-calendar-star"></i></div>
        <div class="stat-info">
            <h3><?= $eventCount ?></h3>
            <p>Active Campus Events (Table 2)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success"><i class="fa-solid fa-ticket-simple"></i></div>
        <div class="stat-info">
            <h3><?= $registrationCount ?></h3>
            <p>Confirmed Registrations (Table 3)</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="stat-info">
            <h3 style="font-size:1.35rem; color:#065f46;">Secured</h3>
            <p>password_hash & Prepared Stmts</p>
        </div>
    </div>
</div>

<!-- 10-Page Direct Access Hub Grid -->
<div style="margin-bottom: 3rem;">
    <div class="page-header" style="margin-bottom: 1.25rem;">
        <div>
            <h2 class="page-title"><i class="fa-solid fa-compass" style="color:var(--primary);"></i> 10 Interactive Hub Pages</h2>
            <p class="page-subtitle">Every feature and page of the website is accessible instantly with one button click.</p>
        </div>
    </div>

    <div class="grid-3">
        <!-- 1. Home -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-primary">Page 1</span>
                    <i class="fa-solid fa-house" style="font-size:1.4rem; color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Home Portal</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Central dashboard overview with live stats, announcements, and quick access navigation.</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-arrow-right"></i> Open Home
            </a>
        </div>

        <!-- 2. Register -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; border-color:#c7d2fe;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-success">Page 2 &bull; Core Practical</span>
                    <i class="fa-solid fa-user-plus" style="font-size:1.4rem; color:var(--success);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Student Registration</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Secure registration into MySQL <code>student</code> table with validation & password hashing.</p>
            </div>
            <a href="register.php" class="btn btn-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-user-plus"></i> Go to Registration
            </a>
        </div>

        <!-- 3. Login -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-primary">Page 3</span>
                    <i class="fa-solid fa-arrow-right-to-bracket" style="font-size:1.4rem; color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Student Login</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Authenticate with username/email & password using PHP <code>password_verify()</code>.</p>
            </div>
            <a href="login.php" class="btn btn-outline-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-lock"></i> Student Sign In
            </a>
        </div>

        <!-- 4. Dashboard -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-secondary">Page 4</span>
                    <i class="fa-solid fa-gauge-high" style="font-size:1.4rem; color:var(--secondary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Student Dashboard</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Personalized student portal, registered events list, and quick actions.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-gauge"></i> Open Dashboard
            </a>
        </div>

        <!-- 5. Events -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-primary">Page 5</span>
                    <i class="fa-solid fa-calendar-days" style="font-size:1.4rem; color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Campus Events Explorer</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Browse upcoming college events from the <code>events</code> table with filtering and direct booking.</p>
            </div>
            <a href="events.php" class="btn btn-outline-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-calendar-days"></i> View Events
            </a>
        </div>

        <!-- 6. Event Register -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-warning">Page 6</span>
                    <i class="fa-solid fa-ticket" style="font-size:1.4rem; color:var(--warning);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Event Registration</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Register a student for a selected campus event, populating the <code>registration</code> table.</p>
            </div>
            <a href="event_register.php" class="btn btn-outline-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-ticket"></i> Book Event Slot
            </a>
        </div>

        <!-- 7. All Registrations -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-primary">Page 7</span>
                    <i class="fa-solid fa-list-check" style="font-size:1.4rem; color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Registrations Records</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Live directory of all student registrations in the <code>registration</code> table with search and pass print.</p>
            </div>
            <a href="registrations.php" class="btn btn-outline btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-table-list"></i> View Registrations
            </a>
        </div>

        <!-- 8. Add / Host Event -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-primary">Page 8</span>
                    <i class="fa-solid fa-calendar-plus" style="font-size:1.4rem; color:var(--primary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Host / Add Campus Event</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">Create new campus events inserting records directly into the <code>events</code> table.</p>
            </div>
            <a href="add_event.php" class="btn btn-outline-primary btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-plus-circle"></i> Create New Event
            </a>
        </div>

        <!-- 9. Profile -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <span class="badge badge-secondary">Page 9</span>
                    <i class="fa-solid fa-user-gear" style="font-size:1.4rem; color:var(--secondary);"></i>
                </div>
                <h3 style="font-size:1.15rem; margin-bottom:0.5rem;">Student Profile & Security</h3>
                <p style="font-size:0.88rem; color:var(--dark-muted);">View personal student details, update mobile & gender, or change password securely.</p>
            </div>
            <a href="profile.php" class="btn btn-outline btn-sm btn-block" style="margin-top:1rem;">
                <i class="fa-solid fa-user"></i> Manage Profile
            </a>
        </div>

        <!-- 10. About & ER Diagram -->
        <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; grid-column: span 3;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
                <div>
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                        <span class="badge badge-success">Page 10</span>
                        <h3 style="font-size:1.25rem;">Database ER Architecture & System Documentation</h3>
                    </div>
                    <p style="font-size:0.92rem; color:var(--dark-muted); max-width:850px;">
                        Interactive ER model demonstration matching phpMyAdmin designer for the 3 tables: <code>student</code>, <code>events</code>, and <code>registration</code>. Includes live database integrity checks, security validation walkthrough, and table inspectors.
                    </p>
                </div>
                <a href="about.php" class="btn btn-primary" style="margin-top:0.5rem;">
                    <i class="fa-solid fa-diagram-project"></i> Open ER & Architecture Page
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Featured Upcoming Campus Events -->
<div style="margin-bottom: 2.5rem;">
    <div class="page-header" style="margin-bottom: 1.25rem;">
        <div>
            <h2 class="page-title"><i class="fa-solid fa-fire" style="color:var(--danger);"></i> Featured Campus Events</h2>
            <p class="page-subtitle">Events loaded directly from MySQL table <code>events</code></p>
        </div>
        <a href="events.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye"></i> View All (<?= $eventCount ?>)</a>
    </div>

    <div class="grid-3">
        <?php if (!empty($eventsList)): ?>
            <?php foreach ($eventsList as $ev): ?>
                <div class="event-card">
                    <div class="event-card-header">
                        <span class="badge badge-primary"><i class="fa-solid fa-calendar"></i> <?= date('M d, Y', strtotime($ev['Date'])) ?></span>
                        <h3 style="font-size: 1.15rem; margin-top: 0.6rem; color:var(--dark);"><?= htmlspecialchars($ev['Name']) ?></h3>
                    </div>
                    <div class="event-card-body">
                        <ul class="event-meta">
                            <li><i class="fa-solid fa-location-dot" style="color:var(--danger);"></i> <?= htmlspecialchars($ev['Venue']) ?></li>
                            <li><i class="fa-solid fa-clock" style="color:var(--secondary);"></i> <?= date('h:i A', strtotime($ev['Start_Time'])) ?> - <?= date('h:i A', strtotime($ev['End_Time'])) ?></li>
                        </ul>
                        <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                            <a href="event_register.php?event_id=<?= (int)$ev['ID'] ?>" class="btn btn-primary btn-sm btn-block">
                                <i class="fa-solid fa-ticket"></i> Register Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--dark-muted);">No events found in table.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>