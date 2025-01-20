<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!isset($_COOKIE['token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT user_name, user_email, user_password, avatar_url FROM user WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$user_id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'user_name' => $user['user_name'],
            'user_email' => $user['user_email'],
            'has_password' => !empty($user['user_password']),
            'avatar_url' => $user['avatar_url']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}