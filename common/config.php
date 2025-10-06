<?php
// Database configuration
$host = "localhost";     // Database host
$user = "root";          // MySQL username
$pass = "";              // MySQL password (set your password if not empty)
$db   = "school_db";     // Database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
