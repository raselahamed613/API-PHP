<?php
// Database connection details
$servername  = 'localhost';        // Database host
$databasename = 'test';  // Name of your database
$username  = 'root';    // Database username
$password = ''; // Database password

$conn = new Mysqli($servername, $username, $password, $databasename);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}
?>
