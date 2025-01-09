<?php
require 'db_connection.php';

// echo json_encode(['message' => 'Database connection successful']);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connection successful!";
}
?>