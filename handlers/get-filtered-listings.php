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

    // Podstawowe zapytanie
    $query = "SELECT * FROM listings WHERE 1=1";
    $params = [];

    // Dodaj filtry, jeśli są
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

    // Filtr według rodzaju paliwa
    if (!empty($_GET['rodzaj_paliwa'])) {
        $query .= " AND fuel_type = :fuel_type";
        $params[':fuel_type'] = $_GET['rodzaj_paliwa'];
    }

    // Filtr według kraju pochodzenia
    if (!empty($_GET['kraj_pochodzenia'])) {
        $query .= " AND kraj_pochodzenia = :kraj_pochodzenia";
        $params[':kraj_pochodzenia'] = $_GET['kraj_pochodzenia'];
    }

    // Filtr według stanu uszkodzenia
    if (isset($_GET['damaged']) && $_GET['damaged'] !== '') {
        $query .= " AND damaged = :damaged";
        $params[':damaged'] = (int)$_GET['damaged'];
    }

    // Filtracja według ceny
    if (!empty($_GET['cena_od'])) {
        $cenaOd = floatval($_GET['cena_od']);
        // Dla cen netto uwzględniamy VAT
        $query .= " AND (
            (price_type = 'brutto' AND price >= :cena_od) OR 
            (price_type = 'netto' AND price * 1.23 >= :cena_od)
        )";
        $params[':cena_od'] = $cenaOd;
    }

    if (!empty($_GET['cena_do'])) {
        $cenaDo = floatval($_GET['cena_do']);
        // Dla cen netto uwzględniamy VAT
        $query .= " AND (
            (price_type = 'brutto' AND price <= :cena_do) OR 
            (price_type = 'netto' AND price * 1.23 <= :cena_do)
        )";
        $params[':cena_do'] = $cenaDo;
    }

    // Filtracja według roku produkcji
    if (!empty($_GET['rok_od'])) {
        $query .= " AND prod_year >= :rok_od";
        $params[':rok_od'] = intval($_GET['rok_od']);
    }

    if (!empty($_GET['rok_do'])) {
        $query .= " AND prod_year <= :rok_do";
        $params[':rok_do'] = intval($_GET['rok_do']);
    }

    // Filtr według przebiegu od
    if (!empty($_GET['przebieg_od'])) {
        $query .= " AND mileage >= :przebieg_od";
        $params[':przebieg_od'] = $_GET['przebieg_od'];
    }

    // Filtr według przebiegu do
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

    // Dodaj sortowanie na podstawie parametru 'sort'
    if (isset($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'wyróżnione':
                // Sortuj według wyróżnionych malejąco
                $query .= " ORDER BY is_featured DESC";
                break;
            case 'cena_asc':
                // Sortuj według ceny rosnąco
                $query .= " ORDER BY price ASC";
                break;
            case 'cena_desc':
                // Sortuj według ceny malejąco
                $query .= " ORDER BY price DESC";
                break;
            case 'przebieg_asc':
                // Sortuj według przebiegu rosnąco
                $query .= " ORDER BY mileage ASC";
                break;
            case 'przebieg_desc':
                // Sortuj według przebiegu malejąco
                $query .= " ORDER BY mileage DESC";
                break;
            case 'moc_asc':
                // Sortuj według mocy silnika rosnąco
                $query .= " ORDER BY engine_power ASC";
                break;
            case 'moc_desc':
                // Sortuj według mocy silnika malejąco
                $query .= " ORDER BY engine_power DESC";
                break;
            default:
                // Domyślne sortowanie po ID ogłoszenia malejąco
                $query .= " ORDER BY listing_id DESC";
                break;
        }
    } else {
        // Domyślne sortowanie po ID ogłoszenia malejąco, jeśli brak parametru 'sort'
        $query .= " ORDER BY listing_id DESC";
    }

    // Przygotowanie i wykonanie zapytania SQL
    $stmt = $connect->prepare($query);
    $stmt->execute($params);

    // Pobierz wszystkie wyniki jako tablicę asocjacyjną
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Wyślij wyniki jako odpowiedź w formacie JSON
    echo json_encode($listings);

} catch (Exception $e) {
    // Obsługa błędów: ustaw kod odpowiedzi HTTP na 500 i zwróć błąd jako JSON
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
