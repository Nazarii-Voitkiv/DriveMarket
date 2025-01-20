<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

error_log("Starting form processing...");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

if (!isset($_COOKIE['token'])) {
    header('Location: ../templates/login.php');
    exit();
}

try {
    $decoded = JWT::decode($_COOKIE['token'], new Key($config['jwt_key'], 'HS256'));
    $user_id = $decoded->data->user_id;
} catch (Exception $e) {
    header('Location: ../templates/login.php');
    exit();
}

$isEditing = false;
$listing_id = null;

if (isset($_POST['listing_id']) && is_numeric($_POST['listing_id'])) {
    $isEditing = true;
    $listing_id = (int)$_POST['listing_id'];
}

try {
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}",
        $config['db_username'],
        $config['db_password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Помилка підключення до БД: " . $e->getMessage());
}

function boolFromRadio($value) {
    error_log("Processing radio value: " . print_r($value, true));

    if ($value === null || $value === '') {
        error_log("Empty value, returning 0");
        return 0;
    }
    
    $result = (strtolower($value) === 'tak') ? 1 : 0;
    error_log("Converted to: " . $result);
    return $result;
}

// Функція для очищення тексту
function cleanText($text) {
    // Видаляємо HTML-сутності
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Замінюємо множинні пробіли на один
    $text = preg_replace('/\s+/', ' ', $text);
    // Видаляємо пробіли на початку і в кінці
    return trim($text);
}

// Логуємо POST дані
error_log("POST data received: " . print_r($_POST, true));

// Збираємо базові дані
$title = cleanText($_POST['title'] ?? '');
$description = cleanText($_POST['description'] ?? '');
$brand = $_POST['brand'] ?? '';
$model = $_POST['model'] ?? '';
$version = $_POST['version'] ?? '';
$generation = !empty($_POST['generation']) ? $_POST['generation'] : null;
$prod_year = (int)($_POST['prod_year'] ?? 0);
$mileage = (int)($_POST['mileage'] ?? 0);
$vin = $_POST['vin'] ?? '';
$reg_number = $_POST['reg_number'] ?? '';
$hide_reg_info = isset($_POST['hide_reg_info']) ? 1 : 0;

// Дата першої реєстрації
$first_reg_day = $_POST['first_reg_date_day'] ?? '';
$first_reg_month = $_POST['first_reg_date_month'] ?? '';
$first_reg_year = $_POST['first_reg_date_year'] ?? '';
$first_registration = null;

if (!empty($first_reg_year) && !empty($first_reg_month) && !empty($first_reg_day)) {
    // Валідація дати
    if (checkdate((int)$first_reg_month, (int)$first_reg_day, (int)$first_reg_year)) {
        $first_registration = sprintf(
            '%04d-%02d-%02d',
            (int)$first_reg_year,
            (int)$first_reg_month,
            (int)$first_reg_day
        );
    } else {
        header('Location: ../templates/create-listing.php?error=1&message=' . urlencode('Nieprawidłowa data pierwszej rejestracji'));
        exit();
    }
}

// Валідація VIN
if (!empty($vin) && strlen($vin) !== 17) {
    header('Location: ../templates/create-listing.php?error=1&message=' . urlencode('Nieprawidłowy numer VIN'));
    exit();
}

// Валідація пробігу
if ($mileage < 0) {
    header('Location: ../templates/create-listing.php?error=1&message=' . urlencode('Przebieg nie może być ujemny'));
    exit();
}

// Валідація реєстраційного номера
if (!empty($reg_number) && !preg_match('/^[A-Z0-9]{2,8}$/', strtoupper($reg_number))) {
    header('Location: ../templates/create-listing.php?error=1&message=' . urlencode('Nieprawidłowy numer rejestracyjny'));
    exit();
}

