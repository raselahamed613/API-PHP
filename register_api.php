<?php
// Include database connection
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from $_POST
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $email = $_POST['email'] ?? null;
    $mobile = $_POST['mobile'] ?? null;
    $device_id = $_POST['device_id'] ?? null;
    $additional = $_POST['additional'] ?? null;

    // Validate input
    if (!$username || !$password || !$email || !$mobile || !$device_id) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into the database
    $sql = "INSERT INTO users (username, password, email, mobile, additional, device_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->bind_param("ssssss", $username, $hashed_password, $email, $mobile, $additional, $device_id);
        $stmt->execute();
        echo json_encode(['message' => 'User registered successfully!']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}


// Close the database connection
$conn->close();
?>
