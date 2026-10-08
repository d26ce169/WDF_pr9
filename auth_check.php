<?php
// auth_check.php - Restricts access to authenticated students only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php?msg=login_required");
    exit();
}
?>