// Технічні характеристики
$engine_capacity = !empty($_POST['engine_capacity']) ? (int)$_POST['engine_capacity'] : null;
$fuel_type = $_POST['fuel_type'] ?? '';
$power = (int)($_POST['power'] ?? 0);
$transmission = $_POST['transmission'] ?? '';
$drive_type = !empty($_POST['drive_type']) ? $_POST['drive_type'] : null;
$body_type = $_POST['body_type'] ?? '';
$doors = (int)($_POST['doors'] ?? 0);
$seats = !empty($_POST['seats']) ? (int)$_POST['seats'] : null;
$color = $_POST['color'] ?? '';
$color_type = !empty($_POST['color_type']) ? $_POST['color_type'] : null;
$right_hand_drive = isset($_POST['right_hand_drive']) ? 1 : null;
$co2_emission = !empty($_POST['co2_emission']) ? (int)$_POST['co2_emission'] : null;

// Дані продавця
$seller_phone = !empty($_POST['seller_phone']) ? $_POST['seller_phone'] : null;
$seller_location = $_POST['seller_location'] ?? '';
$kraj_pochodzenia = !empty($_POST['kraj_pochodzenia']) ? $_POST['kraj_pochodzenia'] : null;

// Логуємо значення seller_location
error_log("seller_location from POST: " . print_r($seller_location, true));

// Радіо-кнопки (з логуванням)
error_log("Processing radio buttons...");
error_log("damaged raw value: " . ($_POST['damaged'] ?? 'not set'));
error_log("imported raw value: " . ($_POST['imported'] ?? 'not set'));

$damaged = boolFromRadio($_POST['damaged'] ?? null);
$imported = boolFromRadio($_POST['imported'] ?? null);
$pierwszy_wlasciciel = boolFromRadio($_POST['pierwszy_wlasciciel'] ?? null);
$bezwypadkowy = boolFromRadio($_POST['bezwypadkowy'] ?? null);
$zarejestrowany_pl = boolFromRadio($_POST['zarejestrowany_pl'] ?? null);
$serwisowany_aso = boolFromRadio($_POST['serwisowany_aso'] ?? null);
$zarejestrowany_zabytek = boolFromRadio($_POST['zarejestrowany_zabytek'] ?? null);
$tuning = boolFromRadio($_POST['tuning'] ?? null);
$homologacja = boolFromRadio($_POST['homologacja'] ?? null);

error_log("Converted values:");
error_log("damaged: " . $damaged);
error_log("imported: " . $imported);

// Гарантія
$gwarancja_dzien = !empty($_POST['gwarancja_dzien']) ? (int)$_POST['gwarancja_dzien'] : null;
$gwarancja_miesiac = !empty($_POST['gwarancja_miesiac']) ? (int)$_POST['gwarancja_miesiac'] : null;
$gwarancja_rok = !empty($_POST['gwarancja_rok']) ? (int)$_POST['gwarancja_rok'] : null;
$gwarancja_przebieg = !empty($_POST['gwarancja_przebieg']) ? (int)$_POST['gwarancja_przebieg'] : null;

// Обладнання
$allEquipment = $_POST['equipment'] ?? [];

// Списки категорій обладнання
$audioMultimediaList = [
    'apple_carplay', 'android_auto', 'bluetooth', 'radio', 'speakers',
    'usb', 'wireless_charging', 'navigation', 'sound_system', 'head_up',
    'touchscreen', 'voice_control', 'internet'
];

$komfortList = [
    'elektr_fotel_kier', 'podgrz_fotel_kier', 'regul_ledzw_kier',
    'fotele_przednie_went', 'siedzenie_pamiec', 'ogrzewane_siedzenia_tyl',
    'fotele_tyl_masaz', 'podlokietniki_tyl', 'kierownica_sportowa',
    'kierownica_radio', 'kolumna_kier_el', 'kierownica_wielofunkcyjna',
    'keyless_entry', 'uruchamianie_silnika', 'auto_kontrola_ogrzewania',
    'podgrzewana_szyba', 'elektryczne_szyby_przednie', 'przyciemniane_szyby',
    'dach_otwierany_pilot', 'klimatyzacja_tyl', 'elektr_fotel_pasaz',
    'podgrz_fotel_pasaz', 'regul_ledzw_pasaz', 'fotele_przednie_masaz',
    'sportowe_fotele', 'fotele_tylne_went', 'podlokietniki_przod',
    'kierownica_skorzana', 'zmiana_biegow_kier', 'cyfrowy_kluczyk',
    'dzwignia_skora', 'kierownica_ogrzewana', 'keyless_go',
    'ogrzewanie_postojowe', 'czujnik_deszczu', 'wycieraczki',
    'elektryczne_szyby_tylne', 'dach_otwierany', 'hak'
];

