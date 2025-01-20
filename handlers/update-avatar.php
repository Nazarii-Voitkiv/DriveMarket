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
    if (!isset($_FILES['avatar'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit();
    }

    $file = $_FILES['avatar'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File upload failed']);
        exit();
    }

    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;

    $imageData = base64_encode(file_get_contents($file['tmp_name']));
    $src = 'data:' . mime_content_type($file['tmp_name']) . ';base64,' . $imageData;

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "UPDATE user SET avatar_url = ? WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$src, $user_id]);

    echo json_encode([
        'success' => true,
        'avatar_url' => $src
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}