<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Ustawienie nagłówków odpowiedzi HTTP
header('Content-Type: application/json'); // Typ danych zwracanych jako JSON
header('Cache-Control: no-store, no-cache, must-revalidate'); // Wyłączenie cache'owania po stronie przeglądarki
header('Pragma: no-cache'); // Dodatkowe wyłączenie cache'owania

// Sprawdzenie czy ciasteczko z tokenem istnieje
if (!isset($_COOKIE['token'])) {
    http_response_code(401); // Ustawienie kodu odpowiedzi na 401 - brak autoryzacji
    echo json_encode(['error' => 'Unauthorized']); // Zwrócenie błędu w formacie JSON
    exit(); // Zakończenie wykonywania skryptu
}

try {
    // Odszyfrowanie tokenu JWT
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id; // Pobranie identyfikatora użytkownika z tokenu

    // Nawiązanie połączenia z bazą danych
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ustawienie trybu raportowania błędów

    // Przygotowanie i wykonanie zapytania SQL
    $query = "SELECT user_name, user_email, user_password, avatar_url FROM user WHERE user_id = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$user_id]); // Wykonanie zapytania z podanym identyfikatorem użytkownika
    $user = $statement->fetch(PDO::FETCH_ASSOC); // Pobranie danych użytkownika jako tablicy asocjacyjnej

    if ($user) {
        // Zwrócenie danych użytkownika w formacie JSON
        echo json_encode([
            'user_name' => $user['user_name'],
            'user_email' => $user['user_email'],
            'has_password' => !empty($user['user_password']), // Sprawdzenie czy użytkownik ma ustawione hasło
            'avatar_url' => $user['avatar_url']
        ]);
    } else {
        http_response_code(404); // Ustawienie kodu odpowiedzi na 404 - użytkownik nie znaleziony
        echo json_encode(['error' => 'User not found']); // Zwrócenie błędu w formacie JSON
    }

} catch (Exception $e) {
    http_response_code(500); // Ustawienie kodu odpowiedzi na 500 - błąd serwera
    echo json_encode(['error' => $e->getMessage()]); // Zwrócenie błędu w formacie JSON
}