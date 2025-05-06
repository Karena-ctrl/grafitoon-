<?php
// Configuration file for Cartoon-Themed Custom Clothing E-commerce Platform

// Database Configuration
define('DB_HOST', 'localhost'); // Change if using a remote database
define('DB_USER', 'root'); // Replace with your database username
define('DB_PASS', ''); // Replace with your database password
define('DB_NAME', 'NewDb4'); // Replace with your database name

// Establish Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start Session
session_start();

// Define Site URL (Update accordingly)
define('SITE_URL', 'http://localhost/Grafitoon/');

// Security Settings
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);

define('SESSION_TIMEOUT', 3600); // Session timeout in seconds (1 hour)

// Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
