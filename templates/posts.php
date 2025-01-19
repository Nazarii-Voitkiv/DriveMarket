<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Ogłoszenia</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/posts.css">
    <link rel="stylesheet" href="../styles/footer.css">
</head>
<body>
<header class="header">
    <a href="../index.html"><div class="logo">DriveMarket</div></a>
    <nav class="menu" style="padding-left: 40px;">
        <a href="posts.php" class="active">Produkty</a>
        <a href="about.php">O nas</a>
        <a href="contact.php">Kontakt</a>
    </nav>
    <div class="auth-container">
        <!-- Якщо користувач не авторизований -->
        <div class="auth-buttons">
            <a href="login.php" class="auth-btn">Zaloguj się</a>
            <a href="register.php" class="auth-btn">Zarejestruj się</a>
        </div>
        <!-- Якщо користувач авторизований (перемикатися JS-ом) -->
        <div class="user-menu" style="display: none;">
            <div class="avatar-circle" onclick="toggleDropdown()">
                <span id="userInitials">A</span>
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

<section class="posts">
    <h1>Wszystkie ogłoszenia</h1>
    <div class="filters-container">
        <div class="filters-row">
            <div class="filter-group">
                <select class="filter-select" id="sortowanie">
                    <option value="">Sortuj</option>
                    <option value="cena_asc">Cena: od najniższej</option>
                    <option value="cena_desc">Cena: od najwyższej</option>
                    <option value="przebieg_asc">Najniższy przebieg</option>
                    <option value="przebieg_desc">Najwyższy przebieg</option>
                </select>
            </div>
            <div class="filter-group">
                <select class="filter-select" id="marka">
                    <option value="">Marka pojazdu</option>
                    <?php
                    try {
                        $connect = new PDO(
                            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
                            $config['db_username'],
                            $config['db_password']
                        );
                        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $markaQuery = "SELECT DISTINCT brand FROM listings ORDER BY brand ASC";
                        $markaStmt = $connect->prepare($markaQuery);
                        $markaStmt->execute();
                        
                        while ($marka = $markaStmt->fetch(PDO::FETCH_ASSOC)) {
                            if (!empty($marka['brand'])) {
                                echo '<option value="' . htmlspecialchars($marka['brand']) . '">' . htmlspecialchars($marka['brand']) . '</option>';
                            }
                        }
                    } catch (PDOException $e) {
                        
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <select class="filter-select" id="model" disabled>
                    <option value="">Model pojazdu</option>
                </select>
            </div>

            <div class="filter-group">
                <select class="filter-select" id="generacja">
                    <option value="">Generacja</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>

            <div class="filter-group">
                <select class="filter-select" id="typ_nadwozia">
                    <option value="">Typ nadwozia</option>
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

            <div class="filter-group">
                <input type="number" class="filter-input" placeholder="Cena od" id="cena_od">
            </div>

            <div class="filter-group">
                <input type="number" class="filter-input" placeholder="Cena do" id="cena_do">
            </div>
        </div>

        <div class="filters-row">
            <div class="filter-group">
                <select class="filter-select" id="rok_od">
                    <option value="">Rok od</option>
                    <?php
                    for ($year = 2025; $year >= 1900; $year--) {
                        echo "<option value=\"$year\">$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <select class="filter-select" id="rok_do">
                    <option value="">Rok do</option>
                    <?php
                    for ($year = 2025; $year >= 1900; $year--) {
                        echo "<option value=\"$year\">$year</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <select class="filter-select" id="rodzaj_paliwa">
                    <option value="">Rodzaj paliwa</option>
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

            <div class="filter-group">
                <input type="number" class="filter-input" placeholder="Przebieg od" id="przebieg_od">
            </div>

            <div class="filter-group">
                <input type="number" class="filter-input" placeholder="Przebieg do" id="przebieg_do">
            </div>
        </div>

        <div class="filters-row">
            <div class="filter-group">
                <select class="filter-select" id="stan_uszkodzenia">
                    <option value="">Stan uszkodzenia</option>
                    <option value="dowolny">Dowolny</option>
                    <option value="0">Nieuszkodzony</option>
                    <option value="1">Uszkodzony</option>
                </select>
            </div>

            <div class="filter-group">
                <a href="#" class="more-filters-link">Pokaż więcej filtrów</a>
            </div>
        </div>

        <div class="extended-filters" style="display: none;">
            <div class="filters-columns">
                <!-- Перша колонка -->
                <div class="filters-column">
                    <div class="filters-section">
                        <div class="filter-header">
                            <h3>Kraj pochodzenia</h3>
                        </div>
                        <div class="filter-content">
                            <select name="kraj_pochodzenia" class="filter-select full-width" id="kraj_pochodzenia">
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
                    </div>
                </div>

                <!-- Друга колонка -->
                <div class="filters-column">
                    <div class="filters-section" id="statusSection">
                        <div class="filter-header">
                            <h3>Status pojazdu</h3>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="filter-header">
                            <h3>Silnik i napęd</h3>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="filter-header">
                            <h3>Informacje finansowe</h3>
                        </div>
                    </div>
                </div>

                <!-- Третя колонка -->
                <div class="filters-column">
                    <div class="filters-section">
                        <div class="filter-header">
                            <h3>Nadwozie</h3>
                        </div>
                    </div>

                    <div class="filters-section">
                        <div class="filter-header">
                            <h3>Dodatkowe wyposażenie</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Модальне вікно для статусу автомобіля -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Status pojazdu</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="status-options">
                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="vin">
                        <span class="status-text">Pokaż oferty z numerem VIN</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="rejestracyjny">
                        <span class="status-text">Ma numer rejestracyjny</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="zarejestrowany">
                        <span class="status-text">Zarejestrowany w Polsce</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="wlasciciel">
                        <span class="status-text">Pierwszy właściciel</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="bezwypadkowy">
                        <span class="status-text">Bezwypadkowy</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="serwisowany">
                        <span class="status-text">Serwisowany w ASO</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="zabytek">
                        <span class="status-text">Zarejestrowany jako zabytek</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="tuning">
                        <span class="status-text">Tuning</span>
                    </label>

                    <label class="status-option">
                        <input type="checkbox" name="status[]" value="homologacja">
                        <span class="status-text">Homologacja ciężarowa</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="post-container">
        <?php
        try {
            $connect = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']}",
                $config['db_username'],
                $config['db_password']
            );
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Забираємо всі (або лише aktywne, якщо є статус)
            $query = "SELECT * FROM listings ORDER BY listing_id DESC";
            $stmt = $connect->prepare($query);
            $stmt->execute();
            $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($listings) > 0) {
                foreach ($listings as $listing) {
                    echo '<a href="listing-details.php?id=' . $listing['listing_id'] . '" class="post-link">';
                    echo '<div class="post">';

                    // 1) Зображення
                    if (!empty($listing['images'])) {
                        $images = json_decode($listing['images'], true);
                        error_log("Raw images from DB: " . $listing['images']);
                        error_log("Decoded images: " . print_r($images, true));
                        if (is_array($images) && count($images) > 0) {
                            $firstImage = str_replace('\\/', '/', $images[0]); // Fix escaped slashes
                            error_log("Image path: " . $firstImage);
                            echo '<div class="post-box">';
                            echo '<img src="../' . htmlspecialchars($firstImage) . '" alt="Zdjęcie pojazdu" />';
                            echo '</div>';
                        } else {
                            echo '<div class="post-box"></div>';
                        }
                    } else {
                        echo '<div class="post-box"></div>';
                    }

                    // 2) Інфо: марка, модель, ціна, телефон...
                    echo '<div class="post-info">';
                    echo '<h2>' . htmlspecialchars($listing['brand']) . ' ' . htmlspecialchars($listing['model']) . '</h2>';

                    // Форматуємо ціну з урахуванням ПДВ
                    $price = isset($listing['price']) ? (float)$listing['price'] : 0;
                    $priceType = isset($listing['price_type']) ? $listing['price_type'] : 'brutto';
                    
                    if ($priceType === 'netto') {
                        $priceWithVat = round($price * 1.23);
                        $formattedPrice = number_format($priceWithVat, 0, '.', ' ');
                        echo '<div class="price-tag"><strong>' . $formattedPrice . '</strong> PLN (z VAT)</div>';
                    } else {
                        $formattedPrice = number_format($price, 0, '.', ' ');
                        echo '<div class="price-tag"><strong>' . $formattedPrice . '</strong> PLN</div>';
                    }

                    // Рік, телефон...
                    echo '<p><strong>Rok:</strong> '
                        . htmlspecialchars($listing['prod_year'] ?? 'Brak danych')
                        . '</p>';
                    echo '<p><strong>Kontakt:</strong> '
                        . htmlspecialchars($listing['seller_phone'] ?? 'Brak telefonu')
                        . '</p>';

                    echo '</div>'; // .post-info
                    echo '</div>'; // .post
                    echo '</a>'; // .post-link
                }
            } else {
                echo '<p>Brak ogłoszeń do wyświetlenia.</p>';
            }
        } catch (PDOException $e) {
            echo '<p>Nie udało się załadować ogłoszeń: '
                . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    </div>
</section>
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
    document.addEventListener('DOMContentLoaded', function() {
        const markaSelect = document.getElementById('marka');
        const modelSelect = document.getElementById('model');

        markaSelect.addEventListener('change', function() {
            if (this.value) {
                modelSelect.disabled = false;
                // Очищаємо попередні опції
                modelSelect.innerHTML = '<option value="">Model pojazdu</option>';
                
                // Отримуємо моделі для вибраної марки
                fetch(`../handlers/get-models.php?brand=${encodeURIComponent(this.value)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received data:', data);
                        
                        // Перевіряємо чи немає помилки
                        if (data.error) {
                            console.error('Server error:', data.error);
                            return;
                        }
                        
                        // Перевіряємо чи data є масивом
                        const models = Array.isArray(data) ? data : [];
                        
                        models.forEach(model => {
                            if (model) { // Перевіряємо чи модель не пуста
                                const option = document.createElement('option');
                                option.value = model;
                                option.textContent = model;
                                modelSelect.appendChild(option);
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching models:', error);
                        modelSelect.disabled = true;
                    });
            } else {
                modelSelect.disabled = true;
                modelSelect.innerHTML = '<option value="">Model pojazdu</option>';
            }
        });
    });
</script>
<script src="../scripts/posts.js"></script>
</body>
</html>