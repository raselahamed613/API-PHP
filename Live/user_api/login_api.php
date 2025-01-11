<?php

require 'db_connection.php';

// Secret key for signing the token
$secret_key = "myIoT";


// Function to generate a JWT
function generateJWT($payload, $secret_key)
{
    // Encode header
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $base64Header = base64_encode($header);

    // Encode payload
    $base64Payload = base64_encode(json_encode($payload));

    // Create signature
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key, true);
    $base64Signature = base64_encode($signature);

    // Combine header, payload, and signature
    return "$base64Header.$base64Payload.$base64Signature";
}

// Function to validate JWT
function validateJWT($jwt, $secret_key)
{
    // Split the token into parts
    [$base64Header, $base64Payload, $base64Signature] = explode('.', $jwt);

    // Recreate the signature
    $expectedSignature = base64_encode(hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key, true));

    // Verify if the signatures match
    return hash_equals($expectedSignature, $base64Signature);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $user_input = $_POST['user_input'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($user_input) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Both fields are required.']);
        exit;
    }

    // Check if the user input is email or mobile number
    $is_email = filter_var($user_input, FILTER_VALIDATE_EMAIL);

    // Prepare SQL query based on input type
    $sql = $is_email
        ? "SELECT id, email, password, device_id FROM users WHERE email = ?"
        : "SELECT id, mobile, password, device_id FROM users WHERE mobile = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
        exit;
    }

    // Bind the parameter and execute the query
    $stmt->bind_param("s", $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Create JWT payload
            $payload = [
                "iss" => "http://localhost", // Issuer
                "aud" => "http://localhost", // Audience
                "iat" => time(),             // Issued at
                "exp" => time() + 3600,      // Expiration time (1 hour)
                "data" => [
                    "id" => $user['id'],
                    "device_id" => $user['device_id']
                ]
            ];

            // Generate JWT
            $jwt = generateJWT($payload, $secret_key);

            // Respond with the JWT
            echo json_encode(['status' => 'success', 'message' => 'Login successful.', 'token' => $jwt]);
        } else {
            // Invalid password
            echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
        }
    } else {
        // User not found
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
} else {
    // Invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

?>
