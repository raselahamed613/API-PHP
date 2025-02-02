<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$servername = "localhost";
$username = "root";
$password = "Rasel@24";
$dbname = "pump_data";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// JWT Secret Key
$secret_key = "myIoT";

// Function to Decode JWT
function decodeJWT($jwt, $secret_key) {
    if (!$jwt) return false;
    
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;

    [$base64Header, $base64Payload, $base64Signature] = $parts;

    // Decode base64 values
    $expectedSignature = base64_encode(hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key, true));
    if (!hash_equals($expectedSignature, $base64Signature)) {
        return false;
    }

    return json_decode(base64_decode($base64Payload), true);
}

// Function to Retrieve Bearer Token from Headers
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jwt = $_POST['token'] ?? '';
    // $jwt = getBearerToken(); // Uncomment this if sending token via Bearer Authorization

    if (!$jwt) {
        echo json_encode(['status' => 'error', 'message' => 'Token is required.']);
        exit;
    }

    $decoded = decodeJWT($jwt, $secret_key);
    if (!$decoded || $decoded['exp'] < time()) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
        exit;
    }

    $device_id = $decoded['data']['device_id'] ?? null;
    if (!$device_id) {
        echo json_encode(['status' => 'error', 'message' => 'Device ID not found in token.']);
        exit;
    }

    // Fetch Sensor and Motor Statuses
    $statusQuery = "SELECT lowSensor, midLowSensor, midSensor, fullSensor, motorState 
                    FROM sensor_data 
                    WHERE device_id = ? 
                    ORDER BY id DESC 
                    LIMIT 1";

    $stmt = $conn->prepare($statusQuery);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Prepare Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $statusResult = $stmt->get_result();

    if ($row = $statusResult->fetch_assoc()) {
        $statuses = [
            'lowsensor' => $row['lowSensor'],
            'midlow_sensor' => $row['midLowSensor'],
            'mid_sensor' => $row['midSensor'],
            'fullsensor' => $row['fullSensor'],
            'motor_status' => $row['motorState']
        ];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No data found for this device.']);
        exit;
    }

    // Fetch Last Motor On Time
    $motorLastOnTimeQuery = "SELECT TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS timeAgo 
                             FROM sensor_data 
                             WHERE motorState = 1 AND device_id = ? 
                             ORDER BY timestamp DESC 
                             LIMIT 1";

    $stmt = $conn->prepare($motorLastOnTimeQuery);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'SQL Prepare Error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        $timeAgo = $row['timeAgo'];

        if ($timeAgo < 60) {
            $timeAgoText = "$timeAgo seconds ago";
        } elseif ($timeAgo < 3600) {
            $timeAgoText = floor($timeAgo / 60) . " min ago";
        } elseif ($timeAgo < 86400) {
            $timeAgoText = floor($timeAgo / 3600) . "h " . floor(($timeAgo % 3600) / 60) . "min ago";
        } else {
            $timeAgoText = floor($timeAgo / 86400) . " days ago";
        }
    } else {
        $timeAgoText = "N/A";
    }
    
    //AVG Time
    $averageOnTime = "10:00:00";
    //Last 24H Time
    $last24HourTotalOnTime  = "01:00:00";
    // Response Data
    $data = [
        'data' => [
            'MeterInfo' => [
                [
                    'Key' => 'Full Sensor',
                    'Value' => $statuses['fullsensor'],
                    'Bgcolor' => $statuses['fullsensor'] == 0 ? '#37A41B' : '#FF0000',
                    'Textcolor' => '#000000'
                ],
                [
                    'Key' => 'Mid Sensor',
                    'Value' => $statuses['mid_sensor'],
                    'Bgcolor' => $statuses['mid_sensor'] == 0 ? '#37A41B' : '#FF0000',
                    'Textcolor' => '#000000'
                ],
                [
                    'Key' => 'Mid Low Sensor',
                    'Value' => $statuses['midlow_sensor'],
                    'Bgcolor' => $statuses['midlow_sensor'] == 0 ? '#37A41B' : '#FF0000',
                    'Textcolor' => '#000000'
                ],
                [
                    'Key' => 'Low Sensor',
                    'Value' => $statuses['lowsensor'],
                    'Bgcolor' => $statuses['lowsensor'] == 0 ? '#37A41B' : '#00FF00',
                    'Textcolor' => '#000000'
                ]
                /*[
                    'Key' => 'Motor Status',
                    'Value' => $statuses['motor_status'],
                    'Bgcolor' => $statuses['motor_status'] == 0 ? '#37A41B' : '#FF0000',
                    'Textcolor' => '#00FF00'
                ]*/
            ],
            'motor_last_on_time' => $timeAgoText,
            'average_on_time' => $averageOnTime,
            'last_24_hour_total_on_time' => $last24HourTotalOnTime,
            'MotorStatus' =>[
             'Key' => 'Motor Status',
             'Value' => $statuses['motor_status'] ?? 'unknown',
             'Bgcolor' => '#FF0000', // Add background color if needed
             'Textcolor' => '#00FF00', // Add text color if needed
            ]
        ]
    ];

    echo json_encode($data);
}

$conn->close();
?>
