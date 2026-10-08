<?php
// about.php - About Student Hub, Problem Definition & Database ER Architecture
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/connection.php";

$pageTitle = "About & ER Architecture";

// Query live stats for all 3 tables
$tableStats = [
    'student' => ['count' => 0, 'columns' => []],
    'events' => ['count' => 0, 'columns' => []],
    'registration' => ['count' => 0, 'columns' => []]
];

foreach (array_keys($tableStats) as $tbl) {
    if ($res = $conn->query("SELECT COUNT(*) as c FROM `{$tbl}`")) {
        $tableStats[$tbl]['count'] = (int)$res->fetch_assoc()['c'];
    }
    if ($colRes = $conn->query("SHOW COLUMNS FROM `{$tbl}`")) {
        while ($col = $colRes->fetch_assoc()) {
            $tableStats[$tbl]['columns'][] = $col;
        }
    }
}

require_once __DIR__ . "/header.php";
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fa-solid fa-diagram-project" style="color:var(--primary);"></i> ER Diagram & System Architecture</h1>
        <p class="page-subtitle">Detailed documentation of the 3 database tables, security mechanisms, and practical problem implementation.</p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="register.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Test Secure Registration</a>
        <a href="events.php" class="btn btn-outline-primary"><i class="fa-solid fa-calendar-days"></i> Browse Events</a>
    </div>
</div>

<!-- Practical Problem Statement Banner -->
<div class="card" style="margin-bottom: 2rem; border-left: 5px solid var(--primary);">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
        <span class="badge badge-primary"><i class="fa-solid fa-book-bookmark"></i> Lab Practical Statement</span>
        <h3 style="font-size:1.25rem;">Problem Definition</h3>
    </div>
    <blockquote style="font-size:1.05rem; font-style:italic; color:var(--dark); background:#f8fafc; padding:1.25rem; border-radius:8px; border:1px solid var(--border); line-height:1.7;">
        &ldquo;Implement secure user registration by inserting user details into MySQL using MYSQLi. Validate input on frontend and backend, check duplicate email/username, hash passwords using <code>password_hash()</code>, and display appropriate messages.&rdquo;
    </blockquote>
    <div style="margin-top:1rem; display:flex; gap:0.75rem; flex-wrap:wrap; font-size:0.88rem; color:var(--dark-muted);">
        <span><i class="fa-solid fa-check" style="color:var(--success);"></i> Frontend Validation (JS)</span> &bull;
        <span><i class="fa-solid fa-check" style="color:var(--success);"></i> Backend Validation (PHP)</span> &bull;
        <span><i class="fa-solid fa-check" style="color:var(--success);"></i> Duplicate Check Prepared Stmt</span> &bull;
        <span><i class="fa-solid fa-check" style="color:var(--success);"></i> password_hash() Encryption</span> &bull;
        <span><i class="fa-solid fa-check" style="color:var(--success);"></i> 3-Table Live Population</span>
    </div>
</div>

