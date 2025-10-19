<?php
// Database configuration for SK Payatas File Management System

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // leave blank for default XAMPP setup
define('DB_NAME', 'sk_payatas_db');

// Connect to MySQL using mysqli
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_errno) {
    die("Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants for file paths
define('BASE_PATH', realpath(__DIR__ . '/../') . '/');
define('UPLOAD_DIR', BASE_PATH . 'uploads/');

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>
