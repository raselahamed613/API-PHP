<?php
require 'db_connection.php';

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
    $statusQuery = "SELECT component, status FROM device_status WHERE device_id = ?";
    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param("s", $device_id);
    $stmt->execute();za
    $statusResult = $stmt->get_result();

    $statuses = [];
    while ($row = $statusResult->fetch_assoc()) {
        $statuses[$row['component']] = $row['status'];
    }

    // Fetch motor on/off times
    $motorQuery = "SELECT on_time, off_time FROM motor_activity WHERE device_id = ? AND on_time >= NOW() - INTERVAL 1 DAY";
    $stmt = $conn->prepare($motorQuery);
    $stmt->bind_param("s", $device_id);
    $stmt->execute();
    $motorResult = $stmt->get_result();

    $totalOnTime = 0;
    $motorLastOnTime = null;

    while ($row = $motorResult->fetch_assoc()) {
        $onTime = strtotime($row['on_time']);
        $offTime = $row['off_time'] ? strtotime($row['off_time']) : time();
        $totalOnTime += ($offTime - $onTime);

        if (!$motorLastOnTime || $onTime > strtotime($motorLastOnTime)) {
            $motorLastOnTime = $row['on_time'];
        }
    }

    // Calculate average on time
    $averageOnTime = $totalOnTime > 0 ? gmdate("H:i:s", $totalOnTime / $motorResult->num_rows) : "00:00:00";

    // Convert total on time to HH:MM:SS
    $last24HourTotalOnTime = gmdate("H:i:s", $totalOnTime);

    // Prepare the response
    $response = [
        'status' => 'success',
        'message' => 'Device data retrieved successfully.',
        'data' => [
            'lowsensor' => $statuses['lowsensor'] ?? 'unknown',
            'midlow' => $statuses['midlow'] ?? 'unknown',
            'mid_sensor' => $statuses['mid_sensor'] ?? 'unknown',
            'fullsensor' => $statuses['fullsensor'] ?? 'unknown',
            'motor_status' => $statuses['motor_status'] ?? 'unknown',
            'motor_last_on_time' => $motorLastOnTime ?? 'N/A',
            'average_on_time' => $averageOnTime,
            'last_24_hour_total_on_time' => $last24HourTotalOnTime
        ]
    ];

    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
