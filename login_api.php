
<?php
require 'db_connection.php';
// require '../config/db_connection.php';
require 'vendor/autoload.php'; // Include Composer autoload for JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Secret key for JWT
$secret_key = "myIoT";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve POST data
    $user_input = $_POST['user_input'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($user_input) || empty($password)) {
        header("Location: login.php?message=" . urlencode('Both fields are required.'));
        exit;
    }

    // Check if the user input is email or mobile
    $is_email = filter_var($user_input, FILTER_VALIDATE_EMAIL);
    
    $sql = $is_email
    ? "SELECT id, email, password, device_id FROM users WHERE email = ?"
    : "SELECT id, mobile, password, device_id FROM users WHERE mobile = ?";
    // $sql = $is_email
    //     ? "SELECT id, email, password, device_id FROM users WHERE email = :user_input"
    //     : "SELECT id, mobile, password, device_id FROM users WHERE mobile = :user_input";

    // $stmt = $pdo->prepare($sql);
    // $stmt->execute([':user_input' => $user_input]);
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // die(json_encode(['error' => 'Failed to prepare the SQL statement: ' . $conn->error]));
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare the SQL statement.']);
        exit;
    }

    // Bind the parameter
    $stmt->bind_param("s", $user_input);

    // Execute the query
    $stmt->execute();

    // if ($stmt->rowCount() > 0) {
    //     $user = $stmt->fetch(PDO::FETCH_ASSOC);
      // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Generate JWT
            $payload = [
                "iss" => "http://localhost", // Issuer
                "aud" => "http://localhost", // Audience
                "iat" => time(),             // Issued at
                "exp" => time() + 60,
                // "exp" => time() + 3600,      // Expiration time (1 hour)
                "data" => [
                    "id" => $user['id'],
                    "device_id" => $user['device_id']
                ]
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            // Redirect with success message
            // header("Location: login.php?message=" . urlencode("Login successful! Your token: $jwt"));
            echo json_encode(['status' => 'success', 'message' => 'Login successful.', 'token' => $jwt]);
        } else {
            // header("Location: login.php?message=" . urlencode('Invalid password.'));
            echo json_encode(['status' => 'error', 'message' => 'Invalid password.']);
        }
    } else {
        // header("Location: login.php?message=" . urlencode('User not found.'));
        echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    }
} else {
    // header("Location: login.php?message=" . urlencode('Invalid request method.'));
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
