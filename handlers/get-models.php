<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');
header('Content-Type: application/json');

try {
    // Sprawdzenie, czy parametr 'brand' jest ustawiony i nie jest pusty
    if (!isset($_GET['brand']) || empty($_GET['brand'])) {
        throw new Exception('Brand parameter is required and cannot be empty');
    }

    // Nawiązanie połączenia z bazą danych
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pobieranie unikalnych modeli dla określonej marki
    $query = "SELECT DISTINCT model FROM listings WHERE brand = :brand AND model IS NOT NULL AND model != '' ORDER BY model ASC";
    $stmt = $connect->prepare($query);
    $stmt->execute(['brand' => $_GET['brand']]);

    // Pobieranie wyników jako tablica
    $models = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Upewnienie się, że zwracamy tablicę
    if (!is_array($models)) {
        $models = [];
    }
    
    // Filtrowanie pustych wartości i konwersja do tablicy
    $models = array_values(array_filter($models, function($model) {
        return !empty($model) && is_string($model);
    }));

    // Logowanie uzyskanych modeli do dziennika
    error_log("Models for brand {$_GET['brand']}: " . print_r($models, true));
    
    // Zwracanie wyników jako JSON
    echo json_encode($models);

} catch (Exception $e) {
    // Logowanie błędu i zwracanie kodu błędu 500
    error_log("Error in get-models.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