$samochodyElektryczneList = [
    'system_odzyskiwania', 'funkcja_szybkiego', 'kabel_ladowania'
];

$systemyWspomaganiaList = [
    'kontrola_odleglosci_przod', 'park_assistant', 'kamera_360',
    'lusterka_el_ustaw', 'lusterka_el_sklad', 'asystent_martwego_pola',
    'lane_assist', 'ogranicznik_predkosci', 'asystent_zakretow',
    'auto_kontrola_zjazdu', 'aktywne_rozpoznawanie', 'asystent_kolizji',
    'asystent_swiatel', 'oswietlenie_adapt', 'czujnik_zmierzchu',
    'swiatla_dzienne', 'lampy_przeciwmgielne', 'lampy_tyl_led',
    'oswietlenie_wnetrza', 'elektroniczna_kontrola', 'wspomaganie_kier',
    'regulowany_dyferencjal', 'kontrola_odleglosci_tyl', 'niezalezny_parking',
    'kamera_parkowania_tyl', 'podgrzewane_lusterka', 'kamera_lusterko',
    'aktywny_asystent_pasa', 'kontrola_odleglosci', 'asystent_hamowania',
    'kontrola_trakcji', 'wspomaganie_ruszania', 'lampy_zakret',
    'dynamiczne_swiatla', 'spryskiwacze_ref', 'swiatla_led', 'oswietlenie_drogi',
    'system_start', 'elektryczny_hamulec', 'blokada_mechanizmu', 'asystent_jazdy',
    'system_rozpoznawania', 'autonomiczny_system', 'system_awaryjny'
];

$osiagiTuningList = [
    'opony_runflat', 'elektr_regul_zawieszenia', 'zawieszenie_regulowane',
    'zawieszenie_hydropneum', 'filtr_czastek', 'zawieszenie_komfortowe',
    'zawieszenie_sportowe', 'zawieszenie_pneumatyczne', 'hamulce_ceramiczne'
];

$bezpieczenstwoList = [
    'abs', 'elektr_system_rozdz', 'asystent_hamowania_miasto',
    'aktywny_asystent_awar', 'system_ochrony_sluchu', 'system_kolizja_boczny',
    'system_avas', 'system_rekomendacji', 'asystent_pasa', 'system_wypadku',
    'poduszka_pasazera', 'poduszka_kolan_pasazera', 'poduszka_centralna',
    'boczna_poduszka_kier', 'kurtyny_tyl', 'kurtyna_poprzeczna', 'isofix',
    'esp', 'system_wspomagania', 'system_hamowania_pieszych', 'system_ostrzegajacy',
    'system_kolizja_tylny', 'alarm_ruchu', 'system_zmeczenie', 'aktywny_system',
    'system_minimalizujacy', 'poduszka_kierowcy', 'poduszka_kolan_kier',
    'kurtyny_przod', 'poduszki_czolowe_tyl', 'boczne_poduszki_przod',
    'boczne_poduszki_tyl', 'poduszka_pasow', 'system_dachowania'
];

// Функція категоризації обладнання
function categorizeEquipment($allEquipment, $categoryList) {
    return array_values(array_intersect($allEquipment, $categoryList));
}

// Категоризуємо обладнання
$audio_multimedia = categorizeEquipment($allEquipment, $audioMultimediaList);
$komfort = categorizeEquipment($allEquipment, $komfortList);
$samochody_elektryczne = categorizeEquipment($allEquipment, $samochodyElektryczneList);
$systemy_wspomagania = categorizeEquipment($allEquipment, $systemyWspomaganiaList);
$osiagi_tuning = categorizeEquipment($allEquipment, $osiagiTuningList);
$bezpieczenstwo = categorizeEquipment($allEquipment, $bezpieczenstwoList);

// Обробка зображень
$images = [];

