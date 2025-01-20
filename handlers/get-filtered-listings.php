<?php
require_once '../vendor/autoload.php';
$config = include('../config.php');

header('Content-Type: application/json');

try {
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Базовий запит
    $query = "SELECT * FROM listings WHERE 1=1";
    $params = [];

    // Додаємо фільтри, якщо вони є
    if (!empty($_GET['marka'])) {
        $query .= " AND brand = :brand";
        $params[':brand'] = $_GET['marka'];
    }

    if (!empty($_GET['model'])) {
        $query .= " AND model = :model";
        $params[':model'] = $_GET['model'];
    }

    if (!empty($_GET['generacja'])) {
        $query .= " AND generation = :generation";
        $params[':generation'] = $_GET['generacja'];
    }

    if (!empty($_GET['typ_nadwozia'])) {
        $query .= " AND body_type = :body_type";
        $params[':body_type'] = $_GET['typ_nadwozia'];
    }

    // Фільтр за типом палива
    if (!empty($_GET['rodzaj_paliwa'])) {
        $query .= " AND fuel_type = :fuel_type";
        $params[':fuel_type'] = $_GET['rodzaj_paliwa'];
    }

    // Фільтр за країною походження
    if (!empty($_GET['kraj_pochodzenia'])) {
        $query .= " AND kraj_pochodzenia = :kraj_pochodzenia";
        $params[':kraj_pochodzenia'] = $_GET['kraj_pochodzenia'];
    }

    // Фільтр за станом пошкодження
    if (isset($_GET['damaged']) && $_GET['damaged'] !== '') {
        $query .= " AND damaged = :damaged";
        $params[':damaged'] = (int)$_GET['damaged'];
    }

    // Фільтрація за ціною
    if (!empty($_GET['cena_od'])) {
        $cenaOd = floatval($_GET['cena_od']);
        // Для netto цін враховуємо ПДВ
        $query .= " AND (
            (price_type = 'brutto' AND price >= :cena_od) OR 
            (price_type = 'netto' AND price * 1.23 >= :cena_od)
        )";
        $params[':cena_od'] = $cenaOd;
    }

    if (!empty($_GET['cena_do'])) {
        $cenaDo = floatval($_GET['cena_do']);
        // Для netto цін враховуємо ПДВ
        $query .= " AND (
            (price_type = 'brutto' AND price <= :cena_do) OR 
            (price_type = 'netto' AND price * 1.23 <= :cena_do)
        )";
        $params[':cena_do'] = $cenaDo;
    }

    // Фільтрація за роком виробництва
    if (!empty($_GET['rok_od'])) {
        $query .= " AND prod_year >= :rok_od";
        $params[':rok_od'] = intval($_GET['rok_od']);
    }

    if (!empty($_GET['rok_do'])) {
        $query .= " AND prod_year <= :rok_do";
        $params[':rok_do'] = intval($_GET['rok_do']);
    }

    // Фільтр за пробігом від
    if (!empty($_GET['przebieg_od'])) {
        $query .= " AND mileage >= :przebieg_od";
        $params[':przebieg_od'] = $_GET['przebieg_od'];
    }

    // Фільтр за пробігом до
    if (!empty($_GET['przebieg_do'])) {
        $query .= " AND mileage <= :przebieg_do";
        $params[':przebieg_do'] = $_GET['przebieg_do'];
    }

    // Фільтр за статусами
    if (!empty($_GET['status'])) {
        $statuses = json_decode($_GET['status'], true);
        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                switch ($status) {
                    case 'vin':
                        $query .= " AND has_vin = 1";
                        break;
                    case 'rejestracyjny':
                        $query .= " AND has_registration = 1";
                        break;
                    case 'zarejestrowany':
                        $query .= " AND registered_in_poland = 1";
                        break;
                    case 'wlasciciel':
                        $query .= " AND first_owner = 1";
                        break;
                    case 'bezwypadkowy':
                        $query .= " AND accident_free = 1";
                        break;
                    case 'serwisowany':
                        $query .= " AND dealer_serviced = 1";
                        break;
                    case 'zabytek':
                        $query .= " AND registered_as_historic = 1";
                        break;
                    case 'tuning':
                        $query .= " AND tuning = 1";
                        break;
                    case 'homologacja':
                        $query .= " AND truck_approval = 1";
                        break;
                }
            }
        }
    }

    // Додаємо сортування
    $query .= " ORDER BY listing_id DESC";

    $stmt = $connect->prepare($query);
    $stmt->execute($params);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($listings);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
