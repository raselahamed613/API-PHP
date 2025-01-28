<?php
// Secret key for verifying the token
define('SECRET_KEY', 'myIoT'); // Replace with your actual secret key

// Decode and validate JWT token
function validateJWT($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false; // Invalid structure
    }

    $header = json_decode(base64_decode($parts[0]), true);
    $payload = json_decode(base64_decode($parts[1]), true);
    $signature_provided = $parts[2];

    if (isset($payload['exp']) && time() > $payload['exp']) {
        return false; // Token expired
    }

    $base64_url_header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64_url_payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', "$base64_url_header.$base64_url_payload", SECRET_KEY, true);
    $base64_url_signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    if ($base64_url_signature !== $signature_provided) {
        return false; // Invalid signature
    }

    return $payload; // Return payload if valid
}

// API to validate token and trigger pump
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract token from the Authorization header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        echo json_encode(['success' => false, 'message' => 'Token is required']);
        exit;
    }

    $token = $matches[1]; // Extract the token from the header

    $payload = validateJWT($token);

    if (!$payload) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
        exit;
    }

    // Token is valid, hit the URL
    $url = 'http://103.115.255.11:8085/lock_set?state=1';
    $response = file_get_contents($url);

    echo json_encode([
        'success' => true,
        'message' => 'Pump activated',
        'response' => $response,
        'data' => $payload // Optionally return token payload
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
