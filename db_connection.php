<?php
// Database connection details
$host = 'localhost';        // Database host
$dbname = 'test';  // Name of your database
$user = 'root';    // Database username
$password = ''; // Database password

try {
    // Create a new PDO instance to connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If the connection fails, output an error message and stop execution
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
