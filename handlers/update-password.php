<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Set response headers for JSON and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Retrieve raw request data and log it
$rawData = file_get_contents('php://input');
error_log('Raw request data: ' . $rawData);

// Decode JSON request data and log it
$data = json_decode($rawData, true);
error_log('Decoded request data: ' . print_r($data, true));

// Check for JWT token in cookies
if (!isset($_COOKIE['token'])) {
    error_log('No token found in cookies');
    http_response_code(401); // Unauthorized error
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Decode JWT token to get user_id
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    error_log('Decoded token: ' . print_r($decoded, true));
    $user_id = $decoded->data->user_id;

    // Connect to the database
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode for PDO errors

    // Query to fetch user password from the database
    $query = "SELECT user_password FROM user WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$user_id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if ($user) {
        // If user has no password, create a new password
        if (empty($user['user_password'])) {
            if (!isset($data['password'])) {
                http_response_code(400); // Bad request error
                echo json_encode(['error' => 'Password is required']);
                exit();
            }

            // Hash the new password and update it in the database
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
            $statement = $connect->prepare($update_query);
            $statement->execute([$hashed_password, $user_id]);

            echo json_encode(['message' => 'Password created successfully']);
        } else {
            // If user has a password, update it
            if (!isset($data['oldPassword']) || !isset($data['newPassword'])) {
                http_response_code(400); // Bad request error
                echo json_encode(['error' => 'Both old and new passwords are required']);
                exit();
            }

            // Verify the old password
            if (!password_verify($data['oldPassword'], $user['user_password'])) {
                http_response_code(400); // Bad request error
                echo json_encode(['error' => 'Incorrect old password']);
                exit();
            }

            // Hash the new password and update it in the database
            $hashed_password = password_hash($data['newPassword'], PASSWORD_DEFAULT);
            $update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
            $statement = $connect->prepare($update_query);
            $statement->execute([$hashed_password, $user_id]);

            echo json_encode(['message' => 'Password updated successfully']);
        }
    } else {
        http_response_code(404); // Not found error
        echo json_encode(['error' => 'User not found']);
    }

} catch (Exception $e) {
    // Log errors and return a server error response
    error_log('Error in update-password.php: ' . $e->getMessage());
    http_response_code(500); // Internal server error
    echo json_encode(['error' => $e->getMessage()]);
}