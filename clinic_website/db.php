<?php
$host = "127.0.0.1";  // Use the IP address instead of localhost
$port = "3307";       // Specify the custom port number (3307)
$username = "root";   // Change this to your MySQL username
$password = "root";       // Change this to your MySQL password
$database = "clinic_web_db";  // The name of your database


// Enable exception handling for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create a new connection using the specified port
$conn = new mysqli($host, $username, $password, $database, $port);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . " (Error Code: " . $conn->connect_errno . ")");
} else {
    echo "Connected successfully to the database: " . $database;
}
?>