<!-- Database ER Diagram Visualization -->
<div class="card" style="margin-bottom: 2.5rem;">
    <div class="card-header">
        <div>
            <h3 class="card-title"><i class="fa-solid fa-network-wired" style="color:var(--secondary);"></i> Relational ER Architecture (Database: <code>degree</code>)</h3>
            <p style="font-size:0.85rem; color:var(--dark-muted); margin-top:0.2rem;">Visual schema matching the phpMyAdmin Designer ER diagram</p>
        </div>
        <span class="badge badge-success"><i class="fa-solid fa-check-double"></i> Live Synchronized</span>
    </div>

    <div class="grid-3" style="margin-bottom: 1.5rem; align-items: stretch;">
        <!-- Table 1: student -->
        <div class="card" style="border: 2px solid #c7d2fe; background:#ffffff;">
            <div style="background:var(--primary-light); padding:0.75rem 1rem; border-radius:6px; margin:-0.5rem -0.5rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:var(--primary); font-size:1rem;"><i class="fa-solid fa-table"></i> degree.student</strong>
                <span class="badge badge-primary"><?= $tableStats['student']['count'] ?> rows</span>
            </div>
            <ul style="list-style:none; font-size:0.85rem; display:flex; flex-direction:column; gap:0.45rem;">
                <li style="font-weight:700; color:var(--primary);"><i class="fa-solid fa-key"></i> ID : int(11) [PK, AI]</li>
                <li><i class="fa-solid fa-font"></i> STUDENT_NAME : varchar(1000)</li>
                <li style="color:#0284c7; font-weight:600;"><i class="fa-solid fa-at"></i> USERNAME : varchar(100) [UNIQUE]</li>
                <li style="color:#0284c7; font-weight:600;"><i class="fa-solid fa-envelope"></i> EMAIL : varchar(100) [UNIQUE]</li>
                <li><i class="fa-solid fa-phone"></i> MOBILE_NUMBER : varchar(15)</li>
                <li><i class="fa-solid fa-venus-mars"></i> GENDER : varchar(20)</li>
                <li style="color:var(--danger); font-weight:600;"><i class="fa-solid fa-lock"></i> PASSWORD : varchar(255) [HASHED]</li>
            </ul>
            <div style="margin-top:1.25rem;">
                <a href="register.php" class="btn btn-sm btn-outline-primary btn-block"><i class="fa-solid fa-user-plus"></i> Register Student</a>
            </div>
        </div>

        <!-- Table 2: registration (Center Join Table) -->
        <div class="card" style="border: 2px solid #a7f3d0; background:#ffffff;">
            <div style="background:var(--success-bg); padding:0.75rem 1rem; border-radius:6px; margin:-0.5rem -0.5rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:#065f46; font-size:1rem;"><i class="fa-solid fa-table"></i> degree.registration</strong>
                <span class="badge badge-success"><?= $tableStats['registration']['count'] ?> rows</span>
            </div>
            <ul style="list-style:none; font-size:0.85rem; display:flex; flex-direction:column; gap:0.45rem;">
                <li style="font-weight:700; color:var(--success);"><i class="fa-solid fa-key"></i> ID : int(11) [PK, AI]</li>
                <li><i class="fa-solid fa-user"></i> Student_Name : varchar(100)</li>
                <li><i class="fa-solid fa-star"></i> Event_Name : varchar(100)</li>
                <li><i class="fa-regular fa-calendar"></i> Date : date</li>
                <li><i class="fa-solid fa-location-dot"></i> Venue : varchar(100)</li>
                <li style="color:var(--primary); font-weight:600;"><i class="fa-solid fa-id-badge"></i> student_id : varchar(100) [UNIQUE]</li>
                <li style="color:var(--secondary); font-weight:600;"><i class="fa-solid fa-link"></i> event_id : int(11) [FK -> events.ID]</li>
            </ul>
            <div style="margin-top:1.25rem;">
                <a href="event_register.php" class="btn btn-sm btn-success btn-block"><i class="fa-solid fa-ticket"></i> Register Event</a>
            </div>
        </div>

        <!-- Table 3: events -->
        <div class="card" style="border: 2px solid #bae6fd; background:#ffffff;">
            <div style="background:#e0f2fe; padding:0.75rem 1rem; border-radius:6px; margin:-0.5rem -0.5rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                <strong style="color:#0284c7; font-size:1rem;"><i class="fa-solid fa-table"></i> degree.events</strong>
                <span class="badge badge-secondary"><?= $tableStats['events']['count'] ?> rows</span>
            </div>
            <ul style="list-style:none; font-size:0.85rem; display:flex; flex-direction:column; gap:0.45rem;">
                <li style="font-weight:700; color:#0284c7;"><i class="fa-solid fa-key"></i> ID : int(11) [PK, AI]</li>
                <li><i class="fa-solid fa-heading"></i> Name : varchar(100)</li>
                <li><i class="fa-solid fa-location-dot"></i> Venue : varchar(100)</li>
                <li><i class="fa-regular fa-clock"></i> Start_Time : time</li>
                <li><i class="fa-solid fa-clock"></i> End_Time : time</li>
                <li><i class="fa-regular fa-calendar"></i> Date : date</li>
            </ul>
            <div style="margin-top:1.25rem;">
                <a href="add_event.php" class="btn btn-sm btn-outline btn-block"><i class="fa-solid fa-plus-circle"></i> Add Campus Event</a>
            </div>
        </div>
    </div>

    <!-- Relationship Summary -->
    <div style="background:#f8fafc; border-radius:8px; padding:1.25rem; border:1px solid var(--border);">
        <h4 style="font-size:1rem; margin-bottom:0.5rem;"><i class="fa-solid fa-circle-nodes" style="color:var(--primary);"></i> Relational Architecture Summary</h4>
        <p style="font-size:0.9rem; color:var(--dark-muted); line-height:1.6;">
            The Student Hub bridges students and campus events through the <code>registration</code> table. 
            When a student signs up, an entry is added to <code>student</code>. When campus activities are scheduled, entries are saved in <code>events</code>. 
            When students reserve a seat for an event, records are created in <code>registration</code> referencing both <code>student_id</code> and <code>event_id</code>, maintaining full referential integrity.
        </p>
    </div>
</div>

<!-- Security Architecture Breakdown -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-shield-halved" style="color:var(--success);"></i> Security & Defensive Engineering</h3>
    </div>

    <div class="grid-2">
        <div style="display:flex; gap:1rem;">
            <div style="width:40px; height:40px; border-radius:8px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; margin-bottom:0.3rem;">Bcrypt Password Hashing</h4>
                <p style="font-size:0.87rem; color:var(--dark-muted);">Uses PHP <code>password_hash($pass, PASSWORD_DEFAULT)</code> with cryptographically secure salts. Verified upon login using <code>password_verify()</code>.</p>
            </div>
        </div>

        <div style="display:flex; gap:1rem;">
            <div style="width:40px; height:40px; border-radius:8px; background:var(--success-bg); color:var(--success); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; margin-bottom:0.3rem;">SQL Injection Prevention</h4>
                <p style="font-size:0.87rem; color:var(--dark-muted);">All database queries use MySQLi Prepared Statements (<code>prepare()</code>, <code>bind_param()</code>, <code>execute()</code>) completely isolating data from query logic.</p>
            </div>
        </div>

        <div style="display:flex; gap:1rem;">
            <div style="width:40px; height:40px; border-radius:8px; background:var(--warning-bg); color:var(--warning); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                <i class="fa-solid fa-clone"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; margin-bottom:0.3rem;">Duplicate Username & Email Checks</h4>
                <p style="font-size:0.87rem; color:var(--dark-muted);">Queries the database before insertion to prevent duplicate account registration and provides exact feedback messages indicating which field collided.</p>
            </div>
        </div>

        <div style="display:flex; gap:1rem;">
            <div style="width:40px; height:40px; border-radius:8px; background:var(--info-bg); color:var(--info); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0;">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <h4 style="font-size:1rem; margin-bottom:0.3rem;">Frontend & Backend Two-Tier Validation</h4>
                <p style="font-size:0.87rem; color:var(--dark-muted);">Frontend checks length, regex patterns, phone format, and matching passwords. Backend enforces identical sanitization, regex validations, and type constraints.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
