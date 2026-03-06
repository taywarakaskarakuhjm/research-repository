<?php
/**
 * Database Configuration
 * Secure database connection setup with error handling
 */

// Database credentials (use environment variables in production)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'research_db');

// Create connection with proper error handling
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection error. Please contact administrator.');
}

// Set charset to utf8 to avoid issues with international characters
$conn->set_charset("utf8mb4");

// Enable error reporting for development (disable in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
