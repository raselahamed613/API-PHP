<?php
// Secret key for verifying the token (should match the key used to sign it)
define('SECRET_KEY', 'myIoT');

// Decode and validate JWT token
function validateJWT($jwt) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false; // Invalid structure
    }

    $header = json_decode(base64_decode($parts[0]), true);
    $payload = json_decode(base64_decode($parts[1]), true);
    $signature_provided = $parts[2];

    if (!$header || !$payload) {
        echo json_encode(['success' => false, 'message' => 'Malformed token']);
        exit;
    }

    if (isset($payload['exp']) && time() > $payload['exp']) {
        echo json_encode(['success' => false, 'message' => 'Token expired']);
        exit;
    }

    $base64_url_header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64_url_payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $calculated_signature = hash_hmac('sha256', "$base64_url_header.$base64_url_payload", SECRET_KEY, true);
    $base64_url_calculated_signature = rtrim(strtr(base64_encode($calculated_signature), '+/', '-_'), '=');

    if ($base64_url_calculated_signature !== $signature_provided) {
        echo json_encode([
            'success' => false,
            'message' => 'Signature mismatch',
            'expected_signature' => $base64_url_calculated_signature,
            'provided_signature' => $signature_provided
        ]);
        exit;
    }

    return $payload;
}

// API to validate token and trigger pump
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';

    if (empty($token)) {
        echo json_encode(['success' => false, 'message' => 'Token is required']);
        exit;
    }

    $payload = validateJWT($token);

    if (!$payload) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
        exit;
    }

    $url = 'http://103.115.255.11:8085/lock_set?state=1';
    $response = file_get_contents($url);

    echo json_encode([
        'success' => true,
        'message' => 'Pump activated',
        'response' => $response,
        'data' => $payload
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
