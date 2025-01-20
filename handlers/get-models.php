<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');
header('Content-Type: application/json');

try {
    if (!isset($_GET['brand']) || empty($_GET['brand'])) {
        throw new Exception('Brand parameter is required and cannot be empty');
    }

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT DISTINCT model FROM listings WHERE brand = :brand AND model IS NOT NULL AND model != '' ORDER BY model ASC";
    $stmt = $connect->prepare($query);
    $stmt->execute(['brand' => $_GET['brand']]);

    $models = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Переконуємося що повертаємо масив
    if (!is_array($models)) {
        $models = [];
    }
    
    // Фільтруємо пусті значення і конвертуємо в масив
    $models = array_values(array_filter($models, function($model) {
        return !empty($model) && is_string($model);
    }));

    error_log("Models for brand {$_GET['brand']}: " . print_r($models, true));
    echo json_encode($models);

} catch (Exception $e) {
    error_log("Error in get-models.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
