</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="index.php" class="brand" style="margin-bottom:0.6rem;">
                    <span class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></span>
                    <span>Student<span style="color:#0ea5e9;">Hub</span></span>
                </a>
                <p>An integrated, secure campus platform for managing students, university events, and event registrations powered by PHP MySQLi & prepared statements.</p>
            </div>

            <div class="footer-links">
                <h4>Portal Navigation</h4>
                <ul>
                    <li><a href="index.php"><i class="fa-solid fa-chevron-right"></i> Home Portal</a></li>
                    <li><a href="register.php"><i class="fa-solid fa-chevron-right"></i> Secure Register</a></li>
                    <li><a href="login.php"><i class="fa-solid fa-chevron-right"></i> Student Login</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="dashboard.php"><i class="fa-solid fa-chevron-right"></i> Dashboard</a></li>
                        <li><a href="profile.php"><i class="fa-solid fa-chevron-right"></i> Student Profile</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Events & Activities</h4>
                <ul>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="events.php"><i class="fa-solid fa-chevron-right"></i> Upcoming Events</a></li>
                        <li><a href="event_register.php"><i class="fa-solid fa-chevron-right"></i> Book Event Slot</a></li>
                        <li><a href="registrations.php"><i class="fa-solid fa-chevron-right"></i> Registration Records</a></li>
                        <li><a href="add_event.php"><i class="fa-solid fa-chevron-right"></i> Host Event</a></li>
                        <li><a href="about.php"><i class="fa-solid fa-chevron-right"></i> Database ER Model</a></li>
                    <?php else: ?>
                        <li><a href="login.php?msg=login_required" style="color:var(--dark-muted);"><i class="fa-solid fa-lock"></i> Upcoming Events (Login Req.)</a></li>
                        <li><a href="login.php?msg=login_required" style="color:var(--dark-muted);"><i class="fa-solid fa-lock"></i> Book Event Slot (Login Req.)</a></li>
                        <li><a href="login.php?msg=login_required" style="color:var(--dark-muted);"><i class="fa-solid fa-lock"></i> Registrations (Login Req.)</a></li>
                        <li><a href="login.php?msg=login_required" style="color:var(--dark-muted);"><i class="fa-solid fa-lock"></i> Host Event (Login Req.)</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Security & Schema</h4>
                <p style="font-size:0.85rem; color:var(--dark-muted); margin-bottom:0.75rem;">
                    <i class="fa-solid fa-shield-halved" style="color:var(--success);"></i> <strong>password_hash()</strong> encryption<br>
                    <i class="fa-solid fa-database" style="color:var(--primary);"></i> 3 MySQL Tables (student, events, registration)<br>
                    <i class="fa-solid fa-lock" style="color:var(--secondary);"></i> Access Control: Guest limited to Home, Register & Login
                </p>
                <?php if ($isLoggedIn): ?>
                    <a href="about.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-diagram-project"></i> View Database ER</a>
                <?php else: ?>
                    <a href="login.php?msg=login_required" class="btn btn-sm btn-outline"><i class="fa-solid fa-lock"></i> Sign In to View Architecture</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Student Hub Portal. All rights reserved. Practical Demonstration.</span>
            <span>Database: <strong>degree</strong> &bull; Host: <strong>localhost:9999</strong></span>
        </div>
    </div>
</footer>

<script>
function toggleNav() {
    const nav = document.getElementById('navMenu');
    if (nav) {
        nav.classList.toggle('show');
    }
}
</script>

</body>
</html>
