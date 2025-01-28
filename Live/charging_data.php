<?php
// Database credentials
$servername = "localhost"; // Use your server's address or localhost
$username = "root";        // Database username
$password = "Rasel@24";    // Database password
$dbname = "charging_data"; // Database name

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get JSON data from the ESP32
$jsonData = file_get_contents('php://input');

// Decode JSON data
$data = json_decode($jsonData, true);

// Check if JSON decoding was successful
if (json_last_error() === JSON_ERROR_NONE) {
    // Validate data fields
    if (isset($data['device_id'], $data['battery_voltage'], $data['charging_status'], $data['ssid'], $data['ip'])) {
        // Extract values
        $device_id = $data['device_id'];
        $voltage = (float)$data['battery_voltage']; // Ensure voltage is a float
        $status = (int)$data['charging_status'];    // Ensure status is an integer
        $ssid = $data['ssid'];
        $ip = $data['ip'];

        // Prepare an SQL statement
        $stmt = $conn->prepare("INSERT INTO dsp_ips (device_id, voltage, status, ssid, ip) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiss", $device_id, $voltage, $status, $ssid, $ip);

        // Execute the query
        if ($stmt->execute()) {
            echo "Data successfully stored in the database!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Invalid data fields received!";
    }
} else {
    echo "Invalid JSON received!";
}

// Close the database connection
$conn->close();
?>
