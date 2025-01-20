<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

$rawData = file_get_contents('php://input');
error_log('Raw request data: ' . $rawData);
$data = json_decode($rawData, true);
error_log('Decoded request data: ' . print_r($data, true));

if (!isset($_COOKIE['token'])) {
    error_log('No token found in cookies');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    error_log('Decoded token: ' . print_r($decoded, true));
    $user_id = $decoded->data->user_id;

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT user_password FROM user WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$user_id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (empty($user['user_password'])) {
            if (!isset($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Password is required']);
                exit();
            }

            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
            $statement = $connect->prepare($update_query);
            $statement->execute([$hashed_password, $user_id]);

            echo json_encode(['message' => 'Password created successfully']);
        } else {
            if (!isset($data['oldPassword']) || !isset($data['newPassword'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Both old and new passwords are required']);
                exit();
            }

            if (!password_verify($data['oldPassword'], $user['user_password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Incorrect old password']);
                exit();
            }

            $hashed_password = password_hash($data['newPassword'], PASSWORD_DEFAULT);
            $update_query = "UPDATE user SET user_password = ? WHERE user_id = ?";
            $statement = $connect->prepare($update_query);
            $statement->execute([$hashed_password, $user_id]);

            echo json_encode(['message' => 'Password updated successfully']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }

} catch (Exception $e) {
    error_log('Error in update-password.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 