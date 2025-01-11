<?php
$servername = "localhost";
$username = "root"; // your database username
$password = "Rasel@24"; // your database password
$dbname = "user_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
