<?php
require_once '../vendor/autoload.php';
$config = include('../config.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['marka']) || !isset($_GET['model'])) {
        throw new Exception('Не вказана марка або модель');
    }

    $marka = $_GET['marka'];
    $model = $_GET['model'];

    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Отримуємо унікальні генерації для вказаної марки та моделі
    $query = "SELECT DISTINCT generation FROM listings 
              WHERE brand = :marka 
              AND model = :model 
              AND generation IS NOT NULL 
              AND generation != '' 
              ORDER BY generation ASC";
              
    $stmt = $connect->prepare($query);
    $stmt->execute([
        ':marka' => $marka,
        ':model' => $model
    ]);

    $generations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($generations);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
