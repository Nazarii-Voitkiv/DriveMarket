<?php
session_start();
require '../vendor/autoload.php';
$config = include('../config.php');

// Можливо, ви не хочете вимагати авторизацію для перегляду публічних оголошень,
// тож можете не перевіряти $_COOKIE['token'] тут.
// Якщо ж оголошення закриті та потрібна авторизація, тоді додайте перевірку cookie.

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Ogłoszenia</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/posts.css">
</head>
<body>
<header class="header">
    <div class="logo">DriveMarket</div>
    <nav class="menu">
        <a href="../index.html">Strona główna</a>
        <a href="posts.php">Ogłoszenia</a>
        <a href="#">Kontakt</a>
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
                        // Просто пропускаємо помилку, щоб не зламати інтерфейс
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
                        // Припустимо, що images зберігається у форматі JSON
                        $images = json_decode($listing['images'], true);
                        if (is_array($images) && count($images) > 0) {
                            // Якщо масив з Base64
                            // Якщо ж зберігаються просто назви файлів, тоді:
                            // echo '<img src="../upload/' . htmlspecialchars($images[0]) . '" ... >';

                            // Для Base64 вигляду:
                            $firstImage = $images[0];
                            if (!empty($firstImage['data']) && !empty($firstImage['type'])) {
                                echo '<div class="post-box">';
                                echo '<img src="data:' . htmlspecialchars($firstImage['type']) . ';base64,' . htmlspecialchars($firstImage['data']) . '" alt="Zdjęcie pojazdu" />';
                                echo '</div>';
                            } else {
                                echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
                            }
                        } else {
                            echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
                        }
                    } else {
                        echo '<div class="post-box"><div class="no-image">Brak zdjęcia</div></div>';
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
    <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
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