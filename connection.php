<?php
// Database connection configuration for Student Hub ('degree' database)
$host = "localhost";
$user = "root";
$password = "";
$database = "degree";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

$conn->set_charset("utf8mb4");

// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure all 3 tables (student, events, registration) have active entries upon accessing the website
require_once __DIR__ . "/db_init.php";
?>