// При редагуванні, спочатку зберігаємо існуючі зображення
if ($isEditing && isset($_POST['existing_images']) && is_string($_POST['existing_images'])) {
    $existingImages = json_decode($_POST['existing_images'], true);
    if (is_array($existingImages)) {
        foreach ($existingImages as $image) {
            if (isset($image['id']) && isset($image['type']) && isset($image['data'])) {
                $images[] = $image; // Додаємо існуючі зображення в тому ж порядку
            }
        }
    }
}

// Обробка нових зображень
if (!empty($_FILES['photos']['name'][0])) {
    $newImages = [];
    foreach ($_FILES['photos']['tmp_name'] as $key => $tmpName) {
        // Перевірка на помилки завантаження
        if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
            continue;
        }

        // Перевірка типу файлу
        $mimeType = $_FILES['photos']['type'][$key];
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            continue;
        }

        // Перевірка розміру файлу (максимум 5MB)
        if ($_FILES['photos']['size'][$key] > 5 * 1024 * 1024) {
            continue;
        }

        // Читаємо файл і конвертуємо в base64
        $imageData = file_get_contents($tmpName);
        $base64Data = base64_encode($imageData);

        // Додаємо нове зображення в масив нових зображень
        $newImages[] = [
            'id' => uniqid(),
            'type' => $mimeType,
            'data' => $base64Data
        ];
    }

    // Видаляємо останнє зображення з нових
    if (!empty($newImages)) {
        array_pop($newImages);
    }

    // Додаємо оброблені нові зображення до існуючих
    $images = array_merge($images, $newImages);
}

// Конвертуємо масив зображень в JSON для збереження в БД
$imagesJson = json_encode($images);

// Додаємо обробку селектів комфорту та систем допомоги
$klimatyzacja = !empty($_POST['klimatyzacja']) && $_POST['klimatyzacja'] !== 'none' ? $_POST['klimatyzacja'] : null;
$rozkladany_dach = !empty($_POST['rozkladany_dach']) && $_POST['rozkladany_dach'] !== 'none' ? $_POST['rozkladany_dach'] : null;
$otwierany_dach = !empty($_POST['otwierany_dach']) && $_POST['otwierany_dach'] !== 'none' ? $_POST['otwierany_dach'] : null;
$oslona = !empty($_POST['oslona']) && $_POST['oslona'] !== 'none' ? $_POST['oslona'] : null;
$tapicerka = !empty($_POST['tapicerka']) && $_POST['tapicerka'] !== 'none' ? $_POST['tapicerka'] : null;
$tempomat = !empty($_POST['tempomat']) && $_POST['tempomat'] !== 'none' ? $_POST['tempomat'] : null;
$reflektory = !empty($_POST['reflektory']) && $_POST['reflektory'] !== 'none' ? $_POST['reflektory'] : null;
$felgi = !empty($_POST['felgi']) && $_POST['felgi'] !== 'none' ? $_POST['felgi'] : null;
$opony = !empty($_POST['opony']) && $_POST['opony'] !== 'none' ? $_POST['opony'] : null;

// Ціна та валюта
$price_type = $_POST['price_type'] ?? 'brutto';
$price = (float)($_POST['price'] ?? 0);
$currency = $_POST['currency'] ?? 'PLN';

