<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ustawienie nagłówka odpowiedzi jako JSON
header('Content-Type: application/json');

// Sprawdzenie czy ciasteczko z tokenem istnieje
if (!isset($_COOKIE['token'])) {
    http_response_code(401); // Ustawienie kodu odpowiedzi na 401 - brak autoryzacji
    echo json_encode(['error' => 'Unauthorized']); // Zwrócenie błędu w formacie JSON
    exit(); // Zakończenie skryptu
}

try {
    // Pobranie danych z żądania
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Sprawdzenie czy nowa nazwa jest podana
    if (!isset($data['newName']) || empty($data['newName'])) {
        http_response_code(400); // Ustawienie kodu odpowiedzi na 400 - zły request
        echo json_encode(['error' => 'New name is required']); // Zwrócenie błędu w formacie JSON
        exit(); // Zakończenie skryptu
    }

    // Rozkodowanie tokenu JWT, aby uzyskać user_id
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;

    // Nawiązanie połączenia z bazą danych
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ustawienie trybu raportowania błędów

    // Aktualizacja nazwy użytkownika w bazie danych
    $query = "UPDATE user SET user_name = ? WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$data['newName'], $user_id]);

    // Generowanie nowego tokenu JWT z zaktualizowaną nazwą użytkownika
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

    // Ustawienie nowego ciasteczka z tokenem JWT
    setcookie("token", $token, [
        'expires' => time() + 3600,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => false,
        'samesite' => 'Lax'
    ]);

    // Zwrócenie potwierdzenia sukcesu operacji
    echo json_encode([
        'success' => true,
        'newName' => $data['newName']
    ]);

} catch (Exception $e) {
    http_response_code(500); // Ustawienie kodu odpowiedzi na 500 - błąd serwera
    echo json_encode(['error' => $e->getMessage()]); // Zwrócenie błędu w formacie JSON
}