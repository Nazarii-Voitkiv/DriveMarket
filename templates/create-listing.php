<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

// Перевірка авторизації
if (!isset($_COOKIE['token'])) {
    header('Location: login.php');
    exit();
}

$isEditing = false;
$listingData = null;

// Перевірка режиму редагування
if (isset($_GET['edit'])) {
    $isEditing = true;
    $listing_id = $_GET['edit'];
    
    try {
        $connect = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
            $config['db_username'],
            $config['db_password']
        );
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT * FROM listings WHERE listing_id = ?";
        $stmt = $connect->prepare($query);
        $stmt->execute([$listing_id]);
        $listingData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$listingData) {
            header('Location: my-listings.php');
            exit();
        }
    } catch (PDOException $e) {
        echo "Помилка: " . $e->getMessage();
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>DriveMarket - Stwórz ogłoszenie</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/create-listing.css">
    <link rel="stylesheet" href="../styles/footer.css">
    <link rel="stylesheet" href="../styles/avatar.css">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    
    <style>
        .details-content,
        .equipment-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .details-content.expanded,
        .equipment-content.expanded {
            max-height: 1000px; /* Або інше велике значення */
        }

    </style>

</head>
<body>
    <header class="header">
        <a href="../index.html"><div class="logo">DriveMarket</div></a>
        <nav class="menu" style="padding-left: 40px;">
            <a href="../index.html">Strona główna</a>
            <a href="posts.php">Ogłoszenia</a>
            <a href="#">Kontakt</a>
        </nav>
        <div class="auth-container">
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">Zaloguj się</a>
                <a href="register.php" class="auth-btn">Zarejestruj się</a>
            </div>
            <div class="user-menu" style="display: none;">
                <div class="avatar-circle" onclick="toggleDropdown()">
                    <div class="user-initial"></div>
                    <img class="avatar-image" alt="User avatar">
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">Profil</a>
                    <a href="create-listing.php">Dodaj ogłoszenie</a>
                    <a href="my-listings.php">Moje ogłoszenia</a>
                    <button onclick="logout()">Wyloguj się</button>
                </div>
            </div>
        </div>
    </header>

    <div class="form-container">
        <?php if (isset($_GET['error']) && $_GET['error'] === '1'): ?>
            <p class="error-message">
                <?php echo htmlspecialchars($_GET['message'] ?? 'Wystąpił błąd podczas dodawania ogłoszenia'); ?>
            </p>
        <?php endif; ?>

        <form id="listingForm" action="../handlers/create-listing-handler.php" method="POST" enctype="multipart/form-data">
            <?php if ($isEditing): ?>
                <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">
            <?php endif; ?>

            <h2>Dane pojazdu</h2>

            <h3>Cechy główne</h3>
            
            <div class="features-group">
                <div class="feature-item">
                    <label>Uszkodzony*</label>
                    <div class="radio-group">
                        <label class="radio-button">
                            <input type="radio" name="damaged" value="nie" required>
                            <span>Nie</span>
                        </label>
                        <label class="radio-button">
                            <input type="radio" name="damaged" value="tak">
                            <span>Tak</span>
                        </label>
                    </div>
                </div>

                <div class="feature-item">
                    <label>Importowany*</label>
                    <div class="radio-group">
                        <label class="radio-button">
                            <input type="radio" name="imported" value="nie" required>
                            <span>Nie</span>
                        </label>
                        <label class="radio-button">
                            <input type="radio" name="imported" value="tak">
                            <span>Tak</span>
                        </label>
                    </div>
                </div>
            </div>

            <h3>Informacje podstawowe</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="vin" class="normal-label">VIN:*</label>
                    <input type="text" id="vin" name="vin" placeholder="np. 1HGCM82633A123456" required>
                </div>
                <div class="form-group">
                    <label for="mileage" class="normal-label">Przebieg:*</label>
                    <div class="input-with-suffix">
                        <input type="number" id="mileage" name="mileage" placeholder="np. 50 000" required>
                        <span class="input-suffix">km</span>
                    </div>
                </div>
            </div>

            <div class="form-row highlighted">
                <div class="form-fields">
                    <div class="form-group">
                        <label for="reg_number" class="normal-label">Numer rejestracyjny pojazdu*</label>
                        <input type="text" id="reg_number" name="reg_number" placeholder="np. WY1234C" required>
                    </div>
                    <div class="form-group">
                        <label for="first_reg_date" class="normal-label">Data pierwszej rejestracji w historii pojazdu*</label>
                        <div class="date-input-group">
                            <input type="text" id="first_reg_date_day" name="first_reg_date_day" placeholder="DD" maxlength="2" pattern="[0-9]*" inputmode="numeric" required>
                            <span class="date-separator">/</span>
                            <input type="text" id="first_reg_date_month" name="first_reg_date_month" placeholder="MM" maxlength="2" pattern="[0-9]*" inputmode="numeric" required>
                            <span class="date-separator">/</span>
                            <input type="text" id="first_reg_date_year" name="first_reg_date_year" placeholder="RRRR" maxlength="4" pattern="[0-9]*" inputmode="numeric" required>
                        </div>
                    </div>
                </div>
                <div class="toggle-container">
                    <label class="toggle">
                        <input type="checkbox" name="hide_reg_info">
                        <span class="toggle-switch"></span>
                        <span class="toggle-label">Nie pokazuj numeru rejestracyjnego i daty pierwszej rejestracji w ogtoszeniu</span>
                    </label>
                </div>
            </div>

            <h3>Dane techniczne</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="prod_year" class="normal-label">Rok produkcji*</label>
                    <select id="prod_year" name="prod_year" required>
                        <option value="" disabled selected>Wybierz</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="brand" class="normal-label">Marka pojazdu*</label>
                    <input type="text" id="brand" name="brand" placeholder="Wpisz markę" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="model" class="normal-label">Model pojazdu*</label>
                    <input type="text" id="model" name="model" placeholder="np. A4" required>
                </div>
                <div class="form-group">
                    <label for="fuel_type" class="normal-label">Rodzaj paliwa*</label>
                    <select id="fuel_type" name="fuel_type" required>
                        <option value="" disabled selected>Wybierz</option>
                        <option value="benzyna">Benzyna</option>
                        <option value="benzyna_cng">Benzyna+CNG</option>
                        <option value="benzyna_lpg">Benzyna+LPG</option>
                        <option value="diesel">Diesel</option>
                        <option value="elektryczny">Elektryczny</option>
                        <option value="etanol">Etanol</option>
                        <option value="hybryda">Hybryda</option>
                        <option value="hybryda_plugin">Hybryda Plug-in</option>
                        <option value="wodor">Wodór</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="power" class="normal-label">Moc*</label>
                    <div class="input-with-suffix">
                        <input type="number" id="power" name="power" placeholder="np. 100" required>
                        <span class="input-suffix">KM</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="engine_capacity" class="normal-label">Pojemność skokowa*</label>
                    <div class="input-with-suffix">
                        <input type="number" id="engine_capacity" name="engine_capacity" placeholder="np. 1255" required>
                        <span class="input-suffix">cm3</span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="doors" class="normal-label">Liczba drzwi*</label>
                    <select id="doors" name="doors" required>
                        <option value="" disabled selected>Wybierz</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transmission" class="normal-label">Skrzynia biegów*</label>
                    <select id="transmission" name="transmission" required>
                        <option value="" disabled selected>Wybierz</option>
                        <option value="manual">Manualna</option>
                        <option value="automatic">Automatyczna</option>
                        <option value="semi_automatic">Półautomatyczna</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="version" class="normal-label">Wersja*</label>
                    <input type="text" id="version" name="version" placeholder="np. Sport" required>
                </div>
                <div class="form-group">
                    <label for="generation" class="normal-label">Generacja</label>
                    <input type="text" id="generation" name="generation" placeholder="np. IV">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="body_type" class="normal-label">Typ nadwozia*</label>
                    <select id="body_type" name="body_type" required>
                        <option value="" disabled selected>Wybierz</option>
                        <option value="auta_male">Auta małe</option>
                        <option value="auta_miejskie">Auta miejskie</option>
                        <option value="coupe">Coupe</option>
                        <option value="kabriolet">Kabriolet</option>
                        <option value="kombi">Kombi</option>
                        <option value="kompakt">Kompakt</option>
                        <option value="minivan">Minivan</option>
                        <option value="sedan">Sedan</option>
                        <option value="suv">SUV</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="color" class="normal-label">Kolor*</label>
                    <select id="color" name="color" required>
                        <option value="" disabled selected>Wybierz</option>
                        <option value="bezowy">Beżowy</option>
                        <option value="bialy">Biały</option>
                        <option value="blekitny">Błękitny</option>
                        <option value="bordowy">Bordowy</option>
                        <option value="brazowy">Brązowy</option>
                        <option value="czarny">Czarny</option>
                        <option value="czerwony">Czerwony</option>
                        <option value="fioletowy">Fioletowy</option>
                        <option value="granatowy">Granatowy</option>
                        <option value="niebieski">Niebieski</option>
                        <option value="pomaranczowy">Pomarańczowy</option>
                        <option value="srebrny">Srebrny</option>
                        <option value="szary">Szary</option>
                        <option value="zielony">Zielony</option>
                        <option value="zloty">Złoty</option>
                        <option value="zolty">Żółty</option>
                        <option value="inny">Inny kolor</option>
                    </select>
                </div>
            </div>

            <h2>Zdjęcia i multimedia <span class="photo-counter">0/40</span></h2>

            <div class="photo-upload-container">
                <div class="upload-zone" id="uploadZone">
                    <button type="button" class="upload-button" id="uploadButton">Dodaj zdjęcia</button>
                    <span class="upload-text">lub upuść pliki tutaj</span>
                </div>
                <p class="upload-info"><strong>Zalecamy co najmniej 15 zdjęć</strong>, ale możesz dodać maksymalnie 40 zdjęć. Akceptowane są formaty plików JPG i PNG.</p>
                <input type="file" id="photoInput" name="photos[]" multiple accept="image/jpeg,image/png" style="display: none;">
                <div id="previewContainer" class="preview-container"></div>
            </div>

            <h2>Opis pojazdu</h2>

            <div class="description-container">
                <div class="form-group">
                    <label for="title" class="normal-label">Tytuł ogłoszenia*</label>
                    <input type="text" id="title" name="title" placeholder="np. pierwszy właściciel, stan idealny, nowy akumulator" value="<?php echo htmlspecialchars($listingData['title'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="normal-label">Opis*</label>
                    <div id="editor-container">
                        <div id="editor"><?php echo $listingData['description'] ?? ''; ?></div>
                    </div>
                    <input type="hidden" name="description" id="description">
                </div>
            </div>

            <h2>Wyświetl dodatkowe szczegóły</h2>
            
            <div class="details-section">
                <div class="details-header">
                    <h3>Dane techniczne</h3>
                    <button type="button" class="expand-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="details-content">
                    <div class="details-group">
                        <div class="details-item">
                            <label>Kierownica po prawej (Anglik)</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="right_hand_drive" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="right_hand_drive" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>

                        <div class="details-item">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Napęd</label>
                                    <select name="drive_type">
                                        <option value="">Wybierz</option>
                                        <option value="4x4_auto">4x4 (dołączany automatycznie)</option>
                                        <option value="4x4_manual">4x4 (dołączany ręcznie)</option>
                                        <option value="4x4_permanent">4x4 (stały)</option>
                                        <option value="front">Na przednie koła</option>
                                        <option value="rear">Na tylne koła</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Emisja CO2</label>
                                    <div class="input-with-suffix">
                                        <input type="number" name="co2_emission" placeholder="">
                                        <span class="input-suffix">g/km</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="details-item">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Rodzaj koloru</label>
                                    <select name="color_type">
                                        <option value="">Wybierz</option>
                                        <option value="matowy">Matowy</option>
                                        <option value="metalik">Metalik</option>
                                        <option value="perlowy">Perłowy</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Liczba miejsc</label>
                                    <select name="seats">
                                        <option value="">Wybierz</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-section">
                <div class="details-header">
                    <h3>Wyposażenie</h3>
                    <button type="button" class="expand-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="details-content">
                    <div class="equipment-section">
                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Audio i multimedia</span>
                                <span class="equipment-counter">0/13</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="checkbox-grid">
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="apple_carplay">
                                        <span>Apple CarPlay</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="android_auto">
                                        <span>Android Auto</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="bluetooth">
                                        <span>Interfejs Bluetooth</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="radio">
                                        <span>Radio</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="speakers">
                                        <span>Zestaw głośnomówiący</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="usb">
                                        <span>Gniazdo USB</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="wireless_charging">
                                        <span>Ładowanie bezprzewodowe urządzeń</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="navigation">
                                        <span>System nawigacji satelitarnej</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="sound_system">
                                        <span>System nagłośnienia</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="head_up">
                                        <span>Wyświetlacz typu Head-Up</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="touchscreen">
                                        <span>Ekran dotykowy</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="voice_control">
                                        <span>Sterowanie funkcjami pojazdu za pomocą głosu</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="internet">
                                        <span>Dostęp do internetu</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Komfort</span>
                                <span class="equipment-counter">0/44</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="komfort-grid">
                                    <div class="komfort-column">
                                        <div class="select-wrapper">
                                            <label>Klimatyzacja</label>
                                            <select name="klimatyzacja">
                                                <option value="none">Wybierz</option>
                                                <option value="klimatyzacja_auto">Klimatyzacja automatyczna</option>
                                                <option value="klimatyzacja_auto_2">Klimatyzacja automatyczna, dwustrefowa</option>
                                                <option value="klimatyzacja_auto_3">Klimatyzacja automatyczna: 3 strefowa</option>
                                                <option value="klimatyzacja_auto_4">Klimatyzacja automatyczna: 4 lub więcej strefowa</option>
                                                <option value="klimatyzacja_manual">Klimatyzacja manualna</option>
                                            </select>
                                        </div>
                                        <div class="select-wrapper">
                                            <label>Rozkładany dach</label>
                                            <select name="rozkladany_dach">
                                                <option value="none">Wybierz</option>
                                                <option value="dach_material">Dach materiałowy</option>
                                                <option value="dach_tworzywo">Dach z tworzywa sztucznego</option>
                                                <option value="hardtop">Hardtop</option>
                                            </select>
                                        </div>
                                        <div class="select-wrapper">
                                            <label>Otwierany dach</label>
                                            <select name="otwierany_dach">
                                                <option value="none">Wybierz</option>
                                                <option value="dach_panoramiczny">Dach panoramiczny</option>
                                                <option value="drugi_szyberdach">Drugi szyberdach szklany - przesuwny i uchylny el.</option>
                                                <option value="szyberdach_elektr">Szyberdach szklany - przesuwny i uchylny elektrycz</option>
                                                <option value="szyberdach_reczny">Szyberdach szklany - przesuwny i uchylny ręcznie</option>
                                            </select>
                                        </div>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektr_fotel_kier">
                                            <span>Elektrycznie ustawiany fotel kierowcy</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podgrz_fotel_kier">
                                            <span>Podgrzewany fotel kierowcy</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="regul_ledzw_kier">
                                            <span>Regul. elektr. podparcia lędźwiowego - kierowca</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="fotele_przednie_went">
                                            <span>Fotele przednie wentylowane</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="siedzenie_pamiec">
                                            <span>Siedzenie z pamięcią ustawienia</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="ogrzewane_siedzenia_tyl">
                                            <span>Ogrzewane siedzenia tylne</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="fotele_tyl_masaz">
                                            <span>Fotele tylne z funkcje masażu</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podlokietniki_tyl">
                                            <span>Podłokietniki - tył</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kierownica_sportowa">
                                            <span>Kierownica sportowa</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kierownica_radio">
                                            <span>Kierownica ze sterowaniem radia</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kolumna_kier_el">
                                            <span>Kolumna kierownicy regulowana elektrycznie</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kierownica_wielofunkcyjna">
                                            <span>Kierownica wielofunkcyjna</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="keyless_entry">
                                            <span>Keyless entry</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="uruchamianie_silnika">
                                            <span>Uruchamianie silnika bez użycia kluczyków</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="auto_kontrola_ogrzewania">
                                            <span>Automatyczna kontrola ogrzewania</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podgrzewana_szyba">
                                            <span>Podgrzewana przednia szyba</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektryczne_szyby_przednie">
                                            <span>Elektryczne szyby przednie</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="przyciemniane_szyby">
                                            <span>Przyciemniane tylne szyby</span>
                                    </label>
                                    <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="dach_otwierany_pilot">
                                            <span>Dach otwierany elektrycznie pilotem</span>
                                    </label>
                                    </div>
                                    <div class="komfort-column">
                                        <div class="select-wrapper" style="padding-top: 18px;">
                                            <label class="checkbox-item" >
                                                <input type="checkbox" name="equipment[]" value="klimatyzacja_tyl">
                                                <span>Klimatyzacja dla pasażerów z tyłu</span>
                                            </label>
                                        </div>
                                        <div class="select-wrapper">
                                            <label>Osłona przeciwsłoneczna</label>
                                            <select name="oslona">
                                                <option value="none">Wybierz</option>
                                                <option value="rolety_elektr">Rolety na bocznych szybach opuszczane elektrycznie</option>
                                                <option value="rolety_reczne">Rolety na bocznych szybach opuszczane ręcznie</option>
                                            </select>
                                        </div>
                                        <div class="select-wrapper">
                                            <label>Tapicerka</label>
                                            <select name="tapicerka">
                                                <option value="none">Wybierz</option>
                                                <option value="alcantara">Tapicerka Alcantara</option>
                                                <option value="skora_czesciowa">Tapicerka częściowo skórzana</option>
                                                <option value="materialowa">Tapicerka materiałowa</option>
                                                <option value="skorzana">Tapicerka skórzana</option>
                                            </select>
                                        </div>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektr_fotel_pasaz">
                                            <span>Elektrycznie ustawiany fotel pasażera</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podgrz_fotel_pasaz">
                                            <span>Podgrzewany fotel pasażera</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="regul_ledzw_pasaz">
                                            <span>Regul. elektr. podparcia lędźwiowego - pasażer</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="fotele_przednie_masaz">
                                            <span>Fotele przednie z funkcje masażu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="sportowe_fotele">
                                            <span>Sportowe fotele - przód</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="fotele_tylne_went">
                                            <span>Fotele tylne wentylowane</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podlokietniki_przod">
                                            <span>Podłokietniki - przód</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="kierownica_skorzana">
                                        <span>Kierownica skórzana</span>
                                    </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zmiana_biegow_kier">
                                            <span>Zmiana biegów w kierownicy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="cyfrowy_kluczyk">
                                            <span>Cyfrowy kluczyk</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="dzwignia_skora">
                                            <span>Dźwignia zmiany biegów wykończona skórą</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kierownica_ogrzewana">
                                            <span>Kierownica ogrzewana</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="keyless_go">
                                            <span>Keyless Go</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="ogrzewanie_postojowe">
                                            <span>Ogrzewanie postojowe</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="czujnik_deszczu">
                                            <span>Czujnik deszczu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="wycieraczki">
                                            <span>Wycieraczki</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektryczne_szyby_tylne">
                                            <span>Elektryczne szyby tylne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="dach_otwierany">
                                            <span>Dach otwierany elektrycznie</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="hak">
                                            <span>Hak</span>
                                    </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Samochody elektryczne</span>
                                <span class="equipment-counter">0/3</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="checkbox-grid">
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="system_odzyskiwania">
                                        <span>System odzyskiwania energii</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="funkcja_szybkiego">
                                        <span>Funkcja szybkiego ładowania</span>
                                    </label>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="equipment[]" value="kabel_ladowania">
                                        <span>Kabel do ładowania</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Systemy wspomagania kierowcy</span>
                                <span class="equipment-counter">0/46</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="komfort-grid">
                                    <div class="komfort-column">
                                        <div class="select-wrapper">
                                            <label>Rodzaj tempomatu</label>
                                            <select name="tempomat">
                                                <option value="none">Wybierz</option>
                                                <option value="tempomat">Tempomat</option>
                                                <option value="tempomat_adaptacyjny_acc">Tempomat adaptacyjny ACC</option>
                                                <option value="tempomat_przewidujacy_pcc">Tempomat przewidujący PCC</option>
                                            </select>
                                        </div>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kontrola_odleglosci_przod">
                                            <span>Kontrola odległości z przodu (przy parkowaniu)</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="park_assistant">
                                            <span>Park Assistant - asystent parkowania</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kamera_360">
                                            <span>Kamera panoramiczna 360</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lusterka_el_ustaw">
                                            <span>Lusterka boczne ustawiane elektrycznie</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lusterka_el_sklad">
                                            <span>Lusterka boczne składane elektrycznie</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_martwego_pola">
                                            <span>Asystent (czujnik) martwego pola</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lane_assist">
                                            <span>Lane assist - kontrola zmiany pasa ruchu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="ogranicznik_predkosci">
                                            <span>Ogranicznik prędkości</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_zakretow">
                                            <span>Asystent pokonywania zakrętów</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="auto_kontrola_zjazdu">
                                            <span>Automatyczna kontrola zjazdu ze stoku</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="aktywne_rozpoznawanie">
                                            <span>Aktywne rozpoznawanie znaków ograniczenia prędkości</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_kolizji">
                                            <span>Asystent zapobiegania kolizjom na skrzyżowaniu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_swiatel">
                                            <span>Asystent świateł drogowych</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="oswietlenie_adapt">
                                            <span>Oświetlenie adaptacyjne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="czujnik_zmierzchu">
                                            <span>Czujnik zmierzchu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="swiatla_dzienne">
                                            <span>Światła do jazdy dziennej</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lampy_przeciwmgielne">
                                            <span>Lampy przeciwmgielne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lampy_tyl_led">
                                            <span>Lampy tylne w technologii LED</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="oswietlenie_wnetrza">
                                            <span>Oświetlenie wnętrza LED</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektroniczna_kontrola">
                                            <span>Elektroniczna kontrola ciśnienia w oponach</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="wspomaganie_kier">
                                            <span>Wspomaganie kierownicy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="regulowany_dyferencjal">
                                            <span>Regulowany dyferencjał centralny</span>
                                        </label>
                                    </div>
                                    <div class="komfort-column">
                                        <div class="select-wrapper">
                                            <label>Rodzaj reflektorów</label>
                                            <select name="reflektory">
                                                <option value="none">Wybierz</option>
                                                <option value="lampy_bi_ksenonowe">Lampy bi-ksenonowe</option>
                                                <option value="lampy_ksenonowe">Lampy ksenonowe</option>
                                                <option value="lampy_led">Lampy przednie w technologii LED</option>
                                                <option value="reflektory_laserowe">Reflektory laserowe</option>
                                            </select>
                                        </div>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kontrola_odleglosci_tyl">
                                            <span>Kontrola odległości z tyłu (przy parkowaniu)</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="niezalezny_parking">
                                            <span>Niezależny system parkowania</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kamera_parkowania_tyl">
                                            <span>Kamera parkowania tył</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="podgrzewane_lusterka">
                                            <span>Podgrzewane lusterka boczne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kamera_lusterko">
                                            <span>Kamera w lusterku bocznym</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="aktywny_asystent_pasa">
                                            <span>Aktywny asystent zmiany pasa ruchu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kontrola_odleglosci">
                                            <span>Kontrola odległości od poprzedzającego pojazdu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_hamowania">
                                            <span>Asystent hamowania - Brake Assist</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kontrola_trakcji">
                                            <span>Kontrola trakcji</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="wspomaganie_ruszania">
                                            <span>Wspomaganie ruszania pod górę - Hill Holder</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="lampy_zakret">
                                            <span>Lampy doświetlające zakręt</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="dynamiczne_swiatla">
                                            <span>Dynamiczne światła doświetlające zakręty</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="spryskiwacze_ref">
                                            <span>Spryskiwacze reflektorów</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="swiatla_led">
                                            <span>Światła do jazdy dziennej diodowe LED</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="oswietlenie_drogi">
                                            <span>Oświetlenie drogi do domu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_start">
                                            <span>System Start/Stop</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektryczny_hamulec">
                                            <span>Elektryczny hamulec postojowy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="blokada_mechanizmu">
                                            <span>Blokada mechanizmu różnicowego</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_jazdy">
                                            <span>Asystent jazdy w korku</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_rozpoznawania">
                                            <span>System rozpoznawania znaków drogowych</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="autonomiczny_system">
                                            <span>Autonomiczny system kierowania</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_awaryjny">
                                            <span>System awaryjnego hamowania</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Osiągi i tuning</span>
                                <span class="equipment-counter">0/11</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="komfort-grid">
                                    <div class="komfort-column">
                                        <div class="select-wrapper">
                                            <label>Felgi aluminiowe</label>
                                            <select name="felgi">
                                                <option value="none">Wybierz</option>
                                                <option value="felgi_15">Felgi aluminiowe 15</option>
                                                <option value="felgi_16">Felgi aluminiowe 16</option>
                                                <option value="felgi_17">Felgi aluminiowe 17</option>
                                                <option value="felgi_18">Felgi aluminiowe 18</option>
                                                <option value="felgi_19">Felgi aluminiowe 19</option>
                                                <option value="felgi_20">Felgi aluminiowe 20</option>
                                                <option value="felgi_21">Felgi aluminiowe od 21</option>
                                                <option value="felgi_stalowe">Felgi stalowe</option>
                                            </select>
                                        </div>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="opony_runflat">
                                            <span>Opony runflat</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektr_regul_zawieszenia">
                                            <span>Elektroniczna regul. charakterystyki zawieszenia</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zawieszenie_regulowane">
                                            <span>Zawieszenie regulowane</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zawieszenie_hydropneum">
                                            <span>Zawieszenie hydropneumatyczne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="filtr_czastek">
                                            <span>Filtr cząstek stałych</span>
                                        </label>
                                    </div>
                                    <div class="komfort-column">
                                        <div class="select-wrapper">
                                            <label>Opony</label>
                                            <select name="opony">
                                                <option value="none">Wybierz</option>
                                                <option value="opony_letnie">Opony letnie</option>
                                                <option value="opony_offroad">Opony off-road</option>
                                                <option value="opony_wielosezonowe">Opony wielosezonowe</option>
                                                <option value="opony_zimowe">Opony zimowe</option>
                                            </select>
                                        </div>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zawieszenie_komfortowe">
                                            <span>Zawieszenie komfortowe</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zawieszenie_sportowe">
                                            <span>Zawieszenie sportowe</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="zawieszenie_pneumatyczne">
                                            <span>Zawieszenie pneumatyczne</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="hamulce_ceramiczne">
                                            <span>Hamulce z kompozytów ceramicznych</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="equipment-item">
                            <div class="equipment-header">
                                <span>Bezpieczeństwo</span>
                                <span class="equipment-counter">0/34</span>
                                <button type="button" class="expand-btn">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="equipment-content">
                                <div class="komfort-grid">
                                    <div class="komfort-column">
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="abs">
                                            <span>ABS</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="elektr_system_rozdz">
                                            <span>Elektroniczny system rozdziału siły hamowania</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_hamowania_miasto">
                                            <span>Asystent hamowania awaryjnego w mieście</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="aktywny_asystent_awar">
                                            <span>Aktywny asystent hamowania awaryjnego</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_ochrony_sluchu">
                                            <span>System ochrony słuchu w przypadku kolizji</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_kolizja_boczny">
                                            <span>System chroniący przed kolizja, boczny</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_avas">
                                            <span>System emitujący dźwięk silnika (AVAS)</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_rekomendacji">
                                            <span>System rekomendacji przerw podczas trasy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="asystent_pasa">
                                            <span>Asystent pasa ruchu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_wypadku">
                                            <span>System powiadamiania o wypadku</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_pasazera">
                                            <span>Poduszka powietrzna pasażera</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_kolan_pasazera">
                                            <span>Poduszka kolan pasażera</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_centralna">
                                            <span>Poduszka powietrzna centralna</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="boczna_poduszka_kier">
                                            <span>Boczna poduszka powietrzna kierowcy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kurtyny_tyl">
                                            <span>Kurtyny powietrzne - tył</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kurtyna_poprzeczna">
                                            <span>Kurtyna powietrzna z tyłu, poprzeczna</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="isofix">
                                            <span>Isofix (punkty mocowania fotelika dziecięcego)</span>
                                        </label>
                                    </div>
                                    <div class="komfort-column">
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="esp">
                                            <span>ESP</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_wspomagania">
                                            <span>System wspomagania hamowania</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_hamowania_pieszych">
                                            <span>System hamowania awaryjnego dla ochrony pieszych</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_ostrzegajacy">
                                            <span>System ostrzegający o możliwej kolizji</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_kolizja_tylny">
                                            <span>System chroniący przed kolizja, tylny</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="alarm_ruchu">
                                            <span>Alarm ruchu poprzecznego z tyłu pojazdu</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_zmeczenie">
                                            <span>System wykrywania zmęczenie kierowcy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="aktywny_system">
                                            <span>Aktywny system monitorowania kondycji kierowcy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_minimalizujacy">
                                            <span>System minimalizujący skutki kolizji</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_kierowcy">
                                            <span>Poduszka powietrzna kierowcy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_kolan_kier">
                                            <span>Poduszka kolan kierowcy</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="kurtyny_przod">
                                            <span>Kurtyny powietrzne - przód</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszki_czolowe_tyl">
                                            <span>Poduszki powietrzne czołowe - tył</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="boczne_poduszki_przod">
                                            <span>Boczne poduszki powietrzne - przód</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="boczne_poduszki_tyl">
                                            <span>Boczne poduszki powietrzne - tył</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="poduszka_pasow">
                                            <span>Poduszka powietrzna pasów bezpieczeństwa</span>
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="equipment[]" value="system_dachowania">
                                            <span>System zabezpieczający podczas dachowania</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="details-section">
                <div class="details-header">
                    <h3>Historia</h3>
                    <button type="button" class="expand-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="details-content">
                    <div class="details-group">
                        <h3 style="color: #666666;">Pochodzenie pojazdu</h3>
                        <div class="form-group" style="width: 50%;">
                            <label>Kraj pochodzenia</label>
                            <select name="kraj_pochodzenia" class="full-width">
                                <option value="">Wybierz</option>
                                <option value="austria">Austria</option>
                                <option value="belgia">Belgia</option>
                                <option value="bialorus">Białoruś</option>
                                <option value="bulgaria">Bułgaria</option>
                                <option value="chiny">Chiny</option>
                                <option value="chorwacja">Chorwacja</option>
                                <option value="czechy">Czechy</option>
                                <option value="dania">Dania</option>
                                <option value="estonia">Estonia</option>
                                <option value="finlandia">Finlandia</option>
                                <option value="francja">Francja</option>
                                <option value="grecja">Grecja</option>
                                <option value="hiszpania">Hiszpania</option>
                                <option value="holandia">Holandia</option>
                                <option value="inny">Inny</option>
                                <option value="irlandia">Irlandia</option>
                                <option value="islandia">Islandia</option>
                                <option value="japonia">Japonia</option>
                                <option value="kanada">Kanada</option>
                                <option value="korea">Korea</option>
                                <option value="liechtenstein">Liechtenstein</option>
                                <option value="litwa">Litwa</option>
                                <option value="lotwa">Łotwa</option>
                                <option value="luksemburg">Luksemburg</option>
                                <option value="monako">Monako</option>
                                <option value="niemcy">Niemcy</option>
                                <option value="norwegia">Norwegia</option>
                                <option value="polska">Polska</option>
                                <option value="rosja">Rosja</option>
                                <option value="rumunia">Rumunia</option>
                                <option value="slowacja">Słowacja</option>
                                <option value="slowenia">Słowenia</option>
                                <option value="stany_zjednoczone">Stany Zjednoczone</option>
                                <option value="szwajcaria">Szwajcaria</option>
                                <option value="szwecja">Szwecja</option>
                                <option value="turcja">Turcja</option>
                                <option value="ukraina">Ukraina</option>
                                <option value="wegry">Węgry</option>
                                <option value="wielka_brytania">Wielka Brytania</option>
                                <option value="wlochy">Włochy</option>
                            </select>
                        </div>

                        <h3 style="color: #666666;">Stan pojazdu</h3>
                        <div class="details-item">
                            <label>Pierwszy właściciel (od nowości)</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="pierwszy_wlasciciel" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="pierwszy_wlasciciel" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Bezwypadkowy</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="bezwypadkowy" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="bezwypadkowy" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Zarejestrowany w Polsce</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="zarejestrowany_pl" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="zarejestrowany_pl" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Serwisowany w ASO</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="serwisowany_aso" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="serwisowany_aso" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Zarejestrowany jako zabytek</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="zarejestrowany_zabytek" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="zarejestrowany_zabytek" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Tuning</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="tuning" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="tuning" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <div class="details-item">
                            <label>Homologacja ciężarowa</label>
                            <div class="radio-group">
                                <label class="radio-button">
                                    <input type="radio" name="homologacja" value="nie">
                                    <span>Nie</span>
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="homologacja" value="tak">
                                    <span>Tak</span>
                                </label>
                            </div>
                        </div>
                        <h3 style="color: #666666; margin-bottom: 5px;">Gwarancja producenta</h3>
                        <div class="details-item" style="margin-bottom: -50px;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Okres gwarancji producenta</label>
                                    <div class="date-input-group">
                                        <input type="text" name="gwarancja_dzien" placeholder="DD" maxlength="2" pattern="[0-9]*" inputmode="numeric">
                                        <span class="date-separator">/</span>
                                        <input type="text" name="gwarancja_miesiac" placeholder="MM" maxlength="2" pattern="[0-9]*" inputmode="numeric">
                                        <span class="date-separator">/</span>
                                        <input type="text" name="gwarancja_rok" placeholder="RRRR" maxlength="4" pattern="[0-9]*" inputmode="numeric">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>lub do (przebieg km)</label>
                                    <div class="input-with-suffix">
                                        <input type="text" name="gwarancja_przebieg" placeholder="np. 100 000" pattern="[0-9]*" inputmode="numeric">
                                        <span class="input-suffix">km</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h2>Cena</h2>
            
            <div class="price-section">
                <div class="price-type-selector">
                    <label>Typ ceny</label>
                    <div class="price-type-buttons">
                        <input type="radio" id="price_type_brutto" name="price_type" class="price-type-btn" value="brutto" required <?php echo (!$isEditing || ($isEditing && $listingData['price_type'] === 'brutto')) ? 'checked' : ''; ?>>
                        <label for="price_type_brutto" class="price-type-btn <?php echo (!$isEditing || ($isEditing && $listingData['price_type'] === 'brutto')) ? 'active' : ''; ?>">Brutto</label>
                        <input type="radio" id="price_type_netto" name="price_type" class="price-type-btn" value="netto" <?php echo ($isEditing && $listingData['price_type'] === 'netto') ? 'checked' : ''; ?>>
                        <label for="price_type_netto" class="price-type-btn <?php echo ($isEditing && $listingData['price_type'] === 'netto') ? 'active' : ''; ?>">Netto</label>
                    </div>
                </div>

                <div class="price-input-group">
                    <div class="form-group">
                        <label for="price" class="normal-label">Cena*</label>
                        <div class="input-with-suffix">
                            <input type="number" id="price" name="price" placeholder="np. 8 000" required>
                            <span class="input-suffix">PLN</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="currency" class="normal-label">Waluta*</label>
                        <select id="currency" name="currency" required>
                            <option value="PLN" selected>PLN</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>
                <p class="vat-info" style="display: none; margin-top: 5px; color: #666;">Kupujący zobaczą cenę Twojego samochodu wynoszącą <span class="price-with-vat"></span> €. To Twoja cena netto + 23% VAT.</p>
            </div>

            <h2>Dane sprzedającego</h2>
            <div class="seller-details">
                <div class="form-row">
                    <div class="form-group" style="width: 50%;">
                        <label style="font-weight: normal;">Numer telefonu</label>
                        <input type="text" name="seller_phone" placeholder="Numer telefonu">
                    </div>
                    <div class="form-group" style="width: 50%;">
                        <label style="font-weight: normal;">Wpisz miasto lub kod*</label>
                        <input type="text" name="seller_location" placeholder="Wpisz miasto lub kod" required>
                    </div>
                </div>
            </div>

            <div class="form-group submit-group">
                <div class="required-fields-counter" style="text-align: center; margin-bottom: 15px; color: #666;">
                    Wypełniono <span id="filledRequiredFields">0</span> z <span id="totalRequiredFields">23</span> wymaganych pól
                </div>
                <button type="submit" class="submit-btn">
                    <?php echo $isEditing ? 'Zapisz zmiany' : 'Dodaj ogłoszenie'; ?>
                </button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>DriveMarket</h3>
                <p>Twój najlepszy sklep online z częściami samochodowymi. Znajdź wszystko, czego potrzebujesz do swojego pojazdu.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Szybkie linki</h4>
                <nav class="footer-links">
                    <a href="#">Strona główna</a>
                    <a href="templates/posts.php">Produkty</a>
                    <a href="#">O nas</a>
                    <a href="#">Kontakt</a>
                </nav>
            </div>
            <div class="footer-section">
                <h4>Wsparcie</h4>
                <nav class="footer-links">
                    <a href="#">FAQ</a>
                    <a href="#">Polityka prywatności</a>
                    <a href="#">Regulamin</a>
                    <a href="#">Dostawa</a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
        </div>
    </footer>

    <script src="../scripts/auth.js"></script>
    <script>
        // Функція для підрахунку заповнених обов'язкових полів
        function updateRequiredFieldsCounter() {
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(el => console.log(el.name || el.id, el));
            const radioGroups = new Set();
            let filledCount = 0;
            requiredFields.forEach(field => {
                // Для радіо-кнопок
                if (field.type === 'radio') {
                    const groupName = field.getAttribute('name');
                    if (!radioGroups.has(groupName)) {
                        radioGroups.add(groupName);
                        const checkedRadio = document.querySelector(`input[name="${groupName}"]:checked`);
                        if (checkedRadio) {
                            filledCount++;
                        }
                    }
                }
                // Для select елементів
                else if (field.tagName === 'SELECT') {
                    if (field.value && field.value !== '' && field.value !== 'none') {
                        filledCount++;
                    }
                }
                // Для інших полів
                else if (field.value && field.value.trim() !== '') {
                    filledCount++;
                }
            });
            
            document.getElementById('filledRequiredFields').textContent = filledCount;
        }

        // Додаємо слухачі подій для всіх обов'язкових полів
        document.addEventListener('DOMContentLoaded', function() {
            const requiredFields = document.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                field.addEventListener('change', updateRequiredFieldsCounter);
                field.addEventListener('input', updateRequiredFieldsCounter);
            });
            
            // Додаємо слухачі для радіо-кнопок
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', updateRequiredFieldsCounter);
            });
            
            // Початкове оновлення лічильника
            updateRequiredFieldsCounter();
        });

        // Функція для валідації вводу тільки цифр
        function validateNumberInput(event) {
            if (!/[0-9]/.test(event.key) && event.key !== 'Backspace' && event.key !== 'Delete' && event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
                event.preventDefault();
            }
        }

        // Додаємо обробники подій для полів дати
        document.getElementById('first_reg_date_day').addEventListener('keydown', validateNumberInput);
        document.getElementById('first_reg_date_month').addEventListener('keydown', validateNumberInput);
        document.getElementById('first_reg_date_year').addEventListener('keydown', validateNumberInput);

        // Генеруємо список років
        const yearSelect = document.getElementById('prod_year');
        const currentYear = 2025;
        const startYear = 1900;
        
        for (let year = currentYear; year >= startYear; year--) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const uploadZone = document.getElementById('uploadZone');
            const uploadButton = document.getElementById('uploadButton');
            const photoInput = document.getElementById('photoInput');
            const previewContainer = document.getElementById('previewContainer');
            const photoCounter = document.querySelector('.upload-info strong');
            let uploadedFiles = [];

            // Функція оновлення лічильника фотографій
            function updatePhotoCounter() {
                const count = document.querySelectorAll('.preview-item').length;
                photoCounter.textContent = `Masz ${count} z 40 możliwych zdjęć`;
                if (count < 15) {
                    photoCounter.style.color = '#ff4444';
                } else {
                    photoCounter.style.color = '#44aa44';
                }
            }

            // Функція для перевірки файлу
            function validateFile(file) {
                const validTypes = ['image/jpeg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!validTypes.includes(file.type)) {
                    alert('Dozwolone są tylko pliki JPG i PNG.');
                    return false;
                }

                if (file.size > maxSize) {
                    alert('Maksymalny rozmiar pliku to 5MB.');
                    return false;
                }

                return true;
            }

            // Функція для створення попереднього перегляду
            function createPreview(file) {
                const preview = document.createElement('div');
                preview.className = 'preview-item';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <button type="button" class="remove-photo">×</button>
                    `;
                    // Додаємо в кінець
                    previewContainer.appendChild(preview);
                    updatePhotoCounter();
                };
                reader.readAsDataURL(file);

                // Додаємо обробник для кнопки видалення
                preview.querySelector('.remove-photo').addEventListener('click', function() {
                    preview.remove();
                    const index = uploadedFiles.indexOf(file);
                    if (index > -1) {
                        uploadedFiles.splice(index, 1);
                    }
                    updatePhotoCounter();
                });
            }

            // Обробка вибору файлів
            photoInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                const currentCount = document.querySelectorAll('.preview-item').length;
                
                files.forEach(file => {
                    if (currentCount + uploadedFiles.length >= 40) {
                        alert('Możesz dodać maksymalnie 40 zdjęć.');
                        return;
                    }

                    if (validateFile(file)) {
                        uploadedFiles.push(file);
                        createPreview(file);
                    }
                });

                // Очищаємо input для можливості повторного вибору тих самих файлів
                this.value = '';
            });

            // Обробка drag & drop
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                const currentCount = document.querySelectorAll('.preview-item').length;

                files.forEach(file => {
                    if (currentCount + uploadedFiles.length >= 40) {
                        alert('Możesz dodać maksymalnie 40 zdjęć.');
                        return;
                    }

                    if (validateFile(file)) {
                        uploadedFiles.push(file);
                        createPreview(file);
                    }
                });
            });

            // Обробник кліку на кнопку додавання зображень
            uploadButton.addEventListener('click', () => {
                photoInput.click();
            });

            // Обробники для drag & drop
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });
        });

        // Функція для встановлення чекбоксів обладнання
        function setEquipmentCheckboxes(data) {
            if (!data) return;
            
            try {
                const equipment = typeof data === 'string' ? JSON.parse(data) : data;
                equipment.forEach(value => {
                    const checkbox = document.querySelector(`input[name="equipment[]"][value="${value}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            } catch (e) {
                console.error('Error setting equipment:', e);
            }
        }

        // Функція для оновлення лічильника
        function updateEquipmentCounter(checkbox) {
            const section = checkbox.closest('.equipment-item');
            if (section) {
                const counter = section.querySelector('.equipment-counter');
                if (counter) {
                    const checkedCount = section.querySelectorAll('input[type="checkbox"]:checked').length;
                    const total = counter.textContent.split('/')[1];
                    counter.textContent = `${checkedCount}/${total}`;
                }
            }
        }

        // Встановлюємо значення при завантаженні сторінки
        <?php if ($isEditing && $listingData): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const formData = <?php echo json_encode($listingData); ?>;
            
            // Встановлюємо чекбокси для кожної категорії
            if (formData.audio_multimedia) setEquipmentCheckboxes(formData.audio_multimedia);
            if (formData.komfort) setEquipmentCheckboxes(formData.komfort);
            if (formData.samochody_elektryczne) setEquipmentCheckboxes(formData.samochody_elektryczne);
            if (formData.systemy_wspomagania) setEquipmentCheckboxes(formData.systemy_wspomagania);
            if (formData.osiagi_tuning) setEquipmentCheckboxes(formData.osiagi_tuning);
            if (formData.bezpieczenstwo) setEquipmentCheckboxes(formData.bezpieczenstwo);

            // Set price type button active state
            if (formData.price_type) {
                const priceTypeLabel = document.querySelector(`label[for="price_type_${formData.price_type}"]`);
                if (priceTypeLabel) {
                    priceTypeLabel.classList.add('active');
                }
            }
        });
        <?php endif; ?>
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Price type button handling
            const priceTypeLabels = document.querySelectorAll('label.price-type-btn');
            const priceTypeRadios = document.querySelectorAll('input[name="price_type"]');
            
            priceTypeLabels.forEach((label) => {
                label.addEventListener('click', function() {
                    // Remove active class from all labels
                    priceTypeLabels.forEach(lbl => lbl.classList.remove('active'));
                    // Add active class to clicked label
                    this.classList.add('active');
                    // Find and check the corresponding radio button
                    const radioId = this.getAttribute('for');
                    document.getElementById(radioId).checked = true;
                });
            });
        });
    </script>
    <script>
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Opisz swój samochód...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Update hidden input before form submission
        document.getElementById('listingForm').onsubmit = function() {
            var description = document.getElementById('description');
            description.value = quill.root.innerHTML;
            return true;
        };
    </script>
    <script>
        <?php if ($isEditing && $listingData): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const formData = <?php echo json_encode($listingData); ?>;
            
            // Populate form fields
            for (const [key, value] of Object.entries(formData)) {
                if (!value || ['audio_multimedia', 'komfort', 'samochody_elektryczne', 
                    'systemy_wspomagania', 'osiagi_tuning', 'bezpieczenstwo'].includes(key)) continue;

                const elements = document.getElementsByName(key);
                elements.forEach(element => {
                    if (element.type === 'radio') {
                        if (element.value === value.toString()) {
                            element.checked = true;
                            if (key === 'price_type') {
                                const label = document.querySelector(`label[for="${element.id}"]`);
                                if (label) label.classList.add('active');
                            }
                        }
                    } else if (element.type === 'checkbox') {
                        element.checked = value === '1' || value === true;
                    } else if (!element.type.includes('file')) {
                        element.value = value;
                    }
                });
            }

            // Handle Quill editor
            if (formData.description) {
                const quill = new Quill('#editor');
                quill.root.innerHTML = formData.description;
            }
            
            // Handle equipment checkboxes
            if (formData.audio_multimedia) setEquipmentCheckboxes(formData.audio_multimedia);
            if (formData.komfort) setEquipmentCheckboxes(formData.komfort);
            if (formData.samochody_elektryczne) setEquipmentCheckboxes(formData.samochody_elektryczne);
            if (formData.systemy_wspomagania) setEquipmentCheckboxes(formData.systemy_wspomagania);
            if (formData.osiagi_tuning) setEquipmentCheckboxes(formData.osiagi_tuning);
            if (formData.bezpieczenstwo) setEquipmentCheckboxes(formData.bezpieczenstwo);
        });
        <?php endif; ?>
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    // Отримуємо всі кнопки для розгортання
    const expandButtons = document.querySelectorAll('.expand-btn');

    // Додаємо обробник подій для кожної кнопки
    expandButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Знаходимо найближчий контейнер з класом details-section
            const detailsSection = button.closest('.details-section');
            
            if (detailsSection) {
                // Знаходимо елемент з класом details-content всередині секції
                const detailsContent = detailsSection.querySelector('.details-content');

                if (detailsContent) {
                    detailsContent.classList.toggle('expanded');
                    const isExpanded = detailsContent.classList.contains('expanded');
                    detailsContent.style.maxHeight = isExpanded ? detailsContent.scrollHeight + 'px' : null;
                }
            }

            // Для equipment-section
            const equipmentSection = button.closest('.equipment-section');
            
            if (equipmentSection) {
                const equipmentContent = equipmentSection.querySelector('.equipment-content');

                if (equipmentContent) {
                    equipmentContent.classList.toggle('expanded');
                    const isExpanded = equipmentContent.classList.contains('expanded');
                    equipmentContent.style.maxHeight = isExpanded ? equipmentContent.scrollHeight + 'px' : null;
                }
            }
        });
    });
});

    </script>
</body>
</html>