<?php
// Database connection settings
$host = "localhost";        // Server hostname
$user = "root";             // MySQL username
$password = "Rasel@24";     // MySQL password
$dbname = "rfid_db";        // Database name

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if 'card_id' is present in the query string
if (isset($_GET['card_id'])) {
    $card_id = $_GET['card_id'];  // Get the card_id from the URL parameter

    // Validate the card_id (ensure it's not empty)
    if (empty($card_id)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Card ID cannot be empty"]);
        exit();
    }

    // Insert data into the database
    $sql = "INSERT INTO rfid_logs (card_id) VALUES ('$card_id')";
    if ($conn->query($sql) === TRUE) {
        http_response_code(200); // OK
        echo json_encode(["message" => "Card ID saved successfully"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Failed to save card ID", "error" => $conn->error]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Missing card ID"]);
}

// Close connection
$conn->close();
?>
