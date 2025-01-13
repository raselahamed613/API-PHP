<?php
// require 'db_connection.php';
//DB Connection

$servername = "localhost";

$username = "root"; // your database username

$password = "Rasel@24"; // your database password

$dbname = "pump_data";



// Create connection

$conn = new mysqli($servername, $username, $password, $dbname);

///////////////
// Secret key for JWT
$secret_key = "myIoT";

// Function to validate and decode JWT
function decodeJWT($jwt, $secret_key)
{
    [$base64Header, $base64Payload, $base64Signature] = explode('.', $jwt);
    $expectedSignature = base64_encode(hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key, true));
    if (!hash_equals($expectedSignature, $base64Signature)) {
        return false;
    }
    return json_decode(base64_decode($base64Payload), true);
}

function getBearerToken() {
    $headers = apache_request_headers(); // Get all headers

    if (isset($headers['Authorization'])) {
        // Extract the Bearer token from the Authorization header
        $matches = [];
        preg_match('/Bearer (.+)/', $headers['Authorization'], $matches);
        return $matches[1] ?? null; // Return the token, or null if not found
    }
    return null; // Return null if Authorization header is not present
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //$jwt = $_POST['token'] ?? '';
    $jwt = getBearerToken() ?? '';
   // print_r($jwt);
    if (empty($jwt)) {
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

    // Fetch sensor and motor statuses
    // $statusQuery = "SELECT component, status FROM sensor_data WHERE device_id = ?";
    // $statusQuery = "SELECT lowSensor, midLowSensor, midSensor, fullSensor, motorState FROM sensor_data WHERE device_id = ?";
    $statusQuery = "SELECT lowSensor, midLowSensor, midSensor, fullSensor, motorState 
                FROM sensor_data 
                WHERE device_id = ? 
                ORDER BY id DESC 
                LIMIT 1";

    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $statusResult = $stmt->get_result();

    $statuses = [];
    // while ($row = $statusResult->fetch_assoc()) {
    //     $statuses[$row['component']] = $row['status'];
    // }
    if ($row = $statusResult->fetch_assoc()) {
        $statuses['lowsensor'] = $row['lowSensor'];
        $statuses['midlow_sensor'] = $row['midLowSensor'];
        $statuses['mid_sensor'] = $row['midSensor'];
        $statuses['fullsensor'] = $row['fullSensor'];
        $statuses['motor_status'] = $row['motorState'];
    }else {
        $lowSensor = "unknown";
        $midLowSensor = "unknown";
        $midSensor = "unknown";
        $fullSensor = "unknown";
        $motorStatus = "unknown";
    }
    // Fetch motor on/off times
    // $motorQuery = "SELECT on_time, off_time FROM motor_activity WHERE device_id = ? AND on_time >= NOW() - INTERVAL 1 DAY";
    // $stmt = $conn->prepare($motorQuery);
    // $stmt->bind_param("s", $device_id);
    // $stmt->execute();
    // $motorResult = $stmt->get_result();

    // $totalOnTime = 0;
    // $motorLastOnTime = null;

    // while ($row = $motorResult->fetch_assoc()) {
    //     $onTime = strtotime($row['on_time']);
    //     $offTime = $row['off_time'] ? strtotime($row['off_time']) : time();
    //     $totalOnTime += ($offTime - $onTime);

    //     if (!$motorLastOnTime || $onTime > strtotime($motorLastOnTime)) {
    //         $motorLastOnTime = $row['on_time'];
    //     }
    // }

    // // Calculate average on time
    // $averageOnTime = $totalOnTime > 0 ? gmdate("H:i:s", $totalOnTime / $motorResult->num_rows) : "00:00:00";

    // // Convert total on time to HH:MM:SS
    // $last24HourTotalOnTime = gmdate("H:i:s", $totalOnTime);
    //dume value
    $motorLastOnTime = 5;
    $averageOnTime = 10;
    $last24HourTotalOnTime  = 1;
    //////
    // Prepare the response
    // $response = [
    //     'status' => 'success',
    //     'message' => 'Device data retrieved successfully.',
    //     'data' => [
    //         'lowsensor' => $statuses['lowsensor'] ?? 'unknown',
    //         'midlow_sensor' => $statuses['midlow_sensor'] ?? 'unknown',
    //         'mid_sensor' => $statuses['mid_sensor'] ?? 'unknown',
    //         'fullsensor' => $statuses['fullsensor'] ?? 'unknown',
    //         'motor_status' => $statuses['motor_status'] ?? 'unknown',
    //         'motor_last_on_time' => $motorLastOnTime ?? 'N/A',
    //         'average_on_time' => $averageOnTime,
    //         'last_24_hour_total_on_time' => $last24HourTotalOnTime
    //     ]
    // ];
    $data = [

        'data' => [

            'MeterInfo' => [

                [

                    'Key' => 'Low Sensor',

                    'Value' => $statuses['lowsensor'] ?? 'unknown',

                    'Bgcolor' => '#0000FF', // Add background color if needed

                    'Textcolor' => '#7393B3', // Add text color if needed

                ],

                [

                    'Key' => 'Mid Low Sensor',

                    'Value' => $statuses['midlow_sensor'] ?? 'unknown',

                    'Bgcolor' => '#0000FF', // Add background color if needed

                    'Textcolor' => '#7393B3', // Add text color if needed

                ],

                [

                    'Key' => 'Mid Sensor',

                    'Value' => $statuses['mid_sensor'] ?? 'unknown',

                    'Bgcolor' => '#0000FF', // Add background color if needed

                    'Textcolor' => '#7393B3', // Add text color if needed

                ],

                [

                    'Key' => 'Full Sensor',

                    'Value' => $statuses['fullsensor'] ?? 'unknown',

                    'Bgcolor' => '#0000FF', // Add background color if needed

                    'Textcolor' => '#7393B3', // Add text color if needed

                ],

                [

                    'Key' => 'Motor Status',

                    'Value' => $statuses['motor_status'] ?? 'unknown',

                    'Bgcolor' => '#0000FF', // Add background color if needed

                    'Textcolor' => '#7393B3', // Add text color if needed

                ],

                

                'motor_last_on_time' => $motorLastOnTime ?? 'N/A',

                'average_on_time' => $averageOnTime,

                'last_24_hour_total_on_time' => $last24HourTotalOnTime

            ],

        ],

    ];
    

    echo json_encode($data);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>