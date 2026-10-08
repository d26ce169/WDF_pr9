<?php
// header.php - Common Header & Navigation for Student Hub
require_once __DIR__ . "/connection.php";

$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$studentName = $_SESSION['student_name'] ?? 'Student';
$username = $_SESSION['username'] ?? '';

// Fetch quick live stats for the ribbon
$sCount = 0; $eCount = 0; $rCount = 0;
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `student`")) { $sCount = (int)$res->fetch_assoc()['c']; }
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `events`")) { $eCount = (int)$res->fetch_assoc()['c']; }
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `registration`")) { $rCount = (int)$res->fetch_assoc()['c']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " - " : "" ?>Student Hub Portal</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header class="site-header">
    <div class="nav-container">
        <a href="index.php" class="brand">
            <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
            <span>Student<span style="color:#0ea5e9;">Hub</span></span>
        </a>

        <button class="mobile-toggle" onclick="toggleNav()" aria-label="Toggle navigation">
            <i class="fa-solid fa-bars"></i>
        </button>

        <ul class="nav-menu" id="navMenu">
            <li><a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Home</a></li>
            
            <?php if ($isLoggedIn): ?>
                <li><a href="events.php" class="nav-link <?= $currentPage === 'events.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-days"></i> Events</a></li>
                <li><a href="event_register.php" class="nav-link <?= $currentPage === 'event_register.php' ? 'active' : '' ?>"><i class="fa-solid fa-ticket"></i> Register Event</a></li>
                <li><a href="registrations.php" class="nav-link <?= $currentPage === 'registrations.php' ? 'active' : '' ?>"><i class="fa-solid fa-list-check"></i> Registrations</a></li>
                <li><a href="add_event.php" class="nav-link <?= $currentPage === 'add_event.php' ? 'active' : '' ?>"><i class="fa-solid fa-calendar-plus"></i> Host Event</a></li>
                <li><a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                <li><a href="profile.php" class="nav-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-gear"></i> Profile</a></li>
                <li><a href="about.php" class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>"><i class="fa-solid fa-circle-info"></i> About & ER</a></li>
            <?php else: ?>
                <li><a href="register.php" class="nav-link <?= $currentPage === 'register.php' ? 'active' : '' ?>"><i class="fa-solid fa-user-plus"></i> Register</a></li>
                <li><a href="login.php" class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <?php if ($isLoggedIn): ?>
                <span class="user-badge" title="Logged in as <?= htmlspecialchars($studentName) ?>">
                    <span class="user-avatar"><?= strtoupper(substr($studentName, 0, 1)) ?></span>
                    <span><?= htmlspecialchars($studentName) ?></span>
                </span>
                <a href="logout.php" class="btn btn-sm btn-outline" title="Sign out of Student Hub"><i class="fa-solid fa-power-off"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-lock"></i> Student Sign In</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Access Ribbon: Adapts based on authentication status -->
    <div class="quick-ribbon">
        <div class="quick-ribbon-inner">
            <div class="quick-pills">
                <span style="font-weight:700; color:var(--dark); margin-right:4px;"><i class="fa-solid fa-compass"></i> Quick Links:</span>
                
                <?php if ($isLoggedIn): ?>
                    <a href="index.php" class="quick-pill <?= $currentPage === 'index.php' ? 'active' : '' ?>">1. Home</a>
                    <a href="register.php" class="quick-pill <?= $currentPage === 'register.php' ? 'active' : '' ?>">2. Student Register</a>
                    <a href="dashboard.php" class="quick-pill <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">3. Dashboard</a>
                    <a href="events.php" class="quick-pill <?= $currentPage === 'events.php' ? 'active' : '' ?>">4. Campus Events</a>
                    <a href="event_register.php" class="quick-pill <?= $currentPage === 'event_register.php' ? 'active' : '' ?>">5. Event Registration</a>
                    <a href="registrations.php" class="quick-pill <?= $currentPage === 'registrations.php' ? 'active' : '' ?>">6. All Registrations</a>
                    <a href="add_event.php" class="quick-pill <?= $currentPage === 'add_event.php' ? 'active' : '' ?>">7. Host Event</a>
                    <a href="profile.php" class="quick-pill <?= $currentPage === 'profile.php' ? 'active' : '' ?>">8. Profile</a>
                    <a href="about.php" class="quick-pill <?= $currentPage === 'about.php' ? 'active' : '' ?>">9. ER & Architecture</a>
                    <a href="logout.php" class="quick-pill" style="color:var(--danger);"><i class="fa-solid fa-power-off"></i> 10. Logout</a>
                <?php else: ?>
                    <a href="index.php" class="quick-pill <?= $currentPage === 'index.php' ? 'active' : '' ?>">1. Home</a>
                    <a href="register.php" class="quick-pill <?= $currentPage === 'register.php' ? 'active' : '' ?>">2. Register</a>
                    <a href="login.php" class="quick-pill <?= $currentPage === 'login.php' ? 'active' : '' ?>">3. Login</a>
                    <span style="font-size:0.8rem; color:var(--dark-muted); margin-left:0.5rem;">
                        <i class="fa-solid fa-lock" style="color:var(--warning);"></i> <em>Events, Registrations & Portal require student login.</em>
                    </span>
                <?php endif; ?>
            </div>
            <div>
                <span class="db-status-pill" title="Live database synchronization in 'degree'">
                    <span class="status-dot"></span>
                    <span>DB: <strong><?= $sCount ?></strong> Students | <strong><?= $eCount ?></strong> Events | <strong><?= $rCount ?></strong> Registrations</span>
                </span>
            </div>
        </div>
    </div>
</header>

<main class="main-content">
