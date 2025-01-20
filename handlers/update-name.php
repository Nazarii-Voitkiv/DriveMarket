<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');

if (!isset($_COOKIE['token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['newName']) || empty($data['newName'])) {
        http_response_code(400);
        echo json_encode(['error' => 'New name is required']);
        exit();
    }

    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "UPDATE user SET user_name = ? WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$data['newName'], $user_id]);

    $token = JWT::encode(
        [
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => [
                'user_id' => $user_id,
                'user_name' => $data['newName']
            ]
        ],
        $config['jwt_key'],
        'HS256'
    );

    setcookie("token", $token, [
        'expires' => time() + 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);

    echo json_encode([
        'success' => true,
        'newName' => $data['newName']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 