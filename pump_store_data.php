<?php
// Database credentials
$servername = "localhost"; // Use your server's address or localhost
$username = "root";        // Database username
$password = "Rasel@24";            // Database password
$dbname = "pump_data";      // Database name

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
    // Extract values
    $device_id = $data['device_id'];
    $lowSensor = $data['lowSensor'];
    $midLowSensor = $data['midLowSensor'];
    $midSensor = $data['midSensor'];
    $fullSensor = $data['fullSensor'];
    $motorState = $data['motorState'];
    $ssid = $data['ssid'];
    $ip = $data['ip'];
	
    if($data['midLowSensor'] == 1){
    //$url = "http://192.168.0.102/lock_set?state=1";	
    // Trigger the request (this sends the GET request)
    file_get_contents($url);
    }
	
	
    // Prepare an SQL statement
    $stmt = $conn->prepare("INSERT INTO sensor_data (device_id, lowSensor, midLowSensor, midSensor, fullSensor, motorState, ssid, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiiiiss", $device_id, $lowSensor, $midLowSensor, $midSensor, $fullSensor, $motorState, $ssid, $ip);

    // Execute the query
    if ($stmt->execute()) {
        echo "Data successfully stored in the database!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid JSON received!";
}

// Close the database connection
$conn->close();
?>
