<?php

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
    //$headers = apache_request_headers(); // Get all headers
    $headers = getallheaders();
    //if (isset($headers['Authorization'])) {
        // Extract the Bearer token from the Authorization header
      //  $matches = [];
        //preg_match('/Bearer (.+)/', $headers['Authorization'], $matches);
        //return $matches[1] ?? null; // Return the token, or null if not found
   // }
   if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null; // Return null if Authorization header is not present
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jwt = $_POST['token'] ?? '';
    //$jwt = getBearerToken() ?? '';
    
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

    
   
    // Token is valid, make the API request
    $apiUrl = 'http://103.115.255.11:8090/lock_set?state=1';
    $response = makeApiRequest($apiUrl);
    //print_r($response);
    if ($response['success']) {
        echo json_encode(['success' => true, 'data' => $response['data']]);
    } else {
        echo json_encode(['success' => false, 'error' => $response['error']]);
    }
    //echo json_encode("print");
    
    

    //echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

// Function to make a GET request
function makeApiRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    
     // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => "cURL Error: $error"];
    }
    
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $decodedResponse = json_decode($response, true);
        
         if (json_last_error() === JSON_ERROR_NONE) {
            return ['success' => true, 'data' => $decodedResponse];
        } else {
            return ['success' => false, 'error' => 'Invalid JSON response from API'];
        }
        
    }else{
        return ['success' => false, 'error' => "HTTP Error: $httpCode"];
        }
    
}
?>