// SQL запит
if ($isEditing) {
    // Перевірка прав на редагування
    $stmtCheck = $connect->prepare("SELECT listing_id FROM listings WHERE listing_id = :id AND user_id = :uid LIMIT 1");
    $stmtCheck->execute(['id' => $listing_id, 'uid' => $user_id]);
    
    if (!$stmtCheck->fetch()) {
        header('Location: ../templates/my-listings.php?error=1&message=Brak uprawnień do edycji');
        exit;
    }
    
    $sql = "UPDATE listings SET
        brand = :brand,
        model = :model,
        version = :version,
        generation = :generation,
        prod_year = :prod_year,
        mileage = :mileage,
        vin = :vin,
        reg_number = :reg_number,
        first_registration = :first_registration,
        hide_reg_info = :hide_reg_info,
        engine_capacity = :engine_capacity,
        fuel_type = :fuel_type,
        power = :power,
        transmission = :transmission,
        drive_type = :drive_type,
        body_type = :body_type,
        doors = :doors,
        seats = :seats,
        color = :color,
        color_type = :color_type,
        right_hand_drive = :right_hand_drive,
        co2_emission = :co2_emission,
        damaged = :damaged,
        imported = :imported,
        pierwszy_wlasciciel = :pierwszy_wlasciciel,
        bezwypadkowy = :bezwypadkowy,
        zarejestrowany_pl = :zarejestrowany_pl,
        serwisowany_aso = :serwisowany_aso,
        zarejestrowany_zabytek = :zarejestrowany_zabytek,
        tuning = :tuning,
        homologacja = :homologacja,
        gwarancja_dzien = :gwarancja_dzien,
        gwarancja_miesiac = :gwarancja_miesiac,
        gwarancja_rok = :gwarancja_rok,
        gwarancja_przebieg = :gwarancja_przebieg,
        audio_multimedia = :audio_multimedia,
        komfort = :komfort,
        samochody_elektryczne = :samochody_elektryczne,
        systemy_wspomagania = :systemy_wspomagania,
        osiagi_tuning = :osiagi_tuning,
        bezpieczenstwo = :bezpieczenstwo,
        klimatyzacja = :klimatyzacja,
        rozkladany_dach = :rozkladany_dach,
        otwierany_dach = :otwierany_dach,
        oslona = :oslona,
        tapicerka = :tapicerka,
        tempomat = :tempomat,
        reflektory = :reflektory,
        felgi = :felgi,
        opony = :opony,
        seller_phone = :seller_phone,
        seller_location = :seller_location,
        kraj_pochodzenia = :kraj_pochodzenia,
        title = :title,
        description = :description,
        images = :images,
        price_type = :price_type,
        price = :price,
        currency = :currency,
        updated_at = NOW()
        WHERE listing_id = :listing_id AND user_id = :user_id";
} else {
    $sql = "INSERT INTO listings (
        user_id, brand, model, version, generation, prod_year, mileage,
        vin, reg_number, first_registration, hide_reg_info,
        engine_capacity, fuel_type, power, transmission, drive_type,
        body_type, doors, seats, color, color_type, right_hand_drive,
        co2_emission, damaged, imported, pierwszy_wlasciciel, bezwypadkowy,
        zarejestrowany_pl, serwisowany_aso, zarejestrowany_zabytek,
        tuning, homologacja, gwarancja_dzien, gwarancja_miesiac,
        gwarancja_rok, gwarancja_przebieg, audio_multimedia, komfort,
        samochody_elektryczne, systemy_wspomagania, osiagi_tuning,
        bezpieczenstwo, klimatyzacja, rozkladany_dach, otwierany_dach,
        oslona, tapicerka, tempomat, reflektory, felgi, opony,
        seller_phone, seller_location, kraj_pochodzenia, title, description, images, price_type, price, currency, status, created_at, updated_at
    ) VALUES (
        :user_id, :brand, :model, :version, :generation,
        :prod_year, :mileage, :vin, :reg_number,
        :first_registration, :hide_reg_info, :engine_capacity,
        :fuel_type, :power, :transmission, :drive_type,
        :body_type, :doors, :seats, :color, :color_type, :right_hand_drive,
        :co2_emission, :damaged, :imported, :pierwszy_wlasciciel, :bezwypadkowy,
        :zarejestrowany_pl, :serwisowany_aso, :zarejestrowany_zabytek,
        :tuning, :homologacja, :gwarancja_dzien, :gwarancja_miesiac,
        :gwarancja_rok, :gwarancja_przebieg, :audio_multimedia, :komfort,
        :samochody_elektryczne, :systemy_wspomagania, :osiagi_tuning,
        :bezpieczenstwo, :klimatyzacja, :rozkladany_dach, :otwierany_dach,
        :oslona, :tapicerka, :tempomat, :reflektory, :felgi, :opony,
        :seller_phone, :seller_location, :kraj_pochodzenia, :title, :description, :images, :price_type, :price, :currency, 'pending', NOW(), NOW()
    )";
}

$stmt = $connect->prepare($sql);

