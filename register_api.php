<?php
require 'db_connection.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON data sent by the front-end
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($data['username'], $data['password'], $data['email'], $data['mobile'], $data['device_id'])) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $username = $data['username'];
    $password = $data['password'];
    $email = $data['email'];
    $mobile = $data['mobile'];
    $additional = isset($data['additional']) ? $data['additional'] : null;
    $device_id = $data['device_id'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert into the database
    $sql = "INSERT INTO users (username, password, email, mobile, additional, device_id) 
            VALUES (:username, :password, :email, :mobile, :additional, :device_id)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashed_password,
            ':email' => $email,
            ':mobile' => $mobile,
            ':additional' => $additional,
            ':device_id' => $device_id,
        ]);
        echo json_encode(['message' => 'User registered successfully!']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry error
            echo json_encode(['error' => 'Username or email already exists']);
        } else {
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
