<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Database credentials
$servername = "localhost";
$username = "root";
$password = "Rasel@24";
$dbname = "pump_data";

// Check if this is a request for JSON data
if (isset($_GET['fetch_data'])) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to fetch the latest row
    $sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1"; // Fetch only the latest row
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode([]);
    }

    $conn->close();
    exit(); // Terminate script after sending JSON response
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tank Sensor Visualization</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .tank {
            width: 200px;
            height: 400px;
            border: 2px solid #000;
            position: relative;
            margin: 0 auto;
            background: #e0e0e0;
        }
        .water {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: #00f;
            transition: height 0.5s ease-in-out;
        }
        .sensor-label {
            position: absolute;
            width: 100%;
            text-align: center;
            font-weight: bold;
            color: #fff;
        }
        .sensor-level {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Tank Water Level Visualization</h1>
    <div class="tank">
        <!-- Water level -->
        <div class="water" id="water-level" style="height: 0;"></div>
        <!-- Sensor labels -->
        <div class="sensor-label" style="bottom: 0;">Low</div>
        <div class="sensor-label" style="bottom: 25%;">Mid-Low</div>
        <div class="sensor-label" style="bottom: 50%;">Mid</div>
        <div class="sensor-label" style="bottom: 75%;">Full</div>
    </div>
    <div class="sensor-level">
        <strong>Motor State:</strong> <span id="motor-state">OFF</span>
    </div>
    <a href="home.php">Back to Home</a>

    <script>
        // Fetch data and update the tank visualization
        function fetchData() {
            fetch('?fetch_data=true')
                .then(response => response.json())
                .then