// Прив'язуємо параметри
$params = [
    ':user_id' => $user_id,
    ':title' => $title,
    ':description' => $description,
    ':brand' => $brand,
    ':model' => $model,
    ':version' => $version,
    ':generation' => $generation,
    ':prod_year' => $prod_year,
    ':mileage' => $mileage,
    ':vin' => $vin,
    ':reg_number' => $reg_number,
    ':first_registration' => $first_registration,
    ':hide_reg_info' => $hide_reg_info,
    ':engine_capacity' => $engine_capacity,
    ':fuel_type' => $fuel_type,
    ':power' => $power,
    ':transmission' => $transmission,
    ':drive_type' => $drive_type,
    ':body_type' => $body_type,
    ':doors' => $doors,
    ':seats' => $seats,
    ':color' => $color,
    ':color_type' => $color_type,
    ':right_hand_drive' => $right_hand_drive,
    ':co2_emission' => $co2_emission,
    ':damaged' => $damaged,
    ':imported' => $imported,
    ':pierwszy_wlasciciel' => $pierwszy_wlasciciel,
    ':bezwypadkowy' => $bezwypadkowy,
    ':zarejestrowany_pl' => $zarejestrowany_pl,
    ':serwisowany_aso' => $serwisowany_aso,
    ':zarejestrowany_zabytek' => $zarejestrowany_zabytek,
    ':tuning' => $tuning,
    ':homologacja' => $homologacja,
    ':gwarancja_dzien' => $gwarancja_dzien,
    ':gwarancja_miesiac' => $gwarancja_miesiac,
    ':gwarancja_rok' => $gwarancja_rok,
    ':gwarancja_przebieg' => $gwarancja_przebieg,
    ':audio_multimedia' => json_encode($audio_multimedia, JSON_UNESCAPED_UNICODE),
    ':komfort' => json_encode($komfort, JSON_UNESCAPED_UNICODE),
    ':samochody_elektryczne' => json_encode($samochody_elektryczne, JSON_UNESCAPED_UNICODE),
    ':systemy_wspomagania' => json_encode($systemy_wspomagania, JSON_UNESCAPED_UNICODE),
    ':osiagi_tuning' => json_encode($osiagi_tuning, JSON_UNESCAPED_UNICODE),
    ':bezpieczenstwo' => json_encode($bezpieczenstwo, JSON_UNESCAPED_UNICODE),
    ':klimatyzacja' => $klimatyzacja,
    ':rozkladany_dach' => $rozkladany_dach,
    ':otwierany_dach' => $otwierany_dach,
    ':oslona' => $oslona,
    ':tapicerka' => $tapicerka,
    ':tempomat' => $tempomat,
    ':reflektory' => $reflektory,
    ':felgi' => $felgi,
    ':opony' => $opony,
    ':seller_phone' => $seller_phone,
    ':seller_location' => $seller_location,
    ':kraj_pochodzenia' => $kraj_pochodzenia,
    ':images' => $imagesJson,
    ':price_type' => $price_type,
    ':price' => $price,
    ':currency' => $currency
];

if ($isEditing) {
    $params[':listing_id'] = $listing_id;
    $params[':user_id'] = $user_id; // Додаємо user_id для умови WHERE
}

// Логуємо фінальні значення перед збереженням
error_log("Final values before save:");
error_log("damaged: " . $params[':damaged']);
error_log("imported: " . $params[':imported']);

try {
    error_log("Executing SQL query with params: " . print_r($params, true));
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        $redirectUrl = '../templates/my-listings.php?success=1';
        if ($isEditing) {
            $redirectUrl .= '&message=' . urlencode('Ogłoszenie zostało zaktualizowane');
        } else {
            $redirectUrl .= '&message=' . urlencode('Ogłoszenie zostało dodane');
        }
    } else {
        error_log("No rows affected. SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        $redirectUrl = '../templates/create-listing.php?error=1&message=' . urlencode('Nie udało się zapisać ogłoszenia');
    }
} catch (PDOException $e) {
    error_log("SQL Error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    error_log("SQL Query: " . $sql);
    error_log("SQL Params: " . print_r($params, true));
    $redirectUrl = '../templates/create-listing.php?error=1&message=' . urlencode('Błąd podczas zapisywania ogłoszenia: ' . $e->getMessage());
}

header('Location: ' . $redirectUrl);
exit();