<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ustawienie nagłówka zwracanej odpowiedzi jako JSON
header('Content-Type: application/json');

// Sprawdzenie czy ciasteczko z tokenem istnieje
if (!isset($_COOKIE['token'])) {
    http_response_code(401); // Ustawienie kodu odpowiedzi na 401 - brak autoryzacji
    echo json_encode(['error' => 'Unauthorized']); // Zwrócenie błędu w formacie JSON
    exit(); // Zakończenie skryptu
}

try {
    // Sprawdzenie, czy plik awatara został przesłany
    if (!isset($_FILES['avatar'])) {
        http_response_code(400); // Ustawienie kodu odpowiedzi na 400 - błędne żądanie
        echo json_encode(['error' => 'No file uploaded']); // Zwrócenie błędu w formacie JSON
        exit(); // Zakończenie skryptu
    }

    $file = $_FILES['avatar']; // Pobranie informacji o pliku

    // Sprawdzenie, czy wystąpił błąd podczas przesyłania pliku
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400); // Ustawienie kodu odpowiedzi na 400 - błędne żądanie
        echo json_encode(['error' => 'File upload failed']); // Zwrócenie błędu w formacie JSON
        exit(); // Zakończenie skryptu
    }

    // Dekodowanie tokenu JWT w celu uzyskania identyfikatora użytkownika
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id; // Pobranie identyfikatora użytkownika z tokenu

    // Kodowanie obrazu do formatu base64
    $imageData = base64_encode(file_get_contents($file['tmp_name']));
    $src = 'data:' . mime_content_type($file['tmp_name']) . ';base64,' . $imageData;

    // Nawiązanie połączenia z bazą danych
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ustawienie trybu raportowania błędów

    // Aktualizacja URL awatara użytkownika w bazie danych
    $query = "UPDATE user SET avatar_url = ? WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$src, $user_id]); // Wykonanie zapytania

    // Zwrócenie odpowiedzi w formacie JSON z nowym URL awatara
    echo json_encode([
        'success' => true,
        'avatar_url' => $src
    ]);

} catch (Exception $e) {
    http_response_code(500); // Ustawienie kodu odpowiedzi na 500 - błąd serwera
    echo json_encode(['error' => $e->getMessage()]); // Zwrócenie błędu w formacie JSON
